<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LandingSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebsiteCmsHeaderController extends Controller
{
    public function index(): View
    {
        $settings = LandingSetting::query()->pluck('value', 'key');

        return view('content.dashboard.website-cms-header', [
            'form' => [
                'navigation_items_json' => $this->prettyJson($settings['cms_header_navigation_items'] ?? ''),
                'mega_menu_keys' => (string) ($settings['cms_header_mega_menu_keys'] ?? ''),
                'header_layout' => (string) ($settings['cms_header_layout'] ?? 'transparent'),
                'sticky_on_scroll' => ($settings['cms_header_sticky_on_scroll'] ?? '1') === '1',
                'shrink_on_scroll' => ($settings['cms_header_shrink_on_scroll'] ?? '1') === '1',
                'topbar_enabled' => ($settings['cms_header_topbar_enabled'] ?? '0') === '1',
                'topbar_phone' => (string) ($settings['cms_header_topbar_phone'] ?? ''),
                'topbar_email' => (string) ($settings['cms_header_topbar_email'] ?? ''),
                'topbar_social_enabled' => ($settings['cms_header_topbar_social_enabled'] ?? '0') === '1',
                'topbar_language_switcher' => ($settings['cms_header_topbar_language_switcher'] ?? '0') === '1',
                'topbar_cta_text' => (string) ($settings['cms_header_topbar_cta_text'] ?? ''),
                'topbar_cta_link' => (string) ($settings['cms_header_topbar_cta_link'] ?? ''),
                'mobile_hamburger_style' => (string) ($settings['cms_header_mobile_hamburger_style'] ?? 'classic'),
                'mobile_menu_mode' => (string) ($settings['cms_header_mobile_menu_mode'] ?? 'drawer'),
                'mobile_menu_items_json' => $this->prettyJson($settings['cms_header_mobile_menu_items'] ?? ''),
                'header_cta_text' => (string) ($settings['cms_header_cta_text'] ?? 'Start Project'),
                'header_cta_link' => (string) ($settings['cms_header_cta_link'] ?? '#contact'),
                'header_cta_color' => (string) ($settings['cms_header_cta_color'] ?? 'gold'),
                'header_cta_show_home' => ($settings['cms_header_cta_show_home'] ?? '1') === '1',
                'header_cta_show_inner' => ($settings['cms_header_cta_show_inner'] ?? '1') === '1',
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'navigation_items_json' => ['nullable', 'string'],
            'mega_menu_keys' => ['nullable', 'string', 'max:500'],
            'header_layout' => ['required', 'in:transparent,solid'],
            'sticky_on_scroll' => ['nullable', 'boolean'],
            'shrink_on_scroll' => ['nullable', 'boolean'],
            'topbar_enabled' => ['nullable', 'boolean'],
            'topbar_phone' => ['nullable', 'string', 'max:80'],
            'topbar_email' => ['nullable', 'email', 'max:180'],
            'topbar_social_enabled' => ['nullable', 'boolean'],
            'topbar_language_switcher' => ['nullable', 'boolean'],
            'topbar_cta_text' => ['nullable', 'string', 'max:80'],
            'topbar_cta_link' => ['nullable', 'string', 'max:255'],
            'mobile_hamburger_style' => ['required', 'in:classic,rounded,minimal'],
            'mobile_menu_mode' => ['required', 'in:drawer,dropdown'],
            'mobile_menu_items_json' => ['nullable', 'string'],
            'header_cta_text' => ['nullable', 'string', 'max:80'],
            'header_cta_link' => ['nullable', 'string', 'max:255'],
            'header_cta_color' => ['required', 'in:gold,orange,white'],
            'header_cta_show_home' => ['nullable', 'boolean'],
            'header_cta_show_inner' => ['nullable', 'boolean'],
        ]);

        $navigationItems = $this->sanitizeMenuJson($data['navigation_items_json'] ?? '[]');
        $mobileItems = $this->sanitizeMenuJson($data['mobile_menu_items_json'] ?? '[]');

        $payload = [
            'cms_header_navigation_items' => $navigationItems,
            'cms_header_mega_menu_keys' => $data['mega_menu_keys'] ?? '',
            'cms_header_layout' => $data['header_layout'],
            'cms_header_sticky_on_scroll' => $request->boolean('sticky_on_scroll') ? '1' : '0',
            'cms_header_shrink_on_scroll' => $request->boolean('shrink_on_scroll') ? '1' : '0',
            'cms_header_topbar_enabled' => $request->boolean('topbar_enabled') ? '1' : '0',
            'cms_header_topbar_phone' => $data['topbar_phone'] ?? '',
            'cms_header_topbar_email' => $data['topbar_email'] ?? '',
            'cms_header_topbar_social_enabled' => $request->boolean('topbar_social_enabled') ? '1' : '0',
            'cms_header_topbar_language_switcher' => $request->boolean('topbar_language_switcher') ? '1' : '0',
            'cms_header_topbar_cta_text' => $data['topbar_cta_text'] ?? '',
            'cms_header_topbar_cta_link' => $data['topbar_cta_link'] ?? '',
            'cms_header_mobile_hamburger_style' => $data['mobile_hamburger_style'],
            'cms_header_mobile_menu_mode' => $data['mobile_menu_mode'],
            'cms_header_mobile_menu_items' => $mobileItems,
            'cms_header_cta_text' => $data['header_cta_text'] ?? '',
            'cms_header_cta_link' => $data['header_cta_link'] ?? '',
            'cms_header_cta_color' => $data['header_cta_color'],
            'cms_header_cta_show_home' => $request->boolean('header_cta_show_home') ? '1' : '0',
            'cms_header_cta_show_inner' => $request->boolean('header_cta_show_inner') ? '1' : '0',
        ];

        foreach ($payload as $key => $value) {
            LandingSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()
            ->route('dashboard-website-cms-header')
            ->with('success', 'Website CMS Header berhasil diperbarui.');
    }

    private function sanitizeMenuJson(string $raw): string
    {
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return '[]';
        }

        $normalized = $this->normalizeItems($decoded, 1);

        return json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function normalizeItems(array $items, int $depth): array
    {
        if ($depth > 3) {
            return [];
        }

        return collect($items)
            ->filter(fn ($item): bool => is_array($item))
            ->map(function (array $item) use ($depth): array {
                $children = [];
                if (isset($item['children']) && is_array($item['children'])) {
                    $children = $this->normalizeItems($item['children'], $depth + 1);
                }

                $type = (string) ($item['type'] ?? 'internal');
                if (! in_array($type, ['internal', 'external', 'mega'], true)) {
                    $type = 'internal';
                }

                $target = (string) ($item['target'] ?? '_self');
                if (! in_array($target, ['_self', '_blank'], true)) {
                    $target = '_self';
                }

                return [
                    'title' => (string) ($item['title'] ?? 'Untitled'),
                    'url' => (string) ($item['url'] ?? '#'),
                    'type' => $type,
                    'target' => $target,
                    'children' => $children,
                ];
            })
            ->values()
            ->all();
    }

    private function prettyJson(string $raw): string
    {
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return '[]';
        }

        return (string) json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
