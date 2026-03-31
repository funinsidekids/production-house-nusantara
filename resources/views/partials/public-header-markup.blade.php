@php
    $isInnerPage = (bool) ($isInnerPage ?? false);
    $headerLogoPrimary = (string) ($headerConfig['logo_primary'] ?? '');
    $headerLogoSticky = (string) ($headerConfig['logo_sticky'] ?? $headerLogoPrimary);
    $headerMenuItems = $headerConfig['nav_items'] ?? [];
    $headerMobileItems = $headerConfig['mobile_menu_items'] ?? $headerMenuItems;
    $headerMegaKeys = collect($headerConfig['mega_menu_keys'] ?? [])->map(fn (string $key): string => \Illuminate\Support\Str::lower(trim($key)))->all();
    $headerLayout = (string) ($headerConfig['layout'] ?? ($isInnerPage ? 'solid' : 'transparent'));
    $headerBrandHref = (string) ($headerBrandHref ?? ($isInnerPage ? '/' : '#home'));
    $headerBrandText = (string) ($headerBrandText ?? ($cmsGeneral['site_title'] ?? 'Production House Nusantara'));
    $headerBrandAlt = (string) ($headerBrandAlt ?? $headerBrandText);
    $headerCtaColor = (string) ($headerConfig['cta_color'] ?? 'gold');
    $headerCtaClass = $headerCtaColor === 'orange' ? 'color-orange' : ($headerCtaColor === 'white' ? 'color-white' : 'color-gold');
    $showHeaderCta = ($isInnerPage ? ($headerConfig['cta_show_inner'] ?? true) : ($headerConfig['cta_show_home'] ?? true))
        && trim((string) ($headerConfig['cta_text'] ?? '')) !== '';
@endphp
@if ($headerConfig['topbar_enabled'] ?? false)
    <div id="publicTopBar" class="public-topbar">
        <div class="container public-topbar-inner">
            <div class="public-topbar-quick">
                @if (!empty($headerConfig['topbar_phone']))
                    <a href="tel:{{ preg_replace('/\s+/', '', (string) $headerConfig['topbar_phone']) }}">{{ $headerConfig['topbar_phone'] }}</a>
                @endif
                @if (!empty($headerConfig['topbar_email']))
                    <a href="mailto:{{ $headerConfig['topbar_email'] }}">{{ $headerConfig['topbar_email'] }}</a>
                @endif
            </div>
            <div class="public-topbar-actions">
                @if (($headerConfig['topbar_social_enabled'] ?? false) && !empty($headerConfig['topbar_social_links']))
                    <div class="public-topbar-social">
                        @foreach (($headerConfig['topbar_social_links'] ?? []) as $social)
                            <a href="{{ $social['url'] ?? '#' }}" target="_blank">{{ $social['name'] ?? 'Social' }}</a>
                        @endforeach
                    </div>
                @endif
                @if ($headerConfig['topbar_language_switcher'] ?? false)
                    <div class="public-topbar-lang">
                        <a href="#" data-lang="id">ID</a>
                        <span>/</span>
                        <a href="#" data-lang="en">EN</a>
                    </div>
                @endif
                @if (!empty($headerConfig['topbar_cta_text']) && !empty($headerConfig['topbar_cta_link']))
                    <a href="{{ $headerConfig['topbar_cta_link'] }}">{{ $headerConfig['topbar_cta_text'] }}</a>
                @endif
            </div>
        </div>
    </div>
@endif
<nav
    id="publicHeaderNav"
    class="public-header-nav layout-{{ $headerLayout }}"
    data-layout="{{ $headerLayout }}"
    data-sticky-on-scroll="{{ ($headerConfig['sticky_on_scroll'] ?? true) ? '1' : '0' }}"
    data-shrink-on-scroll="{{ ($headerConfig['shrink_on_scroll'] ?? true) ? '1' : '0' }}"
    data-mobile-mode="{{ $headerConfig['mobile_menu_mode'] ?? 'drawer' }}"
