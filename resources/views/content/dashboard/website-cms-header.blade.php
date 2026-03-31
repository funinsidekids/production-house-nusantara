@extends('layouts/contentNavbarLayout')

@section('title', 'Website CMS - Header')

@section('content')
<div class="row g-6">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-primary mb-1">WEBSITE CMS · Header</h4>
                <p class="mb-0">Konfigurasi navigasi utama, top bar, mobile menu, dan CTA header.</p>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success mb-4">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('dashboard-website-cms-header.update') }}" class="row g-4">
                    @csrf

                    <div class="col-12">
                        <h6 class="mb-0">Navigation Menu Builder</h6>
                    </div>
                    <div class="col-12">
                        <div class="border rounded p-3">
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddNavItem">Tambah Item</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnAddDropdownItem">Tambah Dropdown Item</button>
                                <button type="button" class="btn btn-sm btn-outline-dark" id="btnApplyBuilder">Apply ke JSON</button>
                            </div>
                            <div id="navBuilderList" class="d-flex flex-column gap-2"></div>
                            <small class="text-muted d-inline-block mt-2">Drag-and-drop aktif untuk urutan item level utama. Untuk tier 2/3 gunakan properti children di JSON.</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Navigation JSON (max 3 tier)</label>
                        <textarea class="form-control font-monospace" rows="12" name="navigation_items_json" id="navigation_items_json">{{ old('navigation_items_json', $form['navigation_items_json']) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mega Menu Keys</label>
                        <input class="form-control" type="text" name="mega_menu_keys" value="{{ old('mega_menu_keys', $form['mega_menu_keys']) }}" placeholder="portfolio,services">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Header Layout</label>
                        <select class="form-select" name="header_layout">
                            <option value="transparent" @selected(old('header_layout', $form['header_layout']) === 'transparent')>Transparent Header</option>
                            <option value="solid" @selected(old('header_layout', $form['header_layout']) === 'solid')>Solid Header</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="sticky_on_scroll" name="sticky_on_scroll" value="1" @checked(old('sticky_on_scroll', $form['sticky_on_scroll']))>
                            <label class="form-check-label" for="sticky_on_scroll">Sticky on Scroll</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="shrink_on_scroll" name="shrink_on_scroll" value="1" @checked(old('shrink_on_scroll', $form['shrink_on_scroll']))>
                            <label class="form-check-label" for="shrink_on_scroll">Shrink Effect on Scroll</label>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Top Bar (Optional)</h6>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="topbar_enabled" name="topbar_enabled" value="1" @checked(old('topbar_enabled', $form['topbar_enabled']))>
                            <label class="form-check-label" for="topbar_enabled">Enable Top Bar</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="topbar_social_enabled" name="topbar_social_enabled" value="1" @checked(old('topbar_social_enabled', $form['topbar_social_enabled']))>
                            <label class="form-check-label" for="topbar_social_enabled">Show Social Icons</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Quick Contact Phone</label>
                        <input class="form-control" type="text" name="topbar_phone" value="{{ old('topbar_phone', $form['topbar_phone']) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Quick Contact Email</label>
                        <input class="form-control" type="email" name="topbar_email" value="{{ old('topbar_email', $form['topbar_email']) }}">
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="topbar_language_switcher" name="topbar_language_switcher" value="1" @checked(old('topbar_language_switcher', $form['topbar_language_switcher']))>
                            <label class="form-check-label" for="topbar_language_switcher">Show Language Switcher</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Top Bar CTA Text</label>
                        <input class="form-control" type="text" name="topbar_cta_text" value="{{ old('topbar_cta_text', $form['topbar_cta_text']) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Top Bar CTA Link</label>
                        <input class="form-control" type="text" name="topbar_cta_link" value="{{ old('topbar_cta_link', $form['topbar_cta_link']) }}">
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Mobile Menu</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hamburger Icon Style</label>
                        <select class="form-select" name="mobile_hamburger_style">
                            <option value="classic" @selected(old('mobile_hamburger_style', $form['mobile_hamburger_style']) === 'classic')>Classic</option>
                            <option value="rounded" @selected(old('mobile_hamburger_style', $form['mobile_hamburger_style']) === 'rounded')>Rounded</option>
                            <option value="minimal" @selected(old('mobile_hamburger_style', $form['mobile_hamburger_style']) === 'minimal')>Minimal</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mobile Menu Mode</label>
                        <select class="form-select" name="mobile_menu_mode">
                            <option value="drawer" @selected(old('mobile_menu_mode', $form['mobile_menu_mode']) === 'drawer')>Slide-in Drawer</option>
                            <option value="dropdown" @selected(old('mobile_menu_mode', $form['mobile_menu_mode']) === 'dropdown')>Dropdown</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Mobile-specific Menu JSON</label>
                        <textarea class="form-control font-monospace" rows="6" name="mobile_menu_items_json">{{ old('mobile_menu_items_json', $form['mobile_menu_items_json']) }}</textarea>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Header CTA</h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Button Text</label>
                        <input class="form-control" type="text" name="header_cta_text" value="{{ old('header_cta_text', $form['header_cta_text']) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Button Link</label>
                        <input class="form-control" type="text" name="header_cta_link" value="{{ old('header_cta_link', $form['header_cta_link']) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Color Scheme</label>
                        <select class="form-select" name="header_cta_color">
                            <option value="gold" @selected(old('header_cta_color', $form['header_cta_color']) === 'gold')>Gold</option>
                            <option value="orange" @selected(old('header_cta_color', $form['header_cta_color']) === 'orange')>Orange</option>
                            <option value="white" @selected(old('header_cta_color', $form['header_cta_color']) === 'white')>White</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="header_cta_show_home" name="header_cta_show_home" value="1" @checked(old('header_cta_show_home', $form['header_cta_show_home']))>
                            <label class="form-check-label" for="header_cta_show_home">Show CTA on Homepage</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="header_cta_show_inner" name="header_cta_show_inner" value="1" @checked(old('header_cta_show_inner', $form['header_cta_show_inner']))>
                            <label class="form-check-label" for="header_cta_show_inner">Show CTA on Inner Pages</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Simpan Pengaturan Header</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    (() => {
        const navBuilderList = document.getElementById('navBuilderList');
        const navJsonField = document.getElementById('navigation_items_json');
        const btnAddNavItem = document.getElementById('btnAddNavItem');
        const btnAddDropdownItem = document.getElementById('btnAddDropdownItem');
        const btnApplyBuilder = document.getElementById('btnApplyBuilder');

        const toSafeArray = (raw) => {
            try {
                const parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed : [];
            } catch {
                return [];
            }
        };

        const renderItems = (items) => {
            if (!(navBuilderList instanceof HTMLElement)) {
                return;
            }
            navBuilderList.innerHTML = '';
            items.forEach((item, index) => {
                const row = document.createElement('div');
                row.className = 'border rounded p-2 bg-body';
                row.draggable = true;
                row.dataset.index = String(index);
                row.innerHTML = `
                    <div class="row g-2 align-items-center">
                        <div class="col-md-3"><input class="form-control form-control-sm js-title" placeholder="Title" value="${(item.title || '').replace(/"/g, '&quot;')}"></div>
                        <div class="col-md-4"><input class="form-control form-control-sm js-url" placeholder="URL" value="${(item.url || '').replace(/"/g, '&quot;')}"></div>
                        <div class="col-md-2">
                            <select class="form-select form-select-sm js-type">
                                <option value="internal" ${item.type === 'internal' ? 'selected' : ''}>Internal</option>
                                <option value="external" ${item.type === 'external' ? 'selected' : ''}>External</option>
                                <option value="mega" ${item.type === 'mega' ? 'selected' : ''}>Mega</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select form-select-sm js-target">
                                <option value="_self" ${(item.target || '_self') === '_self' ? 'selected' : ''}>Same Tab</option>
                                <option value="_blank" ${(item.target || '_self') === '_blank' ? 'selected' : ''}>New Tab</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-grid"><button type="button" class="btn btn-sm btn-outline-danger js-remove">×</button></div>
                    </div>
                `;
                navBuilderList.appendChild(row);
            });
        };

        const extractItems = () => {
            if (!(navBuilderList instanceof HTMLElement)) {
                return [];
            }
            return Array.from(navBuilderList.querySelectorAll('[data-index]')).map((row) => {
                const title = row.querySelector('.js-title');
                const url = row.querySelector('.js-url');
                const type = row.querySelector('.js-type');
                const target = row.querySelector('.js-target');
                return {
                    title: title instanceof HTMLInputElement ? title.value.trim() : 'Untitled',
                    url: url instanceof HTMLInputElement ? url.value.trim() : '#',
                    type: type instanceof HTMLSelectElement ? type.value : 'internal',
                    target: target instanceof HTMLSelectElement ? target.value : '_self',
                    children: [],
                };
            });
        };

        const applyToJson = () => {
            if (!(navJsonField instanceof HTMLTextAreaElement)) {
                return;
            }
            navJsonField.value = JSON.stringify(extractItems(), null, 2);
        };

        const initial = toSafeArray(navJsonField instanceof HTMLTextAreaElement ? navJsonField.value : '[]');
        renderItems(initial);

        btnAddNavItem?.addEventListener('click', () => {
            const next = [...extractItems(), { title: 'Menu Baru', url: '#', type: 'internal', target: '_self', children: [] }];
            renderItems(next);
            applyToJson();
        });

        btnAddDropdownItem?.addEventListener('click', () => {
            const next = [...extractItems(), { title: 'Dropdown', url: '#', type: 'mega', target: '_self', children: [] }];
            renderItems(next);
            applyToJson();
        });

        btnApplyBuilder?.addEventListener('click', applyToJson);

        navBuilderList?.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement) || !target.classList.contains('js-remove')) {
                return;
            }
            const card = target.closest('[data-index]');
            if (!(card instanceof HTMLElement)) {
                return;
            }
            card.remove();
            renderItems(extractItems());
            applyToJson();
        });

        navBuilderList?.addEventListener('input', applyToJson);
        navBuilderList?.addEventListener('change', applyToJson);

        let dragged = null;
        navBuilderList?.addEventListener('dragstart', (event) => {
            const target = event.target;
            if (target instanceof HTMLElement && target.dataset.index !== undefined) {
                dragged = target;
            }
        });
        navBuilderList?.addEventListener('dragover', (event) => event.preventDefault());
        navBuilderList?.addEventListener('drop', (event) => {
            event.preventDefault();
            const target = event.target;
            if (!(target instanceof HTMLElement) || !(dragged instanceof HTMLElement)) {
                return;
            }
            const dropRow = target.closest('[data-index]');
            if (!(dropRow instanceof HTMLElement) || dropRow === dragged || !(navBuilderList instanceof HTMLElement)) {
                return;
            }
            const rows = Array.from(navBuilderList.children);
            const draggedIndex = rows.indexOf(dragged);
            const dropIndex = rows.indexOf(dropRow);
            const items = extractItems();
            const [moved] = items.splice(draggedIndex, 1);
            items.splice(dropIndex, 0, moved);
            renderItems(items);
            applyToJson();
            dragged = null;
        });
    })();
</script>
@endsection
