<?php

namespace Tests\Feature;

use App\Models\LandingSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RolePermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_admin_can_create_role_from_permission_management_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin-role@test.com',
            'password' => 'Password!123',
            'primary_role' => 'Admin',
        ]);
        $this->actingAs($admin);

        $this->post('/dashboard/user/role', [
            'role_name' => 'Senior Editor',
            'role_slug' => 'senior_editor',
            'description' => 'Advanced editor',
            'color_label' => '#123456',
            'resource_scope' => 'all_projects',
            'permissions' => [
                'dashboard' => ['create' => '0', 'read' => '1', 'update' => '0', 'delete' => '0', 'export' => '0', 'publish' => '0'],
                'cms' => ['create' => '1', 'read' => '1', 'update' => '1', 'delete' => '0', 'export' => '1', 'publish' => '1'],
                'calendar' => ['create' => '1', 'read' => '1', 'update' => '1', 'delete' => '0', 'export' => '0', 'publish' => '0'],
                'finance' => ['create' => '0', 'read' => '0', 'update' => '0', 'delete' => '0', 'export' => '0', 'publish' => '0'],
                'users' => ['create' => '0', 'read' => '1', 'update' => '0', 'delete' => '0', 'export' => '0', 'publish' => '0'],
            ],
        ])->assertRedirect('/dashboard/user/role?edit=senior_editor');

        $payload = (string) (LandingSetting::query()->where('key', 'cms_user_roles_payload')->value('value') ?? '');
        $this->assertStringContainsString('senior_editor', $payload);
    }

    public function test_non_admin_cannot_access_role_permission_management(): void
    {
        $user = User::query()->create([
            'name' => 'Editor',
            'email' => 'editor-role@test.com',
            'password' => 'Password!123',
            'primary_role' => 'Editor',
        ]);
        $this->actingAs($user);

        $this->get('/dashboard/user/role')->assertStatus(403);
        $this->get('/dashboard/user/role/export/pdf')->assertStatus(403);
        $this->post('/dashboard/user/role', [
            'role_name' => 'Blocked Role',
            'role_slug' => 'blocked_role',
            'resource_scope' => 'own_project',
        ])->assertStatus(403);
    }

    public function test_admin_can_export_role_matrix_as_pdf(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin PDF',
            'email' => 'admin-pdf@test.com',
            'password' => 'Password!123',
            'primary_role' => 'Admin',
        ]);
        $this->actingAs($admin);

        $response = $this->get('/dashboard/user/role/export/pdf');
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_non_admin_can_add_new_store_product_but_cannot_edit_existing(): void
    {
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_store_products_payload'],
            ['value' => json_encode([
                'currency' => 'IDR',
                'tax_percent' => 11,
                'checkout_mode' => 'manual',
                'products' => [
                    [
                        'name' => 'Existing Product',
                        'slug' => 'existing-product',
                        'type' => 'Plugin',
                        'sku' => 'EX-001',
                        'price' => 100000,
                        'stock' => 10,
                        'thumbnail' => '/storage/store-products/thumbnails/ex.jpg',
                        'photo' => '/storage/store-products/photos/ex.jpg',
                        'download_url' => '',
                        'asset_path' => 'store-products/existing.zip',
                        'description' => 'existing',
                        'function' => 'existing',
                        'category' => 'Existing',
                        'tags' => 'existing',
                        'license_type' => 'single-use',
                        'status' => 'published',
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
        Storage::disk('local')->put('store-products/existing.zip', 'existing-content');

        $user = User::query()->create([
            'name' => 'Crew',
            'email' => 'crew-store@test.com',
            'password' => 'Password!123',
            'primary_role' => 'Crew',
        ]);
        $this->actingAs($user);

        $this->post('/dashboard/store/product', [
            'action' => 'save',
            'currency' => 'IDR',
            'tax_percent' => 11,
            'checkout_mode' => 'manual',
            'products' => [
                [
                    'name' => 'Existing Product',
                    'type' => 'Plugin',
                    'sku' => 'EX-001',
                    'price' => 100000,
                    'stock' => 10,
                    'thumbnail' => '',
                    'photo' => '',
                    'thumbnail_existing' => '/storage/store-products/thumbnails/ex.jpg',
                    'photo_existing' => '/storage/store-products/photos/ex.jpg',
                    'asset_path_existing' => 'store-products/existing.zip',
                    'download_url' => '',
                    'description' => 'existing',
                    'function' => 'existing',
                    'category' => 'Existing',
                    'tags' => 'existing',
                    'license_type' => 'single-use',
                    'status' => 'published',
                ],
                [
                    'name' => 'New Product',
                    'type' => 'Template',
                    'sku' => 'NW-001',
                    'price' => 50000,
                    'stock' => 0,
                    'thumbnail' => '',
                    'photo' => '',
                    'download_url' => '',
                    'description' => 'new',
                    'function' => 'new',
                    'category' => 'New',
                    'tags' => 'new',
                    'license_type' => 'single-use',
                    'status' => 'published',
                    'thumbnail_file' => UploadedFile::fake()->create('thumb.jpg', 120, 'image/jpeg'),
                    'photo_file' => UploadedFile::fake()->create('photo.jpg', 120, 'image/jpeg'),
                    'asset_file' => UploadedFile::fake()->create('asset.zip', 200, 'application/zip'),
                ],
            ],
        ])->assertRedirect('/dashboard/store/product');

        $this->post('/dashboard/store/product', [
            'action' => 'save',
            'currency' => 'IDR',
            'tax_percent' => 11,
            'checkout_mode' => 'manual',
            'products' => [
                [
                    'name' => 'Existing Product',
                    'type' => 'Plugin',
                    'sku' => 'EX-001',
                    'price' => 999999,
                    'stock' => 10,
                    'thumbnail' => '',
                    'photo' => '',
                    'thumbnail_existing' => '/storage/store-products/thumbnails/ex.jpg',
                    'photo_existing' => '/storage/store-products/photos/ex.jpg',
                    'asset_path_existing' => 'store-products/existing.zip',
                    'download_url' => '',
                    'description' => 'existing',
                    'function' => 'existing',
                    'category' => 'Existing',
                    'tags' => 'existing',
                    'license_type' => 'single-use',
                    'status' => 'published',
                ],
            ],
        ])->assertSessionHasErrors('products');
    }
}
