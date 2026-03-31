<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Models\LandingSetting;
use App\Models\User;
use App\Models\VideoAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdminModuleController extends Controller
{
    public function show(Request $request, string $section, string $item): JsonResponse
    {
        $config = $this->moduleConfig($section, $item);
        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));
        $sortBy = (string) $request->query('sort_by', ($config['default_sort'] ?? 'updated_at'));
        $sortDir = Str::lower((string) $request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(5, min(100, (int) $request->query('per_page', 15)));

        $baseRows = $this->baseRows($section, $item);
        $customRows = $this->customRows($section, $item);
        $rows = $baseRows->concat($customRows);

        if ($search !== '') {
            $needle = Str::lower($search);
            $searchKeys = $config['search_keys'] ?? ['title', 'owner', 'note'];
            $rows = $rows->filter(function (array $row) use ($needle, $searchKeys): bool {
                foreach ($searchKeys as $key) {
                    if (Str::contains(Str::lower((string) ($row[$key] ?? '')), $needle)) {
                        return true;
                    }
                }

                return false;
            });
        }

        if ($status !== '') {
            $rows = $rows->filter(fn (array $row): bool => (string) ($row['status'] ?? '') === $status);
        }

        $rows = $this->sortRows($rows, $sortBy, $sortDir);
        $total = $rows->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);
        $paginatedRows = $rows
            ->slice(($page - 1) * $perPage, $perPage)
            ->values()
            ->all();

        $statusOptions = collect($config['status_values'] ?? [])
            ->merge(
                $rows
                    ->pluck('status')
                    ->filter(fn ($value): bool => filled($value))
                    ->unique()
                    ->values()
            )
            ->unique()
            ->values()
            ->all();

        return response()->json([
            'section' => $section,
            'item' => $item,
            'title' => Str::headline($section).' / '.Str::headline($item),
            'columns' => $config['columns'],
            'formFields' => $config['form_fields'],
            'sectionDescription' => $config['section_description'],
            'itemDescription' => $config['item_description'],
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'sorting' => [
                'sortBy' => $sortBy,
                'sortDir' => $sortDir,
                'options' => collect($config['columns'])->pluck('key')->values()->all(),
            ],
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'lastPage' => $lastPage,
            ],
            'searchKeys' => $config['search_keys'],
            'statusOptions' => $statusOptions,
            'rows' => $paginatedRows,
        ]);
    }

    public function store(Request $request, string $section, string $item): JsonResponse
    {
        $config = $this->moduleConfig($section, $item);
        $data = $request->validate($this->validationRules($config['form_fields']));

        $settingsKey = $this->settingsKey($section, $item);
        $setting = LandingSetting::query()->firstOrCreate(['key' => $settingsKey], ['value' => '[]']);
        $existing = collect(json_decode((string) $setting->value, true) ?: []);

        $entry = $this->buildEntryFromForm($config['form_fields'], $data, $item)->merge([
            'id' => 'custom-'.Str::ulid(),
            'updated_at' => now()->toDateTimeString(),
            'source' => 'custom',
            'can_edit' => true,
            'can_delete' => true,
        ]);

        $setting->update([
            'value' => json_encode($existing->push($entry->all())->values()->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return response()->json([
            'message' => 'Entry berhasil disimpan',
            'entry' => $entry->all(),
        ], 201);
    }

    public function update(Request $request, string $section, string $item, string $entryId): JsonResponse
    {
        $config = $this->moduleConfig($section, $item);
        $data = $request->validate($this->validationRules($config['form_fields']));

        $setting = LandingSetting::query()->firstOrCreate(
            ['key' => $this->settingsKey($section, $item)],
            ['value' => '[]']
        );
        $rows = collect(json_decode((string) $setting->value, true) ?: []);
        $updated = false;

        $rows = $rows->map(function (array $row) use ($entryId, $config, $data, $item, &$updated): array {
            if ((string) ($row['id'] ?? '') !== $entryId) {
                return $row;
            }

            $updated = true;
            $patched = $this->buildEntryFromForm($config['form_fields'], $data, $item)->all();
            $patched['id'] = $entryId;
            $patched['updated_at'] = now()->toDateTimeString();
            $patched['source'] = 'custom';
            $patched['can_edit'] = true;
            $patched['can_delete'] = true;

            return $patched;
        });

        if (! $updated) {
            return response()->json(['message' => 'Entry tidak ditemukan'], 404);
        }

        $this->persistCustomRows($setting, $rows);

        return response()->json(['message' => 'Entry berhasil diupdate']);
    }

    public function destroy(string $section, string $item, string $entryId): JsonResponse
    {
        $setting = LandingSetting::query()->where('key', $this->settingsKey($section, $item))->first();
        if (! $setting) {
            return response()->json(['message' => 'Entry tidak ditemukan'], 404);
        }

        $rows = collect(json_decode((string) $setting->value, true) ?: []);
        $before = $rows->count();
        $rows = $rows->reject(fn (array $row): bool => (string) ($row['id'] ?? '') === $entryId)->values();

        if ($rows->count() === $before) {
            return response()->json(['message' => 'Entry tidak ditemukan'], 404);
        }

        $this->persistCustomRows($setting, $rows);

        return response()->json(['message' => 'Entry berhasil dihapus']);
    }

    private function baseRows(string $section, string $item): Collection
    {
        if ($section === 'projects') {
            return HeroSlide::query()
                ->latest('updated_at')
                ->limit(120)
                ->get(['id', 'title', 'is_active', 'cta_text', 'caption', 'updated_at'])
                ->map(fn (HeroSlide $slide): array => [
                    'id' => 'project-'.$slide->id,
                    'title' => $slide->title ?: 'Untitled Project',
                    'status' => $slide->is_active ? 'active' : 'on-hold',
                    'client' => $slide->cta_text ?: 'Nusantara Client',
                    'deadline' => optional($slide->updated_at)?->addDays(14)?->toDateString(),
                    'pipeline_stage' => Str::headline($item),
                    'owner' => 'Project Lead',
                    'note' => Str::limit((string) $slide->caption, 100),
                    'updated_at' => optional($slide->updated_at)?->toDateTimeString(),
                    'source' => 'mysql',
                    'can_edit' => false,
                    'can_delete' => false,
                ]);
        }

        if ($section === 'finance') {
            return VideoAsset::query()
                ->latest('updated_at')
                ->limit(120)
                ->get(['id', 'title', 'status', 'duration_seconds', 'updated_at'])
                ->map(fn (VideoAsset $asset): array => [
                    'id' => 'finance-'.$asset->id,
                    'invoice_no' => 'INV-'.now()->format('Y').'-'.str_pad((string) $asset->id, 4, '0', STR_PAD_LEFT),
                    'title' => $asset->title,
                    'status' => in_array($asset->status, ['ready', 'processing'], true) ? 'pending' : 'paid',
                    'amount' => (string) (($asset->duration_seconds ?: 60) * 15000),
                    'payment_method' => 'Bank Transfer',
                    'due_date' => optional($asset->updated_at)?->addDays(7)?->toDateString(),
                    'owner' => 'Finance Team',
                    'note' => 'Auto-generated from asset',
                    'updated_at' => optional($asset->updated_at)?->toDateTimeString(),
                    'source' => 'mysql',
                    'can_edit' => false,
                    'can_delete' => false,
                ]);
        }

        if ($section === 'users') {
            return User::query()
                ->latest('updated_at')
                ->limit(80)
                ->get(['id', 'name', 'email', 'updated_at'])
                ->map(fn (User $user): array => [
                    'id' => 'user-'.$user->id,
                    'title' => $user->name,
                    'role' => Str::headline($item),
                    'status' => 'active',
                    'email' => $user->email,
                    'owner' => $user->name,
                    'note' => 'User role synced from menu',
                    'updated_at' => optional($user->updated_at)?->toDateTimeString(),
                    'source' => 'mysql',
                    'can_edit' => false,
                    'can_delete' => false,
                ]);
        }

        if ($section === 'settings') {
            return LandingSetting::query()
                ->latest('updated_at')
                ->limit(120)
                ->get(['id', 'key', 'value', 'updated_at'])
                ->map(fn (LandingSetting $setting): array => [
                    'id' => (string) $setting->id,
                    'title' => $setting->key,
                    'status' => 'active',
                    'owner' => 'System',
                    'note' => Str::limit((string) $setting->value, 120),
                    'value' => Str::limit((string) $setting->value, 80),
                    'updated_at' => optional($setting->updated_at)?->toDateTimeString(),
                    'source' => 'mysql',
                    'can_edit' => false,
                    'can_delete' => false,
                ]);
        }

        if ($section === 'media' || $section === 'production' || $section === 'dashboard') {
            return VideoAsset::query()
                ->latest('updated_at')
                ->limit(120)
                ->get(['id', 'title', 'status', 'duration_seconds', 'updated_at'])
                ->map(fn (VideoAsset $asset): array => [
                    'id' => (string) $asset->id,
                    'title' => $asset->title,
                    'status' => $asset->status,
                    'owner' => 'Media Team',
                    'file_type' => Str::headline($item),
                    'note' => 'Duration: '.($asset->duration_seconds ?: 0).'s',
                    'updated_at' => optional($asset->updated_at)?->toDateTimeString(),
                    'source' => 'mysql',
                    'can_edit' => false,
                    'can_delete' => false,
                ]);
        }

        return HeroSlide::query()
            ->latest('updated_at')
            ->limit(120)
            ->get(['id', 'title', 'is_active', 'sort_order', 'updated_at'])
            ->map(fn (HeroSlide $slide): array => [
                'id' => (string) $slide->id,
                'title' => $slide->title ?: 'Untitled Slide',
                'status' => $slide->is_active ? 'active' : 'inactive',
                'owner' => 'Creative Team',
                'note' => 'Sort order: '.(string) $slide->sort_order,
                'pipeline_stage' => Str::headline($item),
                'updated_at' => optional($slide->updated_at)?->toDateTimeString(),
                'source' => 'mysql',
                'can_edit' => false,
                'can_delete' => false,
            ]);
    }

    private function customRows(string $section, string $item): Collection
    {
        $raw = LandingSetting::query()->where('key', $this->settingsKey($section, $item))->value('value');

        return collect(json_decode((string) $raw, true) ?: [])->map(function (array $row): array {
            $row['source'] = 'custom';
            $row['can_edit'] = true;
            $row['can_delete'] = true;

            return $row;
        });
    }

    private function settingsKey(string $section, string $item): string
    {
        return 'admin_module_entries_'.Str::slug($section).'_'.Str::slug($item);
    }

    private function validationRules(array $formFields): array
    {
        $rules = [];
        foreach ($formFields as $field) {
            $required = (bool) ($field['required'] ?? false);
            $type = (string) ($field['type'] ?? 'text');
            $baseRules = [$required ? 'required' : 'nullable'];
            $baseRules[] = match ($type) {
                'number' => 'numeric',
                'date' => 'date',
                default => 'string',
            };
            if ($type !== 'number' && $type !== 'date') {
                $baseRules[] = 'max:500';
            }
            $rules[$field['key']] = $baseRules;
        }

        return $rules;
    }

    private function buildEntryFromForm(array $formFields, array $data, string $item): Collection
    {
        $entry = collect();
        foreach ($formFields as $field) {
            $key = $field['key'];
            $entry->put($key, $data[$key] ?? ($field['default'] ?? ''));
        }
        if (! $entry->get('title')) {
            $entry->put('title', Str::headline($item).' '.now()->format('His'));
        }
        if (! $entry->get('status')) {
            $entry->put('status', 'active');
        }

        return $entry;
    }

    private function persistCustomRows(LandingSetting $setting, Collection $rows): void
    {
        $setting->update([
            'value' => json_encode($rows->values()->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    private function sortRows(Collection $rows, string $sortBy, string $sortDir): Collection
    {
        $sorted = $sortDir === 'asc'
            ? $rows->sortBy(fn (array $row) => $row[$sortBy] ?? null)
            : $rows->sortByDesc(fn (array $row) => $row[$sortBy] ?? null);

        return $sorted->values();
    }

    private function moduleConfig(string $section, string $item): array
    {
        $descriptions = $this->moduleDescriptions($section, $item);

        if ($section === 'projects') {
            return [
                'section_description' => $descriptions['section'],
                'item_description' => $descriptions['item'],
                'search_keys' => ['title', 'client', 'pipeline_stage', 'owner', 'note'],
                'status_values' => ['draft', 'active', 'on-hold', 'completed'],
                'default_sort' => 'updated_at',
                'columns' => [
                    ['key' => 'title', 'label' => 'Project'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'client', 'label' => 'Client'],
                    ['key' => 'deadline', 'label' => 'Deadline'],
                    ['key' => 'pipeline_stage', 'label' => 'Pipeline'],
                    ['key' => 'owner', 'label' => 'PIC'],
                    ['key' => 'updated_at', 'label' => 'Updated'],
                ],
                'form_fields' => [
                    ['key' => 'title', 'label' => 'Project Name', 'type' => 'text', 'required' => true],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['draft', 'active', 'on-hold', 'completed']],
                    ['key' => 'client', 'label' => 'Client', 'type' => 'text', 'required' => true],
                    ['key' => 'deadline', 'label' => 'Deadline', 'type' => 'date', 'required' => true],
                    ['key' => 'pipeline_stage', 'label' => 'Pipeline Stage', 'type' => 'text', 'required' => true, 'default' => Str::headline($item)],
                    ['key' => 'owner', 'label' => 'PIC', 'type' => 'text', 'required' => false],
                    ['key' => 'note', 'label' => 'Notes', 'type' => 'textarea', 'required' => false],
                ],
            ];
        }

        if ($section === 'finance') {
            return [
                'section_description' => $descriptions['section'],
                'item_description' => $descriptions['item'],
                'search_keys' => ['invoice_no', 'title', 'owner', 'note'],
                'status_values' => ['pending', 'paid', 'failed', 'partial'],
                'default_sort' => 'updated_at',
                'columns' => [
                    ['key' => 'invoice_no', 'label' => 'Invoice No'],
                    ['key' => 'title', 'label' => 'Description'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'amount', 'label' => 'Amount'],
                    ['key' => 'payment_method', 'label' => 'Method'],
                    ['key' => 'due_date', 'label' => 'Due Date'],
                    ['key' => 'updated_at', 'label' => 'Updated'],
                ],
                'form_fields' => [
                    ['key' => 'invoice_no', 'label' => 'Invoice No', 'type' => 'text', 'required' => true],
                    ['key' => 'title', 'label' => 'Description', 'type' => 'text', 'required' => true],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['pending', 'paid', 'failed', 'partial']],
                    ['key' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true],
                    ['key' => 'payment_method', 'label' => 'Payment Method', 'type' => 'text', 'required' => true],
                    ['key' => 'due_date', 'label' => 'Due Date', 'type' => 'date', 'required' => true],
                    ['key' => 'owner', 'label' => 'PIC', 'type' => 'text', 'required' => false],
                    ['key' => 'note', 'label' => 'Notes', 'type' => 'textarea', 'required' => false],
                ],
            ];
        }

        if ($section === 'users') {
            return [
                'section_description' => $descriptions['section'],
                'item_description' => $descriptions['item'],
                'search_keys' => ['title', 'email', 'role', 'note'],
                'status_values' => ['active', 'inactive', 'suspended'],
                'default_sort' => 'updated_at',
                'columns' => [
                    ['key' => 'title', 'label' => 'Name'],
                    ['key' => 'role', 'label' => 'Role'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'updated_at', 'label' => 'Updated'],
                ],
                'form_fields' => [
                    ['key' => 'title', 'label' => 'Name', 'type' => 'text', 'required' => true],
                    ['key' => 'role', 'label' => 'Role', 'type' => 'text', 'required' => true, 'default' => Str::headline($item)],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['active', 'inactive', 'suspended']],
                    ['key' => 'email', 'label' => 'Email', 'type' => 'text', 'required' => true],
                    ['key' => 'note', 'label' => 'Notes', 'type' => 'textarea', 'required' => false],
                ],
            ];
        }

        return [
            'section_description' => $descriptions['section'],
            'item_description' => $descriptions['item'],
            'search_keys' => ['title', 'status', 'owner', 'note'],
            'status_values' => ['active', 'draft', 'review', 'approved', 'pending'],
            'default_sort' => 'updated_at',
            'columns' => [
                ['key' => 'title', 'label' => 'Title'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'owner', 'label' => 'Owner'],
                ['key' => 'note', 'label' => 'Notes'],
                ['key' => 'updated_at', 'label' => 'Updated'],
            ],
            'form_fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
                ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['active', 'draft', 'review', 'approved', 'pending']],
                ['key' => 'owner', 'label' => 'Owner', 'type' => 'text', 'required' => false],
                ['key' => 'note', 'label' => 'Notes', 'type' => 'textarea', 'required' => false],
            ],
        ];
    }

    private function moduleDescriptions(string $section, string $item): array
    {
        $sectionDescriptions = [
            'dashboard' => 'Menu ringkasan semua aktivitas.',
            'calendar' => 'Untuk jadwal dan timeline.',
            'projects' => 'Mengelola semua project.',
            'production' => 'Tahapan produksi film / video.',
            'script-manager' => 'Untuk penulisan naskah.',
            'media' => 'Penyimpanan file.',
            'clients' => 'Data client.',
            'finance' => 'Keuangan.',
            'website' => 'Untuk website production house.',
            'users' => 'Hak akses user.',
            'settings' => 'Pengaturan sistem.',
        ];

        $itemDescriptions = [
            'overview-stats' => 'Statistik utama (project, client, income, progress).',
            'recent-projects' => 'Project terbaru.',
            'production-pipeline' => 'Posisi project dalam proses produksi.',
            'script-storyboard' => 'Status script dan storyboard.',
            'media-summary' => 'Jumlah video, foto, audio, dll.',
            'client-orders' => 'Data client dan pesanan.',
            'finance-summary' => 'Ringkasan keuangan.',
            'charts' => 'Grafik project, income, dan produksi.',
            'timeline' => 'Urutan proses project.',
            'schedule' => 'Jadwal harian / mingguan.',
            'tasks' => 'Tugas crew.',
            'all' => 'Daftar semua project.',
            'add' => 'Tambah project baru.',
            'categories' => 'Jenis project.',
            'status-pipeline' => 'Status produksi project.',
            'pre-production' => 'Persiapan.',
            'shooting' => 'Pengambilan gambar.',
            'editing' => 'Edit video.',
            'color-grading' => 'Pewarnaan.',
            'sound' => 'Audio & musik.',
            'rendering' => 'Export video.',
            'released' => 'Project selesai.',
            'scripts-draft' => 'Script tahap Draft.',
            'scripts-review' => 'Script tahap Review.',
            'scripts-approved' => 'Script tahap Approved.',
            'scripts-revision' => 'Script tahap Revision.',
            'scripts-final' => 'Script tahap Final.',
            'storyboard' => 'Gambar adegan.',
            'dialogue' => 'Dialog.',
            'narration' => 'Narasi / voice over.',
            'videos' => 'File video.',
            'images' => 'Foto / gambar.',
            'audio' => 'Suara.',
            'music' => 'Musik.',
            'lut-color' => 'Preset warna.',
            'subtitle' => 'Teks subtitle.',
            'client-list' => 'Daftar client.',
            'add-client' => 'Tambah client.',
            'orders' => 'Pesanan.',
            'contracts' => 'Kontrak.',
            'invoice' => 'Tagihan.',
            'payments' => 'Pembayaran.',
            'expenses' => 'Pengeluaran.',
            'reports' => 'Laporan keuangan.',
            'pages' => 'Halaman website.',
            'portfolio' => 'Karya / film.',
            'blog-news' => 'Artikel.',
            'testimonials' => 'Testimoni.',
            'team' => 'Tim.',
            'contact' => 'Kontak.',
            'admin' => 'Semua akses.',
            'editor' => 'Editing.',
            'director' => 'Approval.',
            'writer' => 'Script.',
            'crew' => 'Tim produksi.',
            'general' => 'Pengaturan umum.',
            'logo' => 'Logo.',
            'theme' => 'Tampilan.',
            'email' => 'SMTP.',
            'seo' => 'Meta website.',
            'api' => 'Integrasi sistem.',
        ];

        return [
            'section' => $sectionDescriptions[$section] ?? 'Modul operasional.',
            'item' => $itemDescriptions[$item] ?? 'Submenu operasional.',
        ];
    }
}