>
    <div class="container public-nav-inner">
        <a href="{{ $headerBrandHref }}" class="public-brand">
            @if ($headerLogoPrimary !== '')
                <img id="publicHeaderLogoImage" src="{{ $headerLogoPrimary }}" data-primary-logo="{{ $headerLogoPrimary }}" data-sticky-logo="{{ $headerLogoSticky !== '' ? $headerLogoSticky : $headerLogoPrimary }}" alt="{{ $headerBrandAlt }}">
            @endif
            <span>{{ $headerBrandText }}</span>
        </a>
        <ul class="public-nav-list">
            @foreach ($headerMenuItems as $item)
                @php
                    $children = is_array($item['children'] ?? null) ? $item['children'] : [];
                    $isMega = ($item['type'] ?? 'internal') === 'mega' || in_array(\Illuminate\Support\Str::lower((string) ($item['title'] ?? '')), $headerMegaKeys, true);
                    $target = (string) ($item['target'] ?? '_self');
                    $rel = $target === '_blank' ? 'noopener noreferrer' : null;
                @endphp
                <li class="public-nav-item">
                    <a class="public-nav-link" href="{{ $item['url'] ?? '#' }}" target="{{ $target }}" @if($rel) rel="{{ $rel }}" @endif>
                        <span>{{ $item['title'] ?? '' }}</span>
                        @if (!empty($children))
                            <span class="public-nav-caret">▾</span>
                        @endif
                    </a>
                    @if (!empty($children) && !$isMega)
                        <div class="public-dropdown">
                            <ul>
                                @foreach ($children as $child)
                                    @php
                                        $childTarget = (string) ($child['target'] ?? '_self');
                                        $childRel = $childTarget === '_blank' ? 'noopener noreferrer' : null;
                                    @endphp
                                    <li>
                                        <a href="{{ $child['url'] ?? '#' }}" target="{{ $childTarget }}" @if($childRel) rel="{{ $childRel }}" @endif>{{ $child['title'] ?? '' }}</a>
                                        @if (!empty($child['children']))
                                            <ul class="public-sub-list">
                                                @foreach ($child['children'] as $childL2)
                                                    @php
                                                        $childL2Target = (string) ($childL2['target'] ?? '_self');
                                                        $childL2Rel = $childL2Target === '_blank' ? 'noopener noreferrer' : null;
                                                    @endphp
                                                    <li>
                                                        <a href="{{ $childL2['url'] ?? '#' }}" target="{{ $childL2Target }}" @if($childL2Rel) rel="{{ $childL2Rel }}" @endif>{{ $childL2['title'] ?? '' }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (!empty($children) && $isMega)
                        <div class="public-mega">
                            <div class="public-mega-grid">
                                @foreach ($children as $child)
                                    @php
                                        $childTarget = (string) ($child['target'] ?? '_self');
                                        $childRel = $childTarget === '_blank' ? 'noopener noreferrer' : null;
                                    @endphp
                                    <div class="public-mega-col">
                                        <strong>{{ $child['title'] ?? '' }}</strong>
                                        <a href="{{ $child['url'] ?? '#' }}" target="{{ $childTarget }}" @if($childRel) rel="{{ $childRel }}" @endif>{{ $child['url'] ?? '#' }}</a>
                                        @if (!empty($child['children']))
                                            @foreach ($child['children'] as $childL2)
                                                @php
                                                    $childL2Target = (string) ($childL2['target'] ?? '_self');
                                                    $childL2Rel = $childL2Target === '_blank' ? 'noopener noreferrer' : null;
                                                @endphp
                                                <a href="{{ $childL2['url'] ?? '#' }}" target="{{ $childL2Target }}" @if($childL2Rel) rel="{{ $childL2Rel }}" @endif>{{ $childL2['title'] ?? '' }}</a>
                                            @endforeach
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
        <div class="public-header-actions">
            @if ($showHeaderCta)
                <a class="public-cta-btn {{ $headerCtaClass }}" href="{{ $headerConfig['cta_link'] ?? '#contact' }}">{{ $headerConfig['cta_text'] ?? 'Start Project' }}</a>
            @endif
            <button id="publicMobileToggle" class="public-mobile-toggle style-{{ $headerConfig['mobile_hamburger_style'] ?? 'classic' }}" type="button">☰</button>
        </div>
    </div>
</nav>
<div id="publicMobileOverlay" class="public-mobile-overlay"></div>
<div id="publicMobileMenu" class="public-mobile-menu mode-{{ $headerConfig['mobile_menu_mode'] ?? 'drawer' }}">
    <div class="public-mobile-menu-inner">
        @foreach ($headerMobileItems as $item)
            @php
                $mobileTarget = (string) ($item['target'] ?? '_self');
                $mobileRel = $mobileTarget === '_blank' ? 'noopener noreferrer' : null;
            @endphp
            <a class="public-mobile-item" href="{{ $item['url'] ?? '#' }}" target="{{ $mobileTarget }}" @if($mobileRel) rel="{{ $mobileRel }}" @endif>{{ $item['title'] ?? '' }}</a>
            @if (!empty($item['children']))
                <div class="public-mobile-children">
                    @foreach ($item['children'] as $child)
                        @php
                            $childTarget = (string) ($child['target'] ?? '_self');
                            $childRel = $childTarget === '_blank' ? 'noopener noreferrer' : null;
                        @endphp
                        <a class="public-mobile-item" href="{{ $child['url'] ?? '#' }}" target="{{ $childTarget }}" @if($childRel) rel="{{ $childRel }}" @endif>{{ $child['title'] ?? '' }}</a>
                    @endforeach
                </div>
            @endif
        @endforeach
        @if ($showHeaderCta)
            <a class="public-cta-btn {{ $headerCtaClass }}" href="{{ $headerConfig['cta_link'] ?? '#contact' }}">{{ $headerConfig['cta_text'] ?? 'Start Project' }}</a>
        @endif
    </div>
</div>
