<?php

namespace Tests\Feature;

use App\Models\LandingSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreFlowSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::disk('local')->put('store-products/test-lut-pack.zip', 'sample-content');
        Storage::disk('local')->put('store-products/free-pack.zip', 'free-sample');

        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_store_products_payload'],
            ['value' => json_encode([
                'currency' => 'IDR',
                'tax_percent' => 11,
                'checkout_mode' => 'manual',
                'products' => [
                    [
                        'name' => 'Test LUT Pack',
                        'slug' => 'test-lut-pack',
                        'type' => 'LUT',
                        'sku' => 'TST-LUT-001',
                        'price' => 100000,
                        'stock' => 99,
                        'thumbnail' => '',
                        'photo' => '',
                        'download_url' => '',
                        'asset_path' => 'store-products/test-lut-pack.zip',
                        'description' => 'Produk test',
                        'function' => 'Color grading',
                        'category' => 'Color',
                        'tags' => 'lut,test',
                        'license_type' => 'single-use',
                        'status' => 'published',
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
    }

    public function test_store_public_pages_are_accessible(): void
    {
        $this->get('/store')->assertOk()->assertSee('Store Catalog');
        $this->get('/store/test-lut-pack')->assertOk()->assertSee('Test LUT Pack');
        $this->get('/store/cart')->assertOk()->assertSee('Cart');
    }

    public function test_store_cart_and_checkout_flow_works(): void
    {
        Mail::fake();
        $this->post('/store/cart/test-lut-pack/add', ['qty' => 2])->assertRedirect('/store/cart');
        $this->get('/store/cart')->assertOk()->assertSee('Test LUT Pack');
        $this->get('/store/checkout')->assertOk()->assertSee('Data Pembeli');

        $response = $this->post('/store/checkout', [
            'customer_name' => 'Tester',
            'customer_email' => 'tester@example.com',
            'customer_phone' => '08123456789',
            'customer_note' => 'test checkout',
        ]);
        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertNotNull($location);
        $this->get($location)->assertOk()->assertSee('Order Berhasil Dibuat');
    }

    public function test_store_checkout_redirects_to_doku_when_mode_is_doku(): void
    {
        Mail::fake();
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_store_products_payload'],
            ['value' => json_encode([
                'currency' => 'IDR',
                'tax_percent' => 11,
                'checkout_mode' => 'doku',
                'doku_checkout_url' => 'https://example.com/doku-checkout',
                'doku_client_id' => 'MCH-TEST-001',
                'doku_shared_key' => 'secret-shared-key-test',
                'doku_notify_token' => 'tokentest',
                'products' => [
                    [
                        'name' => 'Test LUT Pack',
                        'slug' => 'test-lut-pack',
                        'type' => 'LUT',
                        'sku' => 'TST-LUT-001',
                        'price' => 100000,
                        'stock' => 99,
                        'thumbnail' => '',
                        'photo' => '',
                        'download_url' => '',
                        'asset_path' => 'store-products/test-lut-pack.zip',
                        'description' => 'Produk test',
                        'function' => 'Color grading',
                        'category' => 'Color',
                        'tags' => 'lut,test',
                        'license_type' => 'single-use',
                        'status' => 'published',
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        $this->post('/store/cart/test-lut-pack/add', ['qty' => 1])->assertRedirect('/store/cart');
        $response = $this->post('/store/checkout', [
            'customer_name' => 'Tester',
            'customer_email' => 'tester@example.com',
            'customer_phone' => '08123456789',
            'customer_note' => 'test checkout doku',
        ]);

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');
        $this->assertStringContainsString('https://example.com/doku-checkout', $location);
        $this->assertStringContainsString('order_code=', $location);

        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
        $orderCode = (string) ($query['order_code'] ?? '');
        $this->assertNotSame('', $orderCode);

        $notifyPayload = [
            'order_code' => $orderCode,
            'status' => 'success',
            'transaction_id' => 'TXN-123',
            'notify_token' => 'tokentest',
        ];
        $notifyBody = json_encode($notifyPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->assertNotFalse($notifyBody);
        $requestId = 'REQ-TEST-001';
        $requestTimestamp = now()->toIso8601String();
        $requestTarget = '/store/payment/doku/notify';
        $digest = base64_encode(hash('sha256', $notifyBody, true));
        $stringToSign = "Client-Id:MCH-TEST-001\n"
            ."Request-Id:{$requestId}\n"
            ."Request-Timestamp:{$requestTimestamp}\n"
            ."Request-Target:{$requestTarget}\n"
            ."Digest:{$digest}";
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, 'secret-shared-key-test', true));

        $this->withHeaders([
            'Content-Type' => 'application/json',
            'Client-Id' => 'MCH-TEST-001',
            'Request-Id' => $requestId,
            'Request-Timestamp' => $requestTimestamp,
            'Request-Target' => $requestTarget,
            'Digest' => $digest,
            'Signature' => 'HMACSHA256='.$signature,
            'x-doku-token' => 'tokentest',
        ])->postJson('/store/payment/doku/notify', $notifyPayload)->assertOk()->assertJson(['ok' => true, 'status' => 'paid']);
        $this->withHeaders([
            'Content-Type' => 'application/json',
            'Client-Id' => 'MCH-TEST-001',
            'Request-Id' => $requestId,
            'Request-Timestamp' => $requestTimestamp,
            'Request-Target' => $requestTarget,
            'Digest' => $digest,
            'Signature' => 'HMACSHA256='.$signature,
            'x-doku-token' => 'tokentest',
        ])->postJson('/store/payment/doku/notify', $notifyPayload)->assertStatus(409);
    }

    public function test_free_product_flow_generates_token_and_download_link(): void
    {
        Mail::fake();
        Str::createRandomStringsUsing(static fn (): string => str_repeat('a', 64));
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_store_products_payload'],
            ['value' => json_encode([
                'currency' => 'IDR',
                'tax_percent' => 11,
                'checkout_mode' => 'manual',
                'products' => [
                    [
                        'name' => 'Free Pack',
                        'slug' => 'free-pack',
                        'type' => 'Plugin',
                        'sku' => 'FREE-001',
                        'price' => 0,
                        'stock' => 0,
                        'thumbnail' => '',
                        'photo' => '',
                        'download_url' => '',
                        'asset_path' => 'store-products/free-pack.zip',
                        'description' => 'Free produk',
                        'function' => 'free',
                        'category' => 'Free',
                        'tags' => 'free',
                        'license_type' => 'single-use',
                        'status' => 'published',
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        $this->post('/store/free-pack/purchase', [
            'customer_email' => 'freebuyer@example.com',
        ])->assertRedirect('/store');

        $tokensRaw = (string) (LandingSetting::query()->where('key', 'cms_store_download_tokens_payload')->value('value') ?? '');
        $tokens = json_decode($tokensRaw, true);
        $this->assertIsArray($tokens);
        $tokenHash = (string) ($tokens[0]['token_hash'] ?? '');
        $this->assertNotSame('', $tokenHash);
        $this->assertSame(hash('sha256', str_repeat('a', 64)), $tokenHash);
        $this->get('/download/'.str_repeat('a', 64))->assertOk();
        Str::createRandomStringsNormally();
    }

    public function test_download_token_is_limited_to_three_times_and_one_day_expiry(): void
    {
        $tokenEntry = [
            'token_hash' => hash('sha256', 'tokendownload123'),
            'product_slug' => 'test-lut-pack',
            'email' => 'buyer@example.com',
            'order_code' => null,
            'max_downloads' => 3,
            'download_count' => 0,
            'expires_at' => now()->addDay()->toDateTimeString(),
            'created_at' => now()->toDateTimeString(),
            'last_download_at' => null,
        ];
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_store_download_tokens_payload'],
            ['value' => json_encode([$tokenEntry], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        $this->get('/download/tokendownload123')->assertOk();
        $this->get('/download/tokendownload123')->assertOk();
        $this->get('/download/tokendownload123')->assertOk();
        $this->get('/download/tokendownload123')->assertStatus(403);
    }
}
