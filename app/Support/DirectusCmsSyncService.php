<?php

namespace App\Support;

use App\Models\HeroSlide;
use App\Models\LandingSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DirectusCmsSyncService
{
    public function provisionCollections(array $config): array
    {
        $validationError = $this->validateConfig($config);
        if ($validationError !== null) {
            return ['ok' => false, 'message' => $validationError];
        }

        try {
            $blueprint = $this->loadBlueprint();
            $collectionMap = $this->collectionMap($config);
            $createdCollections = 0;
            $createdFields = 0;

            foreach ($blueprint['collections'] as $collectionBlueprint) {
                if (! is_array($collectionBlueprint)) {
                    continue;
                }
                $sourceCollectionName = (string) ($collectionBlueprint['collection'] ?? '');
                if ($sourceCollectionName === '') {
                    continue;
                }
                $targetCollectionName = $collectionMap[$sourceCollectionName] ?? $sourceCollectionName;
                $createdCollections += $this->ensureCollection($targetCollectionName, $collectionBlueprint, $config) ? 1 : 0;
                $fieldBlueprints = $this->fieldsByCollection($blueprint, $sourceCollectionName);
                $createdFields += $this->ensureFields($targetCollectionName, $fieldBlueprints, $config);
            }
            $accessResult = $this->provisionRolesAndPermissions($collectionMap, $config);

            return [
                'ok' => true,
                'message' => "Provision selesai. Collections dibuat: {$createdCollections}, fields dibuat: {$createdFields}, roles dibuat: {$accessResult['roles_created']}, permission dibuat: {$accessResult['permissions_created']}, permission diupdate: {$accessResult['permissions_updated']}.",
            ];
        } catch (\Throwable $exception) {
            return ['ok' => false, 'message' => 'Provision Directus gagal: '.$exception->getMessage()];
        }
    }

    public function push(array $config): array
    {
        $validationError = $this->validateConfig($config);
        if ($validationError !== null) {
            return ['ok' => false, 'message' => $validationError];
        }

        try {
            $settingsResult = $this->pushLandingSettings($config);
            $slidesResult = $this->pushHeroSlides($config);

            return [
                'ok' => true,
                'message' => "Push selesai. Settings: {$settingsResult['updated']} updated, {$settingsResult['created']} created. Slides: {$slidesResult['updated']} updated, {$slidesResult['created']} created.",
            ];
        } catch (\Throwable $exception) {
            return ['ok' => false, 'message' => 'Push Directus gagal: '.$exception->getMessage()];
        }
    }

    public function pull(array $config): array
    {
        $validationError = $this->validateConfig($config);
        if ($validationError !== null) {
            return ['ok' => false, 'message' => $validationError];
        }

        try {
            $settingsCount = $this->pullLandingSettings($config);
            $slidesCount = $this->pullHeroSlides($config);

            return [
                'ok' => true,
                'message' => "Pull selesai. Settings tersinkron: {$settingsCount}, slides tersinkron: {$slidesCount}.",
            ];
        } catch (\Throwable $exception) {
            return ['ok' => false, 'message' => 'Pull Directus gagal: '.$exception->getMessage()];
        }
    }

    private function pushLandingSettings(array $config): array
    {
        $collection = $config['landing_collection'];
        $settings = LandingSetting::query()->get(['key', 'value']);
        $existingMap = $this->fetchExistingMap($collection, 'key', $config);
        $updated = 0;
        $created = 0;

        foreach ($settings as $setting) {
            $payload = [
                'key' => (string) $setting->key,
                'value' => (string) ($setting->value ?? ''),
            ];
            if ($config['project'] !== '') {
                $payload['project'] = $config['project'];
            }

            $existingId = $existingMap[$payload['key']] ?? null;
            if ($existingId !== null) {
                $this->request('patch', "/items/{$collection}/{$existingId}", $config, $payload);
                $updated++;
            } else {
                $this->request('post', "/items/{$collection}", $config, $payload);
                $created++;
            }
        }

        return ['updated' => $updated, 'created' => $created];
    }

    private function pushHeroSlides(array $config): array
    {
        $collection = $config['slides_collection'];
        $slides = HeroSlide::query()->orderBy('sort_order')->get();
        $existingMap = $this->fetchExistingMap($collection, 'local_id', $config);
        $updated = 0;
        $created = 0;

        foreach ($slides as $slide) {
            $payload = [
                'local_id' => (int) $slide->id,
                'title' => (string) $slide->title,
                'caption' => (string) ($slide->caption ?? ''),
                'video_url' => (string) ($slide->video_url ?? ''),
                'cta_text' => (string) ($slide->cta_text ?? ''),
                'cta_url' => (string) ($slide->cta_url ?? ''),
                'sort_order' => (int) $slide->sort_order,
                'duration_seconds' => (int) ($slide->duration_seconds ?? 0),
                'overlay_opacity' => (float) ($slide->overlay_opacity ?? 0.78),
                'is_active' => (bool) $slide->is_active,
            ];
            if ($config['project'] !== '') {
                $payload['project'] = $config['project'];
            }

            $mapKey = (string) $payload['local_id'];
            $existingId = $existingMap[$mapKey] ?? null;
            if ($existingId !== null) {
                $this->request('patch', "/items/{$collection}/{$existingId}", $config, $payload);
                $updated++;
            } else {
                $this->request('post', "/items/{$collection}", $config, $payload);
                $created++;
            }
        }

        return ['updated' => $updated, 'created' => $created];
    }

    private function pullLandingSettings(array $config): int
    {
        $collection = $config['landing_collection'];
        $params = ['limit' => -1, 'fields' => 'id,key,value,project'];
        if ($config['project'] !== '') {
            $params['filter[project][_eq]'] = $config['project'];
        }
        $response = $this->request('get', "/items/{$collection}", $config, $params);
        $rows = $this->dataRows($response);
        $count = 0;

        foreach ($rows as $row) {
            $key = (string) ($row['key'] ?? '');
            if ($key === '') {
                continue;
            }
            LandingSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => (string) ($row['value'] ?? '')]
            );
            $count++;
        }

        return $count;
    }

    private function pullHeroSlides(array $config): int
    {
        $collection = $config['slides_collection'];
        $params = ['limit' => -1, 'fields' => 'id,local_id,title,caption,video_url,cta_text,cta_url,sort_order,duration_seconds,overlay_opacity,is_active,project'];
        if ($config['project'] !== '') {
            $params['filter[project][_eq]'] = $config['project'];
        }
        $response = $this->request('get', "/items/{$collection}", $config, $params);
        $rows = collect($this->dataRows($response))
            ->filter(fn (array $row): bool => trim((string) ($row['title'] ?? '')) !== '')
            ->sortBy(fn (array $row): int => (int) ($row['sort_order'] ?? 0))
            ->values()
            ->all();

        DB::transaction(function () use ($rows): void {
            HeroSlide::query()->delete();
            foreach ($rows as $index => $row) {
                HeroSlide::query()->create([
                    'title' => (string) ($row['title'] ?? 'Untitled Slide'),
                    'caption' => (string) ($row['caption'] ?? ''),
                    'video_url' => (string) ($row['video_url'] ?? ''),
                    'cta_text' => (string) ($row['cta_text'] ?? ''),
                    'cta_url' => (string) ($row['cta_url'] ?? ''),
                    'sort_order' => (int) ($row['sort_order'] ?? ($index + 1)),
                    'duration_seconds' => max(0, (int) ($row['duration_seconds'] ?? 0)),
                    'overlay_opacity' => (float) ($row['overlay_opacity'] ?? 0.78),
                    'is_active' => (bool) ($row['is_active'] ?? true),
                ]);
            }
        });

        return count($rows);
    }

    private function fetchExistingMap(string $collection, string $mapField, array $config): array
    {
        $params = ['limit' => -1, 'fields' => "id,{$mapField},project"];
        if ($config['project'] !== '') {
            $params['filter[project][_eq]'] = $config['project'];
        }

        $response = $this->request('get', "/items/{$collection}", $config, $params);
        $rows = $this->dataRows($response);
        $map = [];
        foreach ($rows as $row) {
            $mapKey = trim((string) ($row[$mapField] ?? ''));
            $id = $row['id'] ?? null;
            if ($mapKey === '' || $id === null) {
                continue;
            }
            $map[$mapKey] = (string) $id;
        }

        return $map;
    }

    private function dataRows(Response $response): array
    {
        $json = $response->json();
        $data = $json['data'] ?? [];

        return is_array($data) ? $data : [];
    }

    private function loadBlueprint(): array
    {
        $path = resource_path('blueprints/directus/directus-schema-blueprint.json');
        if (! is_file($path)) {
            throw new \RuntimeException('File blueprint Directus tidak ditemukan.');
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (! is_array($decoded)) {
            throw new \RuntimeException('Blueprint Directus tidak valid.');
        }

        return $decoded;
    }

    private function collectionMap(array $config): array
    {
        return [
            'landing_content' => (string) ($config['landing_collection'] ?? 'landing_content'),
            'hero_slides' => (string) ($config['slides_collection'] ?? 'hero_slides'),
        ];
    }

    private function fieldsByCollection(array $blueprint, string $collectionName): array
    {
        $fields = $blueprint['fields'] ?? [];
        if (! is_array($fields)) {
            return [];
        }

        return collect($fields)
            ->filter(fn ($item): bool => is_array($item) && (string) ($item['collection'] ?? '') === $collectionName)
            ->values()
            ->all();
    }

    private function ensureCollection(string $collectionName, array $collectionBlueprint, array $config): bool
    {
        if ($this->collectionExists($collectionName, $config)) {
            return false;
        }

        $payload = [
            'collection' => $collectionName,
            'meta' => $collectionBlueprint['meta'] ?? [],
            'schema' => $collectionBlueprint['schema'] ?? [],
        ];
        if (is_array($payload['meta'])) {
            $payload['meta']['collection'] = $collectionName;
        }
        if (is_array($payload['schema'])) {
            $payload['schema']['name'] = $collectionName;
        }

        $this->request('post', '/collections', $config, $payload);

        return true;
    }

    private function ensureFields(string $collectionName, array $fieldBlueprints, array $config): int
    {
        $existingFields = $this->existingFieldMap($collectionName, $config);
        $created = 0;

        foreach ($fieldBlueprints as $fieldBlueprint) {
            if (! is_array($fieldBlueprint)) {
                continue;
            }
            $fieldName = (string) ($fieldBlueprint['field'] ?? '');
            if ($fieldName === '' || isset($existingFields[$fieldName])) {
                continue;
            }
            $payload = $fieldBlueprint;
            $payload['collection'] = $collectionName;
            $this->request('post', "/fields/{$collectionName}", $config, $payload);
            $created++;
            $existingFields[$fieldName] = true;
        }

        return $created;
    }

    private function existingFieldMap(string $collectionName, array $config): array
    {
        $response = $this->requestNoFail('get', "/fields/{$collectionName}", $config);
        if (! $response->successful()) {
            return [];
        }

        $rows = $this->dataRows($response);
        $map = [];
        foreach ($rows as $row) {
            $field = trim((string) ($row['field'] ?? ''));
            if ($field !== '') {
                $map[$field] = true;
            }
        }

        return $map;
    }

    private function collectionExists(string $collectionName, array $config): bool
    {
        $response = $this->requestNoFail('get', "/collections/{$collectionName}", $config);

        return $response->successful();
    }

    private function provisionRolesAndPermissions(array $collectionMap, array $config): array
    {
        $rolesConfig = [
            'PHN Admin' => [
                'name' => 'PHN Admin',
                'icon' => 'shield',
                'description' => 'Full access untuk operasional PHN.',
                'app_access' => true,
                'admin_access' => true,
            ],
            'PHN Editor' => [
                'name' => 'PHN Editor',
                'icon' => 'edit',
                'description' => 'Akses editor untuk update konten.',
                'app_access' => true,
                'admin_access' => false,
            ],
        ];
        $existingRoleMap = $this->existingRoleMap($config);
        $createdRoles = 0;
        $permissionsCreated = 0;
        $permissionsUpdated = 0;
        $collectionNames = array_values($collectionMap);

        foreach ($rolesConfig as $roleName => $rolePayload) {
            $roleId = $existingRoleMap[$roleName] ?? null;
            if ($roleId === null) {
                $response = $this->request('post', '/roles', $config, $rolePayload);
                $roleId = (string) ($response->json('data.id') ?? '');
                if ($roleId === '') {
                    throw new \RuntimeException("Gagal membaca role id untuk {$roleName}.");
                }
                $createdRoles++;
                $existingRoleMap[$roleName] = $roleId;
            } else {
                $this->request('patch', "/roles/{$roleId}", $config, $rolePayload);
            }

            $actions = $roleName === 'PHN Admin'
                ? ['create', 'read', 'update', 'delete']
                : ['create', 'read', 'update'];

            foreach ($collectionNames as $collectionName) {
                foreach ($actions as $action) {
                    $permissionPayload = [
                        'role' => $roleId,
                        'collection' => $collectionName,
                        'action' => $action,
                        'permissions' => (object) [],
                        'validation' => (object) [],
                        'presets' => (object) [],
                        'fields' => ['*'],
                    ];
                    $existingPermissionId = $this->existingPermissionId($roleId, $collectionName, $action, $config);
                    if ($existingPermissionId === null) {
                        $this->request('post', '/permissions', $config, $permissionPayload);
                        $permissionsCreated++;
                    } else {
                        $this->request('patch', "/permissions/{$existingPermissionId}", $config, $permissionPayload);
                        $permissionsUpdated++;
                    }
                }
            }
        }

        return [
            'roles_created' => $createdRoles,
            'permissions_created' => $permissionsCreated,
            'permissions_updated' => $permissionsUpdated,
        ];
    }

    private function existingRoleMap(array $config): array
    {
        $response = $this->requestNoFail('get', '/roles?limit=-1&fields=id,name', $config);
        if (! $response->successful()) {
            return [];
        }

        $rows = $this->dataRows($response);
        $map = [];
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $id = trim((string) ($row['id'] ?? ''));
            if ($name !== '' && $id !== '') {
                $map[$name] = $id;
            }
        }

        return $map;
    }

    private function existingPermissionId(string $roleId, string $collectionName, string $action, array $config): ?string
    {
        $query = http_build_query([
            'limit' => 1,
            'fields' => 'id,role,collection,action',
            'filter' => [
                'role' => ['_eq' => $roleId],
                'collection' => ['_eq' => $collectionName],
                'action' => ['_eq' => $action],
            ],
        ]);
        $response = $this->requestNoFail('get', '/permissions?'.$query, $config);
        if (! $response->successful()) {
            return null;
        }

        $rows = $this->dataRows($response);
        $first = $rows[0] ?? null;
        if (! is_array($first)) {
            return null;
        }

        $id = trim((string) ($first['id'] ?? ''));

        return $id !== '' ? $id : null;
    }

    private function request(string $method, string $path, array $config, array $payload = []): Response
    {
        $response = $this->requestNoFail($method, $path, $config, $payload);

        if (! $response->successful()) {
            throw new \RuntimeException("Directus {$method} {$path} gagal (HTTP {$response->status()}): ".$response->body());
        }

        return $response;
    }

    private function requestNoFail(string $method, string $path, array $config, array $payload = []): Response
    {
        $url = rtrim($config['base_url'], '/').$path;
        $client = Http::acceptJson()
            ->withToken($config['token'])
            ->timeout(30);

        return match ($method) {
            'get' => $client->get($url, $payload),
            'post' => $client->post($url, $payload),
            'patch' => $client->patch($url, $payload),
            default => throw new \RuntimeException("HTTP method {$method} tidak didukung"),
        };
    }

    private function validateConfig(array $config): ?string
    {
        if (($config['engine'] ?? 'native') !== 'directus') {
            return 'Engine CMS bukan Directus.';
        }
        if (trim((string) ($config['base_url'] ?? '')) === '') {
            return 'Directus Base URL belum diisi.';
        }
        if (trim((string) ($config['token'] ?? '')) === '') {
            return 'Directus Static Access Token belum diisi.';
        }
        if (trim((string) ($config['landing_collection'] ?? '')) === '') {
            return 'Primary Collection Directus belum diisi.';
        }
        if (trim((string) ($config['slides_collection'] ?? '')) === '') {
            return 'Slides Collection Directus belum diisi.';
        }

        return null;
    }
}
