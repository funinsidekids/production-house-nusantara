<?php

namespace App\Http\Controllers;

use App\Models\LandingSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StoreController extends Controller
{
    public function index(): View
    {
        $store = $this->storePayload();
        $products = $this->publishedProducts($store);

        return view('public-store-index', [
            'products' => $products,
            'currency' => (string) ($store['currency'] ?? 'IDR'),
            'cartCount' => $this->cartCount(),
        ]);
    }

    public function show(string $slug): View|RedirectResponse
    {
        $store = $this->storePayload();
        $products = $this->publishedProducts($store);
        $product = collect($products)->first(fn (array $item): bool => (string) ($item['slug'] ?? '') === Str::slug($slug));
        if (! is_array($product)) {
            return redirect()->route('store.index');
        }

        return view('public-store-detail', [
            'product' => $product,
            'currency' => (string) ($store['currency'] ?? 'IDR'),
            'cartCount' => $this->cartCount(),
        ]);
    }

    public function cart(): View
    {
        $store = $this->storePayload();
        [$items, $subtotal] = $this->cartItemsAndSubtotal($store);
        $taxPercent = (float) ($store['tax_percent'] ?? 0);
        $taxAmount = $subtotal * ($taxPercent / 100);
        $total = $subtotal + $taxAmount;

        return view('public-store-cart', [
            'items' => $items,
            'currency' => (string) ($store['currency'] ?? 'IDR'),
            'subtotal' => $subtotal,
            'taxPercent' => $taxPercent,
            'taxAmount' => $taxAmount,
            'total' => $total,
            'cartCount' => $this->cartCount(),
        ]);
    }

    public function addToCart(Request $request, string $slug): RedirectResponse
    {
        $data = $request->validate([
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);
        $qty = (int) ($data['qty'] ?? 1);
        $store = $this->storePayload();
        $products = $this->publishedProducts($store);
        $product = collect($products)->first(fn (array $item): bool => (string) ($item['slug'] ?? '') === Str::slug($slug));
        if (! is_array($product)) {
            return redirect()->route('store.index')->with('error', 'Product tidak ditemukan.');
        }
        if (! $this->productFileExists($product)) {
            return redirect()->route('store.index')->with('error', 'File produk belum tersedia.');
        }

        $cart = $this->cartMap();
        $key = (string) $product['slug'];
        $nextQty = min(99, max(1, (int) ($cart[$key] ?? 0) + $qty));
        $stock = (int) ($product['stock'] ?? 0);
        if ($stock > 0) {
            $nextQty = min($nextQty, $stock);
        }
        $cart[$key] = $nextQty;
        session(['store_cart' => $cart]);

        return redirect()->route('store.cart')->with('success', 'Product ditambahkan ke cart.');
    }

    public function purchase(Request $request, string $slug): RedirectResponse
    {
        $data = $request->validate([
            'customer_email' => ['required', 'email', 'max:180'],
            'customer_name' => ['nullable', 'string', 'max:180'],
            'customer_phone' => ['nullable', 'string', 'max:80'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $store = $this->storePayload();
        $product = $this->resolvePublishedProductBySlug($store, $slug);
        if (! is_array($product)) {
            return redirect()->route('store.index')->with('error', 'Product tidak ditemukan.');
        }
        if (! $this->productFileExists($product)) {
            return redirect()->route('store.index')->with('error', 'File produk belum tersedia.');
        }

        if ($this->isFreeProduct($product)) {
            $tokenEntry = $this->createDownloadToken((string) $product['slug'], (string) $data['customer_email'], null);
            $this->sendDownloadLinksEmail((string) $data['customer_email'], [[
                'product_name' => (string) $product['name'],
                'download_url' => route('store.download.token', ['token' => $tokenEntry['token']]),
                'expired_at' => (string) ($tokenEntry['expires_at'] ?? ''),
                'max_downloads' => (int) ($tokenEntry['max_downloads'] ?? 3),
            ]], true);

            return redirect()->route('store.index')->with('success', 'Produk gratis berhasil diproses. Link download dikirim ke email.');
        }

        $orderCode = $this->nextOrderCode();
        $checkoutMode = (string) ($store['checkout_mode'] ?? 'manual');
        $paymentUrl = '';
        $lineTotal = (float) ($product['price'] ?? 0);
        if ($checkoutMode === 'doku') {
            $paymentUrl = $this->buildDokuCheckoutUrl($store, $orderCode, $lineTotal, [
                'name' => (string) ($data['customer_name'] ?? ''),
                'email' => (string) $data['customer_email'],
            ]);
        }
        $orders = $this->ordersPayload();
        array_unshift($orders, [
            'order_code' => $orderCode,
            'status' => $checkoutMode === 'doku' ? 'awaiting_payment' : 'pending',
            'currency' => (string) ($store['currency'] ?? 'IDR'),
            'subtotal' => $lineTotal,
            'tax_percent' => 0,
            'tax_amount' => 0,
            'total' => $lineTotal,
            'checkout_mode' => $checkoutMode,
            'customer' => [
                'name' => (string) ($data['customer_name'] ?? ''),
                'email' => (string) $data['customer_email'],
                'phone' => (string) ($data['customer_phone'] ?? ''),
                'note' => (string) ($data['customer_note'] ?? ''),
            ],
            'items' => [[
                'name' => (string) $product['name'],
                'slug' => (string) $product['slug'],
                'type' => (string) $product['type'],
                'sku' => (string) $product['sku'],
                'price' => (float) $product['price'],
                'qty' => 1,
                'line_total' => $lineTotal,
            ]],
            'admin_note' => '',
            'payment_url' => $paymentUrl,
            'download_email_sent' => false,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_store_orders_payload'],
            ['value' => json_encode(array_values(array_slice($orders, 0, 500)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        if ($checkoutMode === 'doku' && $paymentUrl !== '') {
            return redirect()->away($paymentUrl);
        }

        return redirect()->route('store.order.success', ['orderCode' => $orderCode]);
    }

    public function updateCart(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'qty' => ['required', 'array'],
            'qty.*' => ['nullable', 'integer', 'min:0', 'max:99'],
        ]);
        $cart = $this->cartMap();
        $store = $this->storePayload();
        $productMap = collect($this->publishedProducts($store))->keyBy('slug');
        foreach ($validated['qty'] as $slug => $qty) {
            $slugKey = Str::slug((string) $slug);
            $quantity = (int) $qty;
            if ($quantity <= 0) {
                unset($cart[$slugKey]);

                continue;
            }
            $product = $productMap->get($slugKey);
            if (is_array($product)) {
                $stock = (int) ($product['stock'] ?? 0);
                if ($stock > 0) {
                    $quantity = min($quantity, $stock);
                }
            }
            $cart[$slugKey] = $quantity;
        }
        session(['store_cart' => $cart]);

        return redirect()->route('store.cart')->with('success', 'Cart berhasil diperbarui.');
    }

    public function removeFromCart(string $slug): RedirectResponse
    {
        $cart = $this->cartMap();
        unset($cart[Str::slug($slug)]);
        session(['store_cart' => $cart]);

        return redirect()->route('store.cart')->with('success', 'Item dihapus dari cart.');
    }

    public function checkout(): View|RedirectResponse
    {
        $store = $this->storePayload();
        [$items, $subtotal] = $this->cartItemsAndSubtotal($store);
        if (count($items) === 0) {
            return redirect()->route('store.cart')->with('error', 'Cart masih kosong.');
        }
        $taxPercent = (float) ($store['tax_percent'] ?? 0);
        $taxAmount = $subtotal * ($taxPercent / 100);
        $total = $subtotal + $taxAmount;

        return view('public-store-checkout', [
            'items' => $items,
            'currency' => (string) ($store['currency'] ?? 'IDR'),
            'subtotal' => $subtotal,
            'taxPercent' => $taxPercent,
            'taxAmount' => $taxAmount,
            'total' => $total,
            'cartCount' => $this->cartCount(),
            'checkoutMode' => (string) ($store['checkout_mode'] ?? 'manual'),
        ]);
    }

    public function checkoutPlaceOrder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:180'],
            'customer_email' => ['required', 'email', 'max:180'],
            'customer_phone' => ['nullable', 'string', 'max:80'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $store = $this->storePayload();
        [$items, $subtotal] = $this->cartItemsAndSubtotal($store);
        if (count($items) === 0) {
            return redirect()->route('store.cart')->with('error', 'Cart masih kosong.');
        }

        $taxPercent = (float) ($store['tax_percent'] ?? 0);
        $taxAmount = $subtotal * ($taxPercent / 100);
        $total = $subtotal + $taxAmount;
        $allFree = collect($items)->every(fn (array $item): bool => $this->isFreeProduct($item));
        if ($allFree) {
            $links = [];
            foreach ($items as $item) {
                if (! $this->productFileExists($item)) {
                    continue;
                }
                $tokenEntry = $this->createDownloadToken((string) $item['slug'], (string) $data['customer_email'], null);
                $links[] = [
                    'product_name' => (string) $item['name'],
                    'download_url' => route('store.download.token', ['token' => $tokenEntry['token']]),
                    'expired_at' => (string) ($tokenEntry['expires_at'] ?? ''),
                    'max_downloads' => (int) ($tokenEntry['max_downloads'] ?? 3),
                ];
            }
            if (count($links) > 0) {
                $this->sendDownloadLinksEmail((string) $data['customer_email'], $links, true);
            }
            session()->forget('store_cart');

            return redirect()->route('store.index')->with('success', 'Produk gratis diproses. Link download sudah dikirim ke email.');
        }
        $orderCode = $this->nextOrderCode();
        $orders = $this->ordersPayload();
        $checkoutMode = (string) ($store['checkout_mode'] ?? 'manual');
        $initialStatus = $checkoutMode === 'doku' ? 'awaiting_payment' : 'pending';
        $paymentUrl = '';
        if ($checkoutMode === 'doku') {
            $paymentUrl = $this->buildDokuCheckoutUrl($store, $orderCode, $total, [
                'name' => (string) $data['customer_name'],
                'email' => (string) $data['customer_email'],
            ]);
        }

        array_unshift($orders, [
            'order_code' => $orderCode,
            'status' => $initialStatus,
            'currency' => (string) ($store['currency'] ?? 'IDR'),
            'subtotal' => $subtotal,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'checkout_mode' => $checkoutMode,
            'customer' => [
                'name' => $data['customer_name'],
                'email' => $data['customer_email'],
                'phone' => $data['customer_phone'] ?? '',
                'note' => $data['customer_note'] ?? '',
            ],
            'items' => $items,
            'admin_note' => '',
            'payment_url' => $paymentUrl,
            'download_email_sent' => false,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_store_orders_payload'],
            ['value' => json_encode(array_values(array_slice($orders, 0, 500)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        session()->forget('store_cart');

        if ($checkoutMode === 'doku' && $paymentUrl !== '') {
            return redirect()->away($paymentUrl);
        }

        return redirect()->route('store.order.success', ['orderCode' => $orderCode]);
    }

    public function orderSuccess(string $orderCode): View|RedirectResponse
    {
        $orders = $this->ordersPayload();
        $order = collect($orders)->first(fn (array $item): bool => (string) ($item['order_code'] ?? '') === (string) $orderCode);
        if (! is_array($order)) {
            return redirect()->route('store.index');
        }

        return view('public-store-success', [
            'order' => $order,
            'cartCount' => $this->cartCount(),
        ]);
    }

    public function dokuReturn(Request $request): RedirectResponse
    {
        $orderCode = (string) $request->query('order_code', '');
        if ($orderCode === '') {
            return redirect()->route('store.index');
        }

        return redirect()->route('store.order.success', ['orderCode' => $orderCode]);
    }

    public function dokuNotify(Request $request): JsonResponse
    {
        $orderCode = (string) $request->input('order_code', '');
        $statusRaw = Str::lower((string) $request->input('status', ''));
        if ($orderCode === '') {
            return response()->json(['ok' => false, 'message' => 'order_code required'], 422);
        }

        $store = $this->storePayload();
        $signatureCheck = $this->verifyDokuNotificationSignature($request, $store);
        if (! $signatureCheck['ok']) {
            return response()->json(['ok' => false, 'message' => (string) $signatureCheck['message']], 401);
        }

        $expectedToken = trim((string) ($store['doku_notify_token'] ?? ''));
        $incomingToken = trim((string) $request->header('x-doku-token', $request->input('notify_token', '')));
        if ($expectedToken !== '' && ! hash_equals($expectedToken, $incomingToken)) {
            return response()->json(['ok' => false, 'message' => 'invalid notify token'], 401);
        }
        $requestId = trim((string) $request->header('Request-Id', ''));
        if ($requestId === '') {
            return response()->json(['ok' => false, 'message' => 'missing request id'], 422);
        }
        if (! $this->checkAndRememberDokuRequestId($requestId)) {
            return response()->json(['ok' => false, 'message' => 'replay detected'], 409);
        }

        $statusMap = [
            'success' => 'paid',
            'paid' => 'paid',
            'settlement' => 'paid',
            'pending' => 'awaiting_payment',
            'challenge' => 'awaiting_payment',
            'expire' => 'failed',
            'failed' => 'failed',
            'cancel' => 'cancelled',
            'cancelled' => 'cancelled',
            'refund' => 'refunded',
            'refunded' => 'refunded',
        ];
        $newStatus = $statusMap[$statusRaw] ?? 'awaiting_payment';
        $updated = $this->updateOrderByCode($orderCode, [
            'status' => $newStatus,
            'gateway_reference' => (string) $request->input('transaction_id', ''),
            'gateway_payload' => $request->all(),
            'updated_at' => now()->toDateTimeString(),
        ]);
        if (! $updated) {
            return response()->json(['ok' => false, 'message' => 'order not found'], 404);
        }

        if ($newStatus === 'paid') {
            $this->deliverOrderDownloads($orderCode);
        }

        return response()->json(['ok' => true, 'order_code' => $orderCode, 'status' => $newStatus]);
    }

    public function downloadByToken(string $token): BinaryFileResponse
    {
        $this->guardDownloadRateLimit($token);
        $tokens = $this->downloadTokensPayload();
        $foundIndex = null;
        foreach ($tokens as $index => $item) {
            if (! is_array($item)) {
                continue;
            }
            if ($this->downloadTokenMatches($item, $token)) {
                $foundIndex = $index;
                break;
            }
        }
        if ($foundIndex === null) {
            abort(403);
        }
        $entry = $tokens[$foundIndex];
        $expiresAt = strtotime((string) ($entry['expires_at'] ?? ''));
        if ($expiresAt === false || $expiresAt < time()) {
            abort(403);
        }
        $downloadCount = (int) ($entry['download_count'] ?? 0);
        $maxDownloads = (int) ($entry['max_downloads'] ?? 3);
        if ($downloadCount >= $maxDownloads) {
            abort(403);
        }

        $store = $this->storePayload();
        $product = $this->resolvePublishedProductBySlug($store, (string) ($entry['product_slug'] ?? ''));
        if (! is_array($product)) {
            abort(403);
        }
        $assetPath = (string) ($product['asset_path'] ?? '');
        if ($assetPath === '' || ! Storage::disk('local')->exists($assetPath)) {
            abort(403);
        }

        $tokens[$foundIndex]['download_count'] = $downloadCount + 1;
        $tokens[$foundIndex]['last_download_at'] = now()->toDateTimeString();
        unset($tokens[$foundIndex]['token']);
        $this->saveDownloadTokensPayload($tokens);

        $absolutePath = Storage::disk('local')->path($assetPath);
        $downloadName = basename($assetPath);

        return response()->download($absolutePath, $downloadName);
    }

    private function storePayload(): array
    {
        $raw = (string) (LandingSetting::query()->where('key', 'cms_store_products_payload')->value('value') ?? '');
        $decoded = json_decode($raw, true);
        $payload = is_array($decoded) ? $decoded : [];
        if (! is_array($payload['products'] ?? null)) {
            $payload['products'] = [];
        }

        return $payload;
    }

    private function publishedProducts(array $store): array
    {
        return collect($store['products'] ?? [])
            ->filter(fn ($item): bool => is_array($item))
            ->map(function (array $product): array {
                $name = trim((string) ($product['name'] ?? ''));

                return [
                    'name' => $name,
                    'slug' => Str::slug((string) ($product['slug'] ?? $name)),
                    'type' => (string) ($product['type'] ?? 'other'),
                    'sku' => (string) ($product['sku'] ?? ''),
                    'price' => (float) ($product['price'] ?? 0),
                    'stock' => max(0, (int) ($product['stock'] ?? 0)),
                    'thumbnail' => (string) ($product['thumbnail'] ?? ''),
                    'photo' => (string) ($product['photo'] ?? ''),
                    'download_url' => (string) ($product['download_url'] ?? ''),
                    'asset_path' => (string) ($product['asset_path'] ?? $product['download_url'] ?? ''),
                    'description' => (string) ($product['description'] ?? ''),
                    'function' => (string) ($product['function'] ?? ''),
                    'category' => (string) ($product['category'] ?? ''),
                    'tags' => (string) ($product['tags'] ?? ''),
                    'license_type' => (string) ($product['license_type'] ?? ''),
                    'status' => (string) ($product['status'] ?? 'draft'),
                ];
            })
            ->filter(fn (array $item): bool => $item['name'] !== '' && $item['status'] === 'published')
            ->values()
            ->all();
    }

    private function cartMap(): array
    {
        $cart = session('store_cart', []);
        if (! is_array($cart)) {
            return [];
        }

        return collect($cart)
            ->mapWithKeys(fn ($qty, $slug): array => [Str::slug((string) $slug) => max(0, (int) $qty)])
            ->filter(fn (int $qty): bool => $qty > 0)
            ->all();
    }

    private function cartCount(): int
    {
        return array_sum($this->cartMap());
    }

    private function cartItemsAndSubtotal(array $store): array
    {
        $products = $this->publishedProducts($store);
        $productMap = collect($products)->keyBy('slug')->all();
        $cart = $this->cartMap();
        $items = [];
        $subtotal = 0.0;

        foreach ($cart as $slug => $qty) {
            $product = $productMap[$slug] ?? null;
            if (! is_array($product)) {
                continue;
            }
            $price = (float) ($product['price'] ?? 0);
            $lineTotal = $price * $qty;
            $item = $product;
            $item['qty'] = $qty;
            $item['line_total'] = $lineTotal;
            $items[] = $item;
            $subtotal += $lineTotal;
        }

        return [$items, $subtotal];
    }

    private function ordersPayload(): array
    {
        $raw = (string) (LandingSetting::query()->where('key', 'cms_store_orders_payload')->value('value') ?? '');
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function nextOrderCode(): string
    {
        $prefix = 'ORD-'.now()->format('Ymd').'-';
        $seed = strtoupper(Str::random(6));

        return $prefix.$seed;
    }

    private function buildDokuCheckoutUrl(array $store, string $orderCode, float $amount, array $customer): string
    {
        $baseUrl = trim((string) ($store['doku_checkout_url'] ?? ''));
        if ($baseUrl === '') {
            return '';
        }

        $separator = Str::contains($baseUrl, '?') ? '&' : '?';
        $params = http_build_query([
            'order_code' => $orderCode,
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => (string) ($store['currency'] ?? 'IDR'),
            'name' => (string) ($customer['name'] ?? ''),
            'email' => (string) ($customer['email'] ?? ''),
            'merchant_id' => (string) ($store['doku_merchant_id'] ?? ''),
            'client_id' => (string) ($store['doku_client_id'] ?? ''),
            'return_url' => route('store.payment.doku.return'),
            'notify_url' => route('store.payment.doku.notify'),
        ]);

        return $baseUrl.$separator.$params;
    }

    private function updateOrderByCode(string $orderCode, array $changes): bool
    {
        $orders = $this->ordersPayload();
        $updated = false;
        foreach ($orders as $index => $order) {
            if (! is_array($order)) {
                continue;
            }
            if ((string) ($order['order_code'] ?? '') !== $orderCode) {
                continue;
            }
            $orders[$index] = array_merge($order, $changes);
            $updated = true;
            break;
        }
        if (! $updated) {
            return false;
        }

        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_store_orders_payload'],
            ['value' => json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        return true;
    }

    private function createDownloadToken(string $productSlug, string $email, ?string $orderCode): array
    {
        $tokens = $this->downloadTokensPayload();
        $token = Str::lower(Str::random(64));
        $entry = [
            'token_hash' => hash('sha256', $token),
            'product_slug' => Str::slug($productSlug),
            'email' => trim($email),
            'order_code' => $orderCode,
            'max_downloads' => 3,
            'download_count' => 0,
            'expires_at' => now()->addDay()->toDateTimeString(),
            'created_at' => now()->toDateTimeString(),
            'last_download_at' => null,
        ];
        array_unshift($tokens, $entry);
        $this->saveDownloadTokensPayload($tokens);

        return array_merge($entry, ['token' => $token]);
    }

    private function downloadTokensPayload(): array
    {
        $raw = (string) (LandingSetting::query()->where('key', 'cms_store_download_tokens_payload')->value('value') ?? '');
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function saveDownloadTokensPayload(array $tokens): void
    {
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_store_download_tokens_payload'],
            ['value' => json_encode(array_values(array_slice($tokens, 0, 5000)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
    }

    private function downloadTokenMatches(array $entry, string $plainToken): bool
    {
        $hash = trim((string) ($entry['token_hash'] ?? ''));
        if ($hash !== '') {
            return hash_equals($hash, hash('sha256', $plainToken));
        }
        $legacyToken = trim((string) ($entry['token'] ?? ''));
        if ($legacyToken === '') {
            return false;
        }

        return hash_equals($legacyToken, $plainToken);
    }

    private function resolvePublishedProductBySlug(array $store, string $slug): ?array
    {
        $normalizedSlug = Str::slug($slug);
        $product = collect($this->publishedProducts($store))
            ->first(fn (array $item): bool => (string) ($item['slug'] ?? '') === $normalizedSlug);

        return is_array($product) ? $product : null;
    }

    private function productFileExists(array $product): bool
    {
        $path = trim((string) ($product['asset_path'] ?? ''));
        if ($path === '') {
            return false;
        }

        return Storage::disk('local')->exists($path);
    }

    private function isFreeProduct(array $product): bool
    {
        return (float) ($product['price'] ?? 0) <= 0;
    }

    private function deliverOrderDownloads(string $orderCode): void
    {
        $orders = $this->ordersPayload();
        $foundIndex = null;
        foreach ($orders as $index => $order) {
            if (! is_array($order)) {
                continue;
            }
            if ((string) ($order['order_code'] ?? '') !== $orderCode) {
                continue;
            }
            $foundIndex = $index;
            break;
        }
        if ($foundIndex === null) {
            return;
        }
        $order = $orders[$foundIndex];
        if (($order['download_email_sent'] ?? false) === true) {
            return;
        }
        $email = trim((string) ($order['customer']['email'] ?? ''));
        if ($email === '') {
            return;
        }

        $links = [];
        foreach (($order['items'] ?? []) as $item) {
            if (! is_array($item)) {
                continue;
            }
            $slug = (string) ($item['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $product = $this->resolvePublishedProductBySlug($this->storePayload(), $slug);
            if (! is_array($product) || ! $this->productFileExists($product)) {
                continue;
            }
            $tokenEntry = $this->createDownloadToken($slug, $email, $orderCode);
            $links[] = [
                'product_name' => (string) ($item['name'] ?? $slug),
                'download_url' => route('store.download.token', ['token' => $tokenEntry['token']]),
                'expired_at' => (string) ($tokenEntry['expires_at'] ?? ''),
                'max_downloads' => (int) ($tokenEntry['max_downloads'] ?? 3),
            ];
        }
        if (count($links) === 0) {
            return;
        }

        $orders[$foundIndex]['download_email_sent'] = true;
        $orders[$foundIndex]['download_links'] = $links;
        $orders[$foundIndex]['delivered_at'] = now()->toDateTimeString();
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_store_orders_payload'],
            ['value' => json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        $this->sendDownloadLinksEmail($email, $links, false);
    }

    private function sendDownloadLinksEmail(string $email, array $links, bool $isFree): void
    {
        $subject = $isFree ? 'Link Download Produk Gratis Kamu' : 'Link Download Produk Kamu';
        $lines = [
            'Terima kasih sudah membeli di PHN Store.',
            '',
            $isFree ? 'Produk gratis sudah siap didownload.' : 'Pembayaran berhasil diverifikasi. Produk sudah siap didownload.',
            '',
            'Link download (berlaku 1 hari, maksimal 3x download per link):',
        ];
        foreach ($links as $link) {
            $lines[] = '- '.((string) ($link['product_name'] ?? 'Product')).': '.((string) ($link['download_url'] ?? ''));
        }
        $lines[] = '';
        $lines[] = 'Salam, PHN Store';
        Mail::raw(implode("\n", $lines), function ($message) use ($email, $subject): void {
            $message->to($email)->subject($subject);
        });
    }

    private function checkAndRememberDokuRequestId(string $requestId): bool
    {
        $requestIdClean = trim($requestId);
        if ($requestIdClean === '') {
            return false;
        }

        $history = $this->dokuRequestReplayPayload();
        $minimumTime = now()->subDay()->timestamp;
        $filtered = [];
        $seen = false;
        foreach ($history as $entry) {
            if (! is_array($entry)) {
                continue;
            }
            $id = trim((string) ($entry['request_id'] ?? ''));
            $receivedAt = strtotime((string) ($entry['received_at'] ?? ''));
            if ($id === '' || $receivedAt === false || $receivedAt < $minimumTime) {
                continue;
            }
            if (hash_equals($id, $requestIdClean)) {
                $seen = true;
            }
            $filtered[] = ['request_id' => $id, 'received_at' => date('Y-m-d H:i:s', $receivedAt)];
        }
        if ($seen) {
            return false;
        }

        array_unshift($filtered, [
            'request_id' => $requestIdClean,
            'received_at' => now()->toDateTimeString(),
        ]);
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_store_doku_request_replay_payload'],
            ['value' => json_encode(array_values(array_slice($filtered, 0, 10000)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        return true;
    }

    private function dokuRequestReplayPayload(): array
    {
        $raw = (string) (LandingSetting::query()->where('key', 'cms_store_doku_request_replay_payload')->value('value') ?? '');
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function guardDownloadRateLimit(string $token): void
    {
        $ip = (string) request()->ip();
        $key = 'store-download-ip:'.sha1($ip.'|'.$token);
        if (RateLimiter::tooManyAttempts($key, 30)) {
            abort(429);
        }

        RateLimiter::hit($key, 60);
    }

    private function verifyDokuNotificationSignature(Request $request, array $store): array
    {
        $sharedKey = trim((string) ($store['doku_shared_key'] ?? ''));
        if ($sharedKey === '') {
            return ['ok' => true, 'message' => 'signature check skipped'];
        }

        $signatureHeader = trim((string) $request->header('Signature', ''));
        if ($signatureHeader === '') {
            return ['ok' => false, 'message' => 'missing signature header'];
        }
        if (! Str::startsWith($signatureHeader, 'HMACSHA256=')) {
            return ['ok' => false, 'message' => 'invalid signature scheme'];
        }
        $incomingSignature = trim((string) Str::after($signatureHeader, 'HMACSHA256='));
        if ($incomingSignature === '') {
            return ['ok' => false, 'message' => 'empty signature value'];
        }

        $clientIdHeader = trim((string) $request->header('Client-Id', ''));
        $requestId = trim((string) $request->header('Request-Id', ''));
        $requestTimestamp = trim((string) $request->header('Request-Timestamp', ''));
        $requestTarget = trim((string) $request->header('Request-Target', ''));
        if ($clientIdHeader === '' || $requestId === '' || $requestTimestamp === '') {
            return ['ok' => false, 'message' => 'missing required signature headers'];
        }

        $configuredClientId = trim((string) ($store['doku_client_id'] ?? ''));
        if ($configuredClientId === '') {
            $configuredClientId = trim((string) ($store['doku_merchant_id'] ?? ''));
        }
        if ($configuredClientId !== '' && ! hash_equals($configuredClientId, $clientIdHeader)) {
            return ['ok' => false, 'message' => 'client id mismatch'];
        }

        if (! $this->isValidRecentTimestamp($requestTimestamp, 300)) {
            return ['ok' => false, 'message' => 'timestamp expired/invalid'];
        }

        if ($requestTarget === '') {
            $requestTarget = '/'.ltrim($request->path(), '/');
        }

        $rawBody = (string) $request->getContent();
        $calculatedDigest = base64_encode(hash('sha256', $rawBody, true));
        $digestFromHeader = $this->normalizeDigestHeader((string) $request->header('Digest', ''));
        if ($digestFromHeader !== '' && ! hash_equals($calculatedDigest, $digestFromHeader)) {
            return ['ok' => false, 'message' => 'digest mismatch'];
        }
        $digestForSigning = $digestFromHeader !== '' ? $digestFromHeader : $calculatedDigest;

        $stringToSign = implode("\n", [
            "Client-Id:{$clientIdHeader}",
            "Request-Id:{$requestId}",
            "Request-Timestamp:{$requestTimestamp}",
            "Request-Target:{$requestTarget}",
            "Digest:{$digestForSigning}",
        ]);
        $expectedSignature = base64_encode(hash_hmac('sha256', $stringToSign, $sharedKey, true));
        if (! hash_equals($expectedSignature, $incomingSignature)) {
            return ['ok' => false, 'message' => 'signature mismatch'];
        }

        return ['ok' => true, 'message' => 'valid'];
    }

    private function normalizeDigestHeader(string $digest): string
    {
        $clean = trim($digest);
        if ($clean === '') {
            return '';
        }
        if (Str::startsWith($clean, 'SHA-256=')) {
            return trim((string) Str::after($clean, 'SHA-256='));
        }

        return $clean;
    }

    private function isValidRecentTimestamp(string $timestamp, int $maxSkewSeconds): bool
    {
        $parsed = strtotime($timestamp);
        if ($parsed === false) {
            return false;
        }

        return abs(time() - $parsed) <= $maxSkewSeconds;
    }
}
