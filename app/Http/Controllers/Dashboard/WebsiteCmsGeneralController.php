<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LandingSetting;
use App\Support\DirectusCmsSyncService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WebsiteCmsGeneralController extends Controller
{
    public function __construct(private readonly DirectusCmsSyncService $directusCmsSyncService) {}

    public function index(): View
    {
        $settings = LandingSetting::query()->pluck('value', 'key');

        return view('content.dashboard.website-cms-general', [
            'form' => [
                'site_title' => (string) ($settings['cms_site_title'] ?? 'PRODUCTION HOUSE NUSANTARA'),
                'tagline' => (string) ($settings['cms_tagline'] ?? 'Professional Film & Content Production'),
                'site_description' => (string) ($settings['cms_site_description'] ?? ''),
                'keywords' => (string) ($settings['cms_keywords'] ?? ''),
                'contact_email' => (string) ($settings['cms_contact_email'] ?? ''),
                'contact_phone' => (string) ($settings['cms_contact_phone'] ?? ''),
                'office_address' => (string) ($settings['cms_office_address'] ?? ''),
                'map_query' => (string) ($settings['cms_map_query'] ?? ($settings['cms_office_address'] ?? '')),
                'operational_hours' => (string) ($settings['cms_operational_hours'] ?? ''),
                'social_instagram' => (string) ($settings['cms_social_instagram'] ?? ''),
                'social_youtube' => (string) ($settings['cms_social_youtube'] ?? ''),
                'social_vimeo' => (string) ($settings['cms_social_vimeo'] ?? ''),
                'social_tiktok' => (string) ($settings['cms_social_tiktok'] ?? ''),
                'social_linkedin' => (string) ($settings['cms_social_linkedin'] ?? ''),
                'social_facebook' => (string) ($settings['cms_social_facebook'] ?? ''),
                'testimonial_title' => (string) ($settings['testimonial_title'] ?? 'Apa Kata Klien Kami'),
                'testimonial_items' => (string) ($settings['testimonial_items'] ?? json_encode([
                    ['name' => 'Nadia Prameswari', 'role' => 'Brand Manager · F&B Chain', 'quote' => 'Eksekusi cepat, hasil video kampanye sangat cinematic dan conversion naik signifikan.'],
                    ['name' => 'Raka Aditya', 'role' => 'Marketing Lead · Property', 'quote' => 'Alur produksi rapi dari pre-production sampai final delivery. Komunikasi tim sangat responsif.'],
                    ['name' => 'Ayu Lestari', 'role' => 'Founder · Beauty Brand', 'quote' => 'Visual storytelling kuat, tone brand kami tetap konsisten di semua materi konten.'],
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
                'analytics_code' => (string) ($settings['cms_analytics_code'] ?? ''),
                'seo_default_og' => (string) ($settings['cms_seo_default_og'] ?? ''),
                'seo_schema_markup' => (string) ($settings['cms_seo_schema_markup'] ?? ''),
                'language_mode' => (string) ($settings['cms_language_mode'] ?? 'id'),
                'maintenance_mode' => ($settings['cms_maintenance_mode'] ?? '0') === '1',
                'cms_engine' => (string) ($settings['cms_engine'] ?? 'native'),
                'directus_url' => (string) ($settings['cms_directus_url'] ?? ''),
                'directus_project' => (string) ($settings['cms_directus_project'] ?? ''),
                'directus_collection' => (string) ($settings['cms_directus_collection'] ?? ''),
                'directus_slides_collection' => (string) ($settings['cms_directus_slides_collection'] ?? 'hero_slides'),
                'directus_token' => (string) ($settings['cms_directus_token'] ?? ''),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_title' => ['required', 'string', 'max:150'],
            'tagline' => ['required', 'string', 'max:180'],
            'site_description' => ['nullable', 'string', 'max:500'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'contact_email' => ['nullable', 'email', 'max:180'],
            'contact_phone' => ['nullable', 'string', 'max:80'],
            'office_address' => ['nullable', 'string', 'max:500'],
            'map_query' => ['nullable', 'string', 'max:500'],
            'operational_hours' => ['nullable', 'string', 'max:180'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
            'social_youtube' => ['nullable', 'url', 'max:255'],
            'social_vimeo' => ['nullable', 'url', 'max:255'],
            'social_tiktok' => ['nullable', 'url', 'max:255'],
            'social_linkedin' => ['nullable', 'url', 'max:255'],
            'social_facebook' => ['nullable', 'url', 'max:255'],
            'testimonial_title' => ['nullable', 'string', 'max:180'],
            'testimonial_items' => ['nullable', 'string'],
            'analytics_code' => ['nullable', 'string'],
            'seo_default_og' => ['nullable', 'string'],
            'seo_schema_markup' => ['nullable', 'string'],
            'language_mode' => ['required', 'in:id,en,id-en'],
            'maintenance_mode' => ['nullable', 'boolean'],
            'cms_engine' => ['required', 'in:native,directus'],
            'directus_url' => ['nullable', 'url', 'max:255', 'required_if:cms_engine,directus'],
            'directus_project' => ['nullable', 'string', 'max:120'],
            'directus_collection' => ['nullable', 'string', 'max:120', 'required_if:cms_engine,directus'],
            'directus_slides_collection' => ['nullable', 'string', 'max:120', 'required_if:cms_engine,directus'],
            'directus_token' => ['nullable', 'string', 'max:500', 'required_if:cms_engine,directus'],
        ]);

        $payload = [
            'cms_site_title' => $data['site_title'],
            'cms_tagline' => $data['tagline'],
            'cms_site_description' => $data['site_description'] ?? '',
            'cms_keywords' => $data['keywords'] ?? '',
            'cms_contact_email' => $data['contact_email'] ?? '',
            'cms_contact_phone' => $data['contact_phone'] ?? '',
            'cms_office_address' => $data['office_address'] ?? '',
            'cms_map_query' => $data['map_query'] ?? '',
            'cms_operational_hours' => $data['operational_hours'] ?? '',
            'cms_social_instagram' => $data['social_instagram'] ?? '',
            'cms_social_youtube' => $data['social_youtube'] ?? '',
            'cms_social_vimeo' => $data['social_vimeo'] ?? '',
            'cms_social_tiktok' => $data['social_tiktok'] ?? '',
            'cms_social_linkedin' => $data['social_linkedin'] ?? '',
            'cms_social_facebook' => $data['social_facebook'] ?? '',
            'testimonial_title' => $data['testimonial_title'] ?? '',
            'testimonial_items' => $data['testimonial_items'] ?? '',
            'cms_analytics_code' => $data['analytics_code'] ?? '',
            'cms_seo_default_og' => $data['seo_default_og'] ?? '',
            'cms_seo_schema_markup' => $data['seo_schema_markup'] ?? '',
            'cms_language_mode' => $data['language_mode'],
            'cms_maintenance_mode' => $request->boolean('maintenance_mode') ? '1' : '0',
            'cms_engine' => $data['cms_engine'],
            'cms_directus_url' => $data['directus_url'] ?? '',
            'cms_directus_project' => $data['directus_project'] ?? '',
            'cms_directus_collection' => $data['directus_collection'] ?? '',
            'cms_directus_slides_collection' => $data['directus_slides_collection'] ?? 'hero_slides',
            'cms_directus_token' => $data['directus_token'] ?? '',
        ];

        foreach ($payload as $key => $value) {
            LandingSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()
            ->route('dashboard-website-cms-general')
            ->with('success', 'Website CMS General berhasil diperbarui.');
    }

    public function directusPush(): RedirectResponse
    {
        $result = $this->directusCmsSyncService->push($this->directusConfig());

        return redirect()
            ->route('dashboard-website-cms-general')
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function directusPull(): RedirectResponse
    {
        $result = $this->directusCmsSyncService->pull($this->directusConfig());

        return redirect()
            ->route('dashboard-website-cms-general')
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function directusProvision(): RedirectResponse
    {
        $result = $this->directusCmsSyncService->provisionCollections($this->directusConfig());

        return redirect()
            ->route('dashboard-website-cms-general')
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function downloadDirectusBlueprint(): BinaryFileResponse
    {
        return response()->download(
            resource_path('blueprints/directus/directus-schema-blueprint.json'),
            'directus-schema-blueprint.json'
        );
    }

    public function downloadDirectusFieldMapping(): BinaryFileResponse
    {
        return response()->download(
            resource_path('blueprints/directus/directus-field-mapping.json'),
            'directus-field-mapping.json'
        );
    }

    private function directusConfig(): array
    {
        $settings = LandingSetting::query()->pluck('value', 'key')->all();

        return [
            'engine' => (string) ($settings['cms_engine'] ?? 'native'),
            'base_url' => (string) ($settings['cms_directus_url'] ?? ''),
            'project' => (string) ($settings['cms_directus_project'] ?? ''),
            'landing_collection' => (string) ($settings['cms_directus_collection'] ?? ''),
            'slides_collection' => (string) ($settings['cms_directus_slides_collection'] ?? 'hero_slides'),
            'token' => (string) ($settings['cms_directus_token'] ?? ''),
        ];
    }
}
