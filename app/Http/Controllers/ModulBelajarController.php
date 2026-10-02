<?php

namespace App\Http\Controllers;

use App\Models\ModulChapter;
use App\Models\ModulMapel;
use App\Models\ModulMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ModulBelajarController extends Controller
{
    public function index(): View
    {
        $mapels = ModulMapel::aktif()
            ->withCount(['chapters as bab_terbit_count' => fn ($q) => $q->where('terbit', true)])
            ->with(['chapters' => fn ($q) => $q->where('terbit', true)->withCount(['materials as materi_terbit_count' => fn ($m) => $m->where('terbit', true)])])
            ->get();

        return view('modul-belajar.index', [
            'mapels' => $mapels,
        ]);
    }

    public function show(string $kode): View|JsonResponse
    {
        $mapel = ModulMapel::aktif()->where('kode', $kode)->firstOrFail();

        $chapters = $mapel->chapters()
            ->where('terbit', true)
            ->with(['materials' => fn ($q) => $q->where('terbit', true)])
            ->get();

        if (request()->expectsJson()) {
            return response()->json([
                'sukses' => true,
                'data' => [
                    'mapel' => $mapel->only(['id', 'kode', 'nama', 'deskripsi', 'ikon', 'warna']),
                    'chapters' => $chapters,
                ],
            ]);
        }

        return view('modul-belajar.mapel', [
            'mapel' => $mapel,
            'chapters' => $chapters,
        ]);
    }

    public function material(string $kode, int $chapterId, int $materialId): View|JsonResponse
    {
        $mapel = ModulMapel::aktif()->where('kode', $kode)->firstOrFail();

        $chapter = $mapel->chapters()
            ->where('terbit', true)
            ->findOrFail($chapterId);

        $material = $chapter->materials()
            ->where('terbit', true)
            ->with('chapter')
            ->findOrFail($materialId);

        $prevNext = $this->resolvePrevNext($chapter, $material);

        if (request()->expectsJson()) {
            return response()->json([
                'sukses' => true,
                'data' => [
                    'mapel' => $mapel->only(['id', 'kode', 'nama', 'warna']),
                    'chapter' => $chapter->only(['id', 'judul', 'nomor']),
                    'material' => $material,
                    'navigation' => $prevNext,
                ],
            ]);
        }

        return view('modul-belajar.material', [
            'mapel' => $mapel,
            'chapter' => $chapter,
            'material' => $material,
            'prevNext' => $prevNext,
        ]);
    }

    public function progress(Request $request, int $materialId): JsonResponse
    {
        $validated = $request->validate([
            'selesai' => ['sometimes', 'boolean'],
            'skor_latihan' => ['sometimes', 'integer', 'min:0', 'max:100'],
        ]);

        abort_unless(auth()->check(), 401, 'Silakan login untuk menyimpan progres.');

        $material = ModulMaterial::query()->findOrFail($materialId);
        $userId = auth()->id();

        $existing = DB::table('modul_progress')
            ->where('user_id', $userId)
            ->where('material_id', $material->id)
            ->first();

        $payload = [
            'user_id' => $userId,
            'material_id' => $material->id,
            'selesai' => (bool) ($validated['selesai'] ?? ! $existing?->selesai),
            'skor_latihan' => $validated['skor_latihan'] ?? $existing?->skor_latihan,
            'diselesaikan_pada' => null,
            'updated_at' => now(),
        ];

        if ($payload['selesai']) {
            $payload['diselesaikan_pada'] = $existing?->diselesaikan_pada ?? now();
        }

        if (! $existing) {
            $payload['created_at'] = now();
            DB::table('modul_progress')->insert($payload);
        } else {
            DB::table('modul_progress')
                ->where('id', $existing->id)
                ->update($payload);
        }

        return response()->json([
            'sukses' => true,
            'message' => 'Progres belajar tersimpan.',
            'data' => [
                'material_id' => $material->id,
                'selesai' => $payload['selesai'],
            ],
        ]);
    }

    private function resolvePrevNext(ModulChapter $chapter, ModulMaterial $material): array
    {
        $flat = $chapter->mapel
            ->chapters()
            ->where('terbit', true)
            ->with(['materials' => fn ($q) => $q->where('terbit', true)])
            ->get()
            ->flatMap(fn (ModulChapter $c) => $c->materials->map(fn (ModulMaterial $m) => [
                'id' => $m->id,
                'judul' => $m->judul,
                'chapter_id' => $c->id,
                'mapel_kode' => $chapter->mapel->kode,
            ]))
            ->values();

        $index = $flat->search(fn (array $item) => $item['id'] === $material->id);

        return [
            'prev' => $index > 0 ? $flat[$index - 1] : null,
            'next' => $index !== false && $index < $flat->count() - 1 ? $flat[$index + 1] : null,
        ];
    }
}
