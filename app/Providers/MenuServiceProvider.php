<?php

namespace App\Providers;

use App\Models\LandingSetting;
use Illuminate\Database\QueryException;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
        $verticalMenuData = json_decode($verticalMenuJson);
        if (! is_object($verticalMenuData)) {
            $verticalMenuData = (object) ['menu' => []];
        }
        $verticalMenuData = $this->normalizeVerticalMenuData($verticalMenuData);
        $dashboardBrandLogos = [
            'primary' => '',
            'sticky' => '',
            'header_position' => 'left',
        ];
        $dashboardFaviconUrl = '';
        $dashboardAppleTouchIconUrl = '';

        try {
            $settings = LandingSetting::query()->pluck('value', 'key')->all();
            $toAsset = static fn (string $path): string => $path !== '' ? asset('storage/'.ltrim($path, '/')) : '';

            $logos = [
                'primary' => $toAsset((string) ($settings['cms_logo_primary'] ?? '')),
                'secondary_light' => $toAsset((string) ($settings['cms_logo_secondary_light'] ?? '')),
                'secondary_dark' => $toAsset((string) ($settings['cms_logo_secondary_dark'] ?? '')),
                'monochrome' => $toAsset((string) ($settings['cms_logo_monochrome'] ?? '')),
                'sticky_custom' => $toAsset((string) ($settings['cms_logo_sticky'] ?? '')),
                'footer' => $toAsset((string) ($settings['cms_logo_footer'] ?? '')),
            ];

            $firstAvailable = static function (array $candidates) use ($logos): string {
                foreach ($candidates as $key) {
                    $value = $logos[$key] ?? '';
                    if ($value !== '') {
                        return $value;
                    }
                }

                return '';
            };

            $stickyVariant = (string) ($settings['cms_logo_sticky_variant'] ?? 'primary');
            $stickyModeMap = [
                'primary' => ['primary', 'secondary_light', 'secondary_dark', 'sticky_custom', 'monochrome', 'footer'],
                'secondary-light' => ['secondary_light', 'primary', 'secondary_dark', 'sticky_custom', 'monochrome', 'footer'],
                'secondary-dark' => ['secondary_dark', 'primary', 'secondary_light', 'sticky_custom', 'monochrome', 'footer'],
                'sticky' => ['sticky_custom', 'primary', 'secondary_dark', 'secondary_light', 'monochrome', 'footer'],
            ];

            $dashboardBrandLogos['primary'] = $firstAvailable(['primary', 'secondary_light', 'secondary_dark', 'sticky_custom', 'footer', 'monochrome']);
            $dashboardBrandLogos['sticky'] = $firstAvailable($stickyModeMap[$stickyVariant] ?? $stickyModeMap['primary']);
            $dashboardBrandLogos['header_position'] = (string) ($settings['cms_logo_header_position'] ?? 'left');
            $dashboardFaviconUrl = $toAsset((string) ($settings['cms_logo_favicon_32'] ?? ''));
            $dashboardAppleTouchIconUrl = $toAsset((string) ($settings['cms_logo_apple_touch'] ?? ''));
        } catch (QueryException) {
            $dashboardBrandLogos = [
                'primary' => '',
                'sticky' => '',
                'header_position' => 'left',
            ];
            $dashboardFaviconUrl = '';
            $dashboardAppleTouchIconUrl = '';
        }

        $this->app->make('view')->share('menuData', [$verticalMenuData]);
        $this->app->make('view')->share('dashboardBrandLogos', $dashboardBrandLogos);
        $this->app->make('view')->share('dashboardBrandLogo', (string) ($dashboardBrandLogos['primary'] ?? ''));
        $this->app->make('view')->share('dashboardFaviconUrl', $dashboardFaviconUrl);
        $this->app->make('view')->share('dashboardAppleTouchIconUrl', $dashboardAppleTouchIconUrl);
    }

    private function normalizeVerticalMenuData(object $menuData): object
    {
        if (! isset($menuData->menu) || ! is_array($menuData->menu)) {
            $menuData->menu = [];

            return $menuData;
        }

        $requiredSubmenus = [
            ['url' => '/dashboard/website-cms/general', 'name' => 'General', 'slug' => 'dashboard-website-cms-general'],
            ['url' => '/dashboard/website-cms/logo', 'name' => 'Logo', 'slug' => 'dashboard-website-cms-logo'],
            ['url' => '/dashboard/website-cms/header', 'name' => 'Header', 'slug' => 'dashboard-website-cms-header'],
            ['url' => '/dashboard/website-cms/slider-video', 'name' => 'Slider Video', 'slug' => 'dashboard-website-cms-slider-video'],
            ['url' => '/dashboard/website-cms/pages', 'name' => 'Pages', 'slug' => 'dashboard-website-cms-pages'],
            ['url' => '/dashboard/website-cms/portfolio', 'name' => 'Portfolio', 'slug' => 'dashboard-website-cms-portfolio'],
            ['url' => '/dashboard/website-cms/blog-news', 'name' => 'Blog / News', 'slug' => 'dashboard-website-cms-blog-news'],
        ];

        foreach ($menuData->menu as $index => $item) {
            if (! is_object($item)) {
                continue;
            }
            if (($item->name ?? '') !== 'WEBSITE CMS') {
                continue;
            }
            if (! isset($item->submenu) || ! is_array($item->submenu)) {
                $item->submenu = [];
            }

            $existing = [];
            foreach ($item->submenu as $submenuItem) {
                if (is_object($submenuItem) && isset($submenuItem->slug) && is_string($submenuItem->slug)) {
                    $existing[$submenuItem->slug] = true;
                }
            }

            foreach ($requiredSubmenus as $requiredSubmenu) {
                $requiredSlug = $requiredSubmenu['slug'];
                if (isset($existing[$requiredSlug])) {
                    continue;
                }
                $item->submenu[] = (object) $requiredSubmenu;
            }

            $menuData->menu[$index] = $item;
            break;
        }

        $storeMenuIndex = null;
        foreach ($menuData->menu as $index => $item) {
            if (! is_object($item)) {
                continue;
            }
            if (($item->name ?? '') !== 'STORE') {
                continue;
            }
            $storeMenuIndex = $index;
            break;
        }
        if ($storeMenuIndex === null) {
            $storeMenu = (object) [
                'name' => 'STORE',
                'icon' => 'menu-icon icon-base bx bx-store-alt',
                'slug' => 'dashboard-store',
                'submenu' => [
                    (object) [
                        'url' => '/dashboard/store/product',
                        'name' => 'Product',
                        'slug' => 'dashboard-store-product',
                    ],
                    (object) [
                        'url' => '/dashboard/store/orders',
                        'name' => 'Orders',
                        'slug' => 'dashboard-store-order',
                    ],
                ],
            ];
            $operationalHeaderIndex = null;
            foreach ($menuData->menu as $index => $item) {
                if (is_object($item) && (($item->menuHeader ?? '') === 'Operational')) {
                    $operationalHeaderIndex = $index;
                    break;
                }
            }
            if ($operationalHeaderIndex !== null) {
                array_splice($menuData->menu, $operationalHeaderIndex + 1, 0, [$storeMenu]);
            } else {
                $menuData->menu[] = $storeMenu;
            }
        } else {
            $storeMenu = $menuData->menu[$storeMenuIndex];
            if (! isset($storeMenu->submenu) || ! is_array($storeMenu->submenu)) {
                $storeMenu->submenu = [];
            }
            $hasProduct = false;
            foreach ($storeMenu->submenu as $submenuItem) {
                if (! is_object($submenuItem)) {
                    continue;
                }
                if (($submenuItem->slug ?? '') === 'dashboard-store-product') {
                    $hasProduct = true;
                    break;
                }
            }
            if (! $hasProduct) {
                $storeMenu->submenu[] = (object) [
                    'url' => '/dashboard/store/product',
                    'name' => 'Product',
                    'slug' => 'dashboard-store-product',
                ];
            }
            $hasOrder = false;
            foreach ($storeMenu->submenu as $submenuItem) {
                if (! is_object($submenuItem)) {
                    continue;
                }
                if (($submenuItem->slug ?? '') === 'dashboard-store-order') {
                    $hasOrder = true;
                    break;
                }
            }
            if (! $hasOrder) {
                $storeMenu->submenu[] = (object) [
                    'url' => '/dashboard/store/orders',
                    'name' => 'Orders',
                    'slug' => 'dashboard-store-order',
                ];
            }
            $menuData->menu[$storeMenuIndex] = $storeMenu;
        }

        $userMenuIndex = null;
        foreach ($menuData->menu as $index => $item) {
            if (! is_object($item)) {
                continue;
            }
            if (($item->name ?? '') !== 'USER') {
                continue;
            }
            $userMenuIndex = $index;
            break;
        }
        if ($userMenuIndex === null) {
            $userMenu = (object) [
                'name' => 'USER',
                'icon' => 'menu-icon icon-base bx bx-user',
                'slug' => 'dashboard-user',
                'submenu' => [
                    (object) [
                        'url' => '/dashboard/user',
                        'name' => 'Management',
                        'slug' => 'dashboard-user-management',
                    ],
                    (object) [
                        'url' => '/dashboard/user/create',
                        'name' => 'Create User',
                        'slug' => 'dashboard-user-create',
                    ],
                    (object) [
                        'url' => '/dashboard/user/role',
                        'name' => 'Role',
                        'slug' => 'dashboard-user-role',
                    ],
                ],
            ];
            $insertIndex = $storeMenuIndex !== null ? $storeMenuIndex + 1 : count($menuData->menu);
            array_splice($menuData->menu, $insertIndex, 0, [$userMenu]);
        } else {
            $userMenu = $menuData->menu[$userMenuIndex];
            if (! isset($userMenu->submenu) || ! is_array($userMenu->submenu)) {
                $userMenu->submenu = [];
            }
            $hasManagement = false;
            foreach ($userMenu->submenu as $submenuItem) {
                if (! is_object($submenuItem)) {
                    continue;
                }
                if (($submenuItem->slug ?? '') === 'dashboard-user-management') {
                    $hasManagement = true;
                    break;
                }
            }
            if (! $hasManagement) {
                $userMenu->submenu[] = (object) [
                    'url' => '/dashboard/user',
                    'name' => 'Management',
                    'slug' => 'dashboard-user-management',
                ];
            }
            $hasCreate = false;
            foreach ($userMenu->submenu as $submenuItem) {
                if (! is_object($submenuItem)) {
                    continue;
                }
                if (($submenuItem->slug ?? '') === 'dashboard-user-create') {
                    $hasCreate = true;
                    break;
                }
            }
            if (! $hasCreate) {
                $userMenu->submenu[] = (object) [
                    'url' => '/dashboard/user/create',
                    'name' => 'Create User',
                    'slug' => 'dashboard-user-create',
                ];
            }
            $hasRole = false;
            foreach ($userMenu->submenu as $submenuItem) {
                if (! is_object($submenuItem)) {
                    continue;
                }
                if (($submenuItem->slug ?? '') === 'dashboard-user-role') {
                    $hasRole = true;
                    break;
                }
            }
            if (! $hasRole) {
                $userMenu->submenu[] = (object) [
                    'url' => '/dashboard/user/role',
                    'name' => 'Role',
                    'slug' => 'dashboard-user-role',
                ];
            }
            $menuData->menu[$userMenuIndex] = $userMenu;
        }

        return $menuData;
    }
}
