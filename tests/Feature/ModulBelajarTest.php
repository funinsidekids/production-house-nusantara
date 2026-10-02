<?php

namespace Tests\Feature;

use App\Models\ModulMapel;
use App\Models\ModulMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ModulBelajarTest extends TestCase
{
    use DatabaseMigrations;

    public function test_index_page_shows_mapel(): void
    {
        $this->seed(\Database\Seeders\ModulBelajarSeeder::class);

        $response = $this->get('/modul-belajar');
        $response->assertOk();
        $response->assertSee('Matematika (Wajib & Peminatan)');
        $response->assertSee('Fisika');
    }

    public function test_mapel_page_and_json(): void
    {
        $this->seed(\Database\Seeders\ModulBelajarSeeder::class);

        $this->get('/modul-belajar/mtk')->assertOk()->assertSee('Limit Fungsi');

        $json = $this->getJson('/modul-belajar/mtk')->assertOk()->assertJsonPath('sukses', true);
        $json->assertJsonStructure(['sukses', 'data' => ['mapel', 'chapters']]);
    }

    public function test_material_page_and_progress_requires_login(): void
    {
        $this->seed(\Database\Seeders\ModulBelajarSeeder::class);

        $material = ModulMaterial::first();
        $chapter = $material->chapter;
        $mapel = $chapter->mapel;

        $this->get("/modul-belajar/{$mapel->kode}/{$chapter->id}/{$material->id}")
            ->assertOk()
            ->assertSee($material->judul);

        // Guest cannot store progress.
        $this->postJson(route('modul-belajar.progress', $material->id), ['selesai' => true])
            ->assertStatus(401);
    }

    public function test_authenticated_user_can_store_progress(): void
    {
        $this->seed(\Database\Seeders\ModulBelajarSeeder::class);

        $user = User::first() ?? User::factory()->create();
        $material = ModulMaterial::first();

        $this->actingAs($user)
            ->postJson(route('modul-belajar.progress', $material->id), ['selesai' => true, 'skor_latihan' => 85])
            ->assertOk()
            ->assertJsonPath('sukses', true);

        $row = DB::table('modul_progress')
            ->where('user_id', $user->id)
            ->where('material_id', $material->id)
            ->first();

        $this->assertNotNull($row);
        $this->assertTrue((bool) $row->selesai);
        $this->assertSame(85, (int) $row->skor_latihan);
        $this->assertNotNull($row->diselesaikan_pada);
    }

    public function test_inactive_mapel_is_hidden(): void
    {
        $this->seed(\Database\Seeders\ModulBelajarSeeder::class);
        ModulMapel::where('kode', 'fis')->update(['aktif' => false]);

        $this->get('/modul-belajar/mtk')->assertOk();
        $this->get('/modul-belajar/fis')->assertNotFound();
    }
}
