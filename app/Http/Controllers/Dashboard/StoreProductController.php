<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LandingSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoreProductController extends Controller
{
    private const MAX_REVISIONS = 10;

    public function index(): View
    {
        $settings = LandingSetting::query()->pluck('value', 'key')->all();
        $payload = $this->decodePayload((string) ($settings['cms_store_products_payload'] ?? ''));
        $revisions = $this->trimRevisions($this->decodeRevisions((string) ($settings['cms_store_products_revisions'] ?? '')));
        $products = $this->normalizeProducts($payload['products'] ?? $this->defaultProducts());

        return view('content.dashboard.store-product', [
            'form' => [
                'products_form' => $products,
                'products_json' => $this->prettyJson($products),
                'currency' => (string) ($payload['currency'] ?? 'IDR'),
                'tax_percent' => (string) ($payload['tax_percent'] ?? '11'),
                'checkout_mode' => (string) ($payload['checkout_mode'] ?? 'manual'),
                'doku_checkout_url' => (string) ($payload['doku_checkout_url'] ?? ''),
                'doku_merchant_id' => (string) ($payload['doku_merchant_id'] ?? ''),
                'doku_client_id' => (string) ($payload['doku_client_id'] ?? ''),
                'doku_shared_key' => (string) ($payload['doku_shared_key'] ?? ''),
                'doku_notify_token' => (string) ($payload['doku_notify_token'] ?? ''),
            ],
            'revisions' => $revisions,
            'is_admin' => $this->isAdminUser(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['nullable', 'in:save,restore'],
            'restore_revision_index' => ['nullable', 'integer'],
            'currency' => ['required', 'in:IDR,USD'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'checkout_mode' => ['required', 'in:manual,midtrans,xendit,stripe,doku'],
            'doku_checkout_url' => ['nullable', 'url', 'max:1000', 'required_if:checkout_mode,doku'],
            'doku_merchant_id' => ['nullable', 'string', 'max:120'],
            'doku_client_id' => ['nullable', 'string', 'max:120', 'required_if:checkout_mode,doku'],
            'doku_shared_key' => ['nullable', 'string', 'max:220', 'required_if:checkout_mode,doku'],
            'doku_notify_token' => ['nullable', 'string', 'max:220'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.name' => ['required', 'string', 'max:180'],
            'products.*.type' => ['required', 'string', 'max:120'],
            'products.*.sku' => ['nullable', 'string', 'max:120'],
            'products.*.price' => ['nullable', 'numeric', 'min:0'],
            'products.*.stock' => ['nullable', 'integer', 'min:0'],
            'products.*.thumbnail' => ['nullable', 'string', 'max:1000'],
            'products.*.photo' => ['nullable', 'string', 'max:1000'],
            'products.*.thumbnail_existing' => ['nullable', 'string', 'max:2000'],
            'products.*.photo_existing' => ['nullable', 'string', 'max:2000'],
            'products.*.thumbnail_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'products.*.photo_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'products.*.download_url' => ['nullable', 'string', 'max:1000'],
            'products.*.asset_path_existing' => ['nullable', 'string', 'max:1000'],
            'products.*.asset_file' => ['nullable', 'file', 'max:1024000'],
            'products.*.description' => ['nullable', 'string'],
            'products.*.function' => ['nullable', 'string'],
            'products.*.category' => ['nullable', 'string', 'max:120'],
            'products.*.tags' => ['nullable', 'string', 'max:400'],
            'products.*.license_type' => ['nullable', 'string', 'max:120'],
            'products.*.status' => ['required', 'in:draft,published,archived'],
        ]);

        $action = (string) ($data['action'] ?? 'save');
        $currentPayload = $this->decodePayload((string) (LandingSetting::query()->where('key', 'cms_store_products_payload')->value('value') ?? ''));
        $revisions = $this->trimRevisions($this->decodeRevisions((string) (LandingSetting::query()->where('key', 'cms_store_products_revisions')->value('value') ?? '')));
        $isAdmin = $this->isAdminUser();

        if ($action === 'restore') {
            if (! $isAdmin) {
                return redirect()
                    ->route('dashboard-store-product')
                    ->with('error', 'Hanya Admin yang dapat restore revisi product.');
            }
            $index = (int) ($data['restore_revision_index'] ?? -1);
            if ($index >= 0 && isset($revisions[$index]['payload']) && is_array($revisions[$index]['payload'])) {
                $restorePayload = $revisions[$index]['payload'];
                LandingSetting::query()->updateOrCreate(
                    ['key' => 'cms_store_products_payload'],
                    ['value' => json_encode($restorePayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
                );

                return redirect()
                    ->route('dashboard-store-product')
                    ->with('success', 'Revision Store Product berhasil dipulihkan.');
            }

            return redirect()
                ->route('dashboard-store-product')
                ->with('error', 'Revision tidak ditemukan.');
        }

        if (! empty($currentPayload)) {
            array_unshift($revisions, [
                'saved_at' => now()->toDateTimeString(),
                'summary' => 'Autosave before update',
                'payload' => $currentPayload,
            ]);
            $revisions = $this->trimRevisions($revisions);
        }

        $products = $this->processProductsForSave($request, $data['products'] ?? []);
        if (! $isAdmin) {
            $this->ensureNonAdminAddOnly($currentPayload, $data, $products);
        }
        $payload = [
            'currency' => $data['currency'],
            'tax_percent' => (float) ($data['tax_percent'] ?? 0),
            'checkout_mode' => $data['checkout_mode'],
            'doku_checkout_url' => (string) ($data['doku_checkout_url'] ?? ''),
            'doku_merchant_id' => (string) ($data['doku_merchant_id'] ?? ''),
            'doku_client_id' => (string) ($data['doku_client_id'] ?? ''),
            'doku_shared_key' => (string) ($data['doku_shared_key'] ?? ''),
            'doku_notify_token' => (string) ($data['doku_notify_token'] ?? ''),
            'products' => $products,
            'updated_at' => now()->toDateTimeString(),
        ];

        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_store_products_payload'],
            ['value' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_store_products_revisions'],
            ['value' => json_encode($revisions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        return redirect()
            ->route('dashboard-store-product')
            ->with('success', 'Store Product berhasil diperbarui.');
    }

    private function decodePayload(string $raw): array
    {
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function decodeRevisions(string $raw): array
    {
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->filter(fn ($item): bool => is_array($item) && isset($item['payload']) && is_array($item['payload']))
            ->values()
            ->all();
    }

    private function trimRevisions(array $revisions): array
    {
        return array_values(array_slice($revisions, 0, self::MAX_REVISIONS));
    }

    private function normalizeProducts(array $products): array
    {
        return collect($products)
            ->filter(fn ($item): bool => is_array($item))
            ->map(function (array $product): array {
                $name = trim((string) ($product['name'] ?? ''));

                return [
                    'name' => $name,
                    'slug' => Str::slug((string) ($product['slug'] ?? $name)),
                    'type' => $this->normalizeType((string) ($product['type'] ?? 'other')),
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
            ->filter(fn (array $product): bool => $product['name'] !== '')
            ->values()
            ->all();
    }

    private function prettyJson(array $value): string
    {
        return (string) json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function defaultProducts(): array
    {
        return [
            [
                'name' => 'Cinematic LUT Pack Vol.1',
                'slug' => 'cinematic-lut-pack-vol-1',
                'type' => 'LUT',
                'sku' => 'PHN-LUT-001',
                'price' => 149000,
                'stock' => 0,
                'thumbnail' => '',
                'photo' => '',
                'download_url' => '',
                'asset_path' => '',
                'description' => 'Paket LUT cinematic untuk color grading cepat.',
                'function' => 'Color grading untuk footage log profile.',
                'category' => 'Color',
                'tags' => 'lut,cinematic,color-grading',
                'license_type' => 'single-use',
                'status' => 'published',
            ],
        ];
    }

    private function normalizeType(string $type): string
    {
        $clean = trim($type);

        return $clean !== '' ? $clean : 'Other';
    }

    private function processProductsForSave(Request $request, array $products): array
    {
        $normalized = [];
        foreach ($products as $index => $product) {
            if (! is_array($product)) {
                continue;
            }
            $name = trim((string) ($product['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $thumbnail = $this->resolveImageAsset(
                $request->file("products.{$index}.thumbnail_file"),
                trim((string) ($product['thumbnail_existing'] ?? '')),
                trim((string) ($product['thumbnail'] ?? '')),
                "products.{$index}.thumbnail_file",
                'store-products/thumbnails'
            );
            $photo = $this->resolveImageAsset(
                $request->file("products.{$index}.photo_file"),
                trim((string) ($product['photo_existing'] ?? '')),
                trim((string) ($product['photo'] ?? '')),
                "products.{$index}.photo_file",
                'store-products/photos'
            );
            $existingPath = trim((string) ($product['asset_path_existing'] ?? ''));
            $uploadedFile = $request->file("products.{$index}.asset_file");
            $assetPath = $existingPath;
            if ($uploadedFile instanceof UploadedFile) {
                $assetPath = $uploadedFile->store('store-products', 'local');
                if ($existingPath !== '' && Storage::disk('local')->exists($existingPath)) {
                    Storage::disk('local')->delete($existingPath);
                }
            }
            if ($assetPath === '') {
                throw ValidationException::withMessages([
                    "products.{$index}.asset_file" => 'File produk wajib diupload.',
                ]);
            }
            $normalized[] = [
                'name' => $name,
                'slug' => Str::slug((string) ($product['slug'] ?? $name)),
                'type' => $this->normalizeType((string) ($product['type'] ?? 'other')),
                'sku' => (string) ($product['sku'] ?? ''),
                'price' => (float) ($product['price'] ?? 0),
                'stock' => max(0, (int) ($product['stock'] ?? 0)),
                'thumbnail' => $thumbnail,
                'photo' => $photo,
                'download_url' => (string) ($product['download_url'] ?? ''),
                'asset_path' => $assetPath,
                'description' => (string) ($product['description'] ?? ''),
                'function' => (string) ($product['function'] ?? ''),
                'category' => (string) ($product['category'] ?? ''),
                'tags' => (string) ($product['tags'] ?? ''),
                'license_type' => (string) ($product['license_type'] ?? ''),
                'status' => (string) ($product['status'] ?? 'draft'),
            ];
        }

        return $normalized;
    }

    private function isAdminUser(): bool
    {
        $user = Auth::user();
        if (! is_object($user)) {
            return false;
        }
        $primaryRole = Str::lower(trim((string) ($user->primary_role ?? '')));
        if ($primaryRole === 'admin') {
            return true;
        }
        $roleSlugs = (array) ($user->role_slugs ?? []);

        return collect($roleSlugs)
            ->map(fn ($slug): string => Str::lower(trim((string) $slug)))
            ->contains('admin');
    }

    private function ensureNonAdminAddOnly(array $currentPayload, array $incomingData, array $incomingProducts): void
    {
        $currentProducts = $this->normalizeProducts((array) ($currentPayload['products'] ?? []));
        if ((string) ($incomingData['currency'] ?? '') !== (string) ($currentPayload['currency'] ?? 'IDR')
            || (string) ($incomingData['checkout_mode'] ?? '') !== (string) ($currentPayload['checkout_mode'] ?? 'manual')
            || (string) ($incomingData['doku_checkout_url'] ?? '') !== (string) ($currentPayload['doku_checkout_url'] ?? '')
            || (string) ($incomingData['doku_merchant_id'] ?? '') !== (string) ($currentPayload['doku_merchant_id'] ?? '')
            || (string) ($incomingData['doku_client_id'] ?? '') !== (string) ($currentPayload['doku_client_id'] ?? '')
            || (string) ($incomingData['doku_shared_key'] ?? '') !== (string) ($currentPayload['doku_shared_key'] ?? '')
            || (string) ($incomingData['doku_notify_token'] ?? '') !== (string) ($currentPayload['doku_notify_token'] ?? '')
            || (float) ($incomingData['tax_percent'] ?? 0) !== (float) ($currentPayload['tax_percent'] ?? 0)
        ) {
            throw ValidationException::withMessages([
                'products' => 'Hanya Admin yang dapat mengubah pengaturan store.',
            ]);
        }

        $incomingBySlug = collect($incomingProducts)->keyBy(fn (array $item): string => (string) ($item['slug'] ?? ''))->all();
        foreach ($currentProducts as $currentProduct) {
            $slug = (string) ($currentProduct['slug'] ?? '');
            if ($slug === '' || ! isset($incomingBySlug[$slug])) {
                throw ValidationException::withMessages([
                    'products' => 'Hanya Admin yang dapat menghapus atau menyesuaikan product yang sudah ada.',
                ]);
            }
            $incomingProduct = $incomingBySlug[$slug];
            if (json_encode($incomingProduct, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !== json_encode($currentProduct, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) {
                throw ValidationException::withMessages([
                    'products' => 'Hanya Admin yang dapat mengedit product yang sudah diupload.',
                ]);
            }
        }
    }

    private function resolveImageAsset(
        mixed $uploadedFile,
        string $existingValue,
        string $urlValue,
        string $errorKey,
        string $directory
    ): string {
        $manualUrl = trim($urlValue);
        if ($uploadedFile instanceof UploadedFile) {
            $storedPath = $uploadedFile->store($directory, 'public');
            $this->deletePublicAssetIfManaged($existingValue);

            return Storage::url($storedPath);
        }
        if ($manualUrl !== '') {
            return $manualUrl;
        }
        if (trim($existingValue) !== '') {
            return trim($existingValue);
        }

        throw ValidationException::withMessages([
            $errorKey => 'Thumbnail/Photo wajib diupload. URL hanya alternatif.',
        ]);
    }

    private function deletePublicAssetIfManaged(string $value): void
    {
        $trimmed = trim($value);
        if (! Str::startsWith($trimmed, '/storage/')) {
            return;
        }
        $relative = ltrim(Str::after($trimmed, '/storage/'), '/');
        if ($relative === '') {
            return;
        }
        if (Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
        }
    }
}
