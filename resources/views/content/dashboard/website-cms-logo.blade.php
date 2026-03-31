@extends('layouts/contentNavbarLayout')

@section('title', 'Website CMS - Logo')

@section('content')
@php
    $assetUrl = static fn (?string $path): string => $path ? asset('storage/' . ltrim($path, '/')) : '';
@endphp
<div class="row g-6">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-primary mb-1">WEBSITE CMS · Logo</h4>
                <p class="mb-0">Manajemen asset branding, favicon, dan logo placement.</p>
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

                <form method="POST" action="{{ route('dashboard-website-cms-logo.update') }}" enctype="multipart/form-data" class="row g-4">
                    @csrf

                    <div class="col-12">
                        <h6 class="mb-0">Primary Logo</h6>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label">Primary Logo (PNG/SVG)</label>
                        <input class="form-control" type="file" name="primary_logo" id="primary_logo" accept=".png,.svg,.jpg,.jpeg,.webp">
                        <small class="text-muted">Ideal: 200x60 desktop, 150x45 mobile. Sistem generate @2x dan @3x otomatis.</small>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Primary @2x</label>
                        <input class="form-control" type="text" value="{{ $form['logo_primary_2x'] }}" readonly>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Primary @3x</label>
                        <input class="form-control" type="text" value="{{ $form['logo_primary_3x'] }}" readonly>
                    </div>

                    <div class="col-12">
                        <h6 class="mb-0">Secondary / Alt Logo</h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Secondary Light</label>
                        <input class="form-control" type="file" name="secondary_logo_light" id="secondary_logo_light" accept=".png,.svg,.jpg,.jpeg,.webp">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Secondary Dark</label>
                        <input class="form-control" type="file" name="secondary_logo_dark" id="secondary_logo_dark" accept=".png,.svg,.jpg,.jpeg,.webp">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Monochrome</label>
                        <input class="form-control" type="file" name="monochrome_logo" id="monochrome_logo" accept=".png,.svg,.jpg,.jpeg,.webp">
                    </div>

                    <div class="col-12">
                        <h6 class="mb-0">Favicon & Loading</h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Favicon 32x32 (ICO/PNG)</label>
                        <input class="form-control" type="file" name="favicon_32" id="favicon_32" accept=".ico,.png">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Apple Touch Icon 180x180</label>
                        <input class="form-control" type="file" name="apple_touch_icon" id="apple_touch_icon" accept=".png">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Loading Logo (Animated SVG)</label>
                        <input class="form-control" type="file" name="loading_logo" id="loading_logo" accept=".svg,.png,.webp,.gif">
                    </div>

                    <div class="col-12">
                        <h6 class="mb-0">Sticky / Footer Variant</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sticky Header Logo</label>
                        <input class="form-control" type="file" name="sticky_logo" id="sticky_logo" accept=".png,.svg,.jpg,.jpeg,.webp">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Footer Logo Variant</label>
                        <input class="form-control" type="file" name="footer_logo" id="footer_logo" accept=".png,.svg,.jpg,.jpeg,.webp">
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Logo Placement Settings</h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Header Position</label>
                        <select class="form-select" name="header_position" id="header_position">
                            <option value="left" @selected(old('header_position', $form['header_position']) === 'left')>Left</option>
                            <option value="center" @selected(old('header_position', $form['header_position']) === 'center')>Center</option>
                            <option value="right" @selected(old('header_position', $form['header_position']) === 'right')>Right</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sticky Header Variant</label>
                        <select class="form-select" name="sticky_variant">
                            <option value="primary" @selected(old('sticky_variant', $form['sticky_variant']) === 'primary')>Primary</option>
                            <option value="secondary-light" @selected(old('sticky_variant', $form['sticky_variant']) === 'secondary-light')>Secondary Light</option>
                            <option value="secondary-dark" @selected(old('sticky_variant', $form['sticky_variant']) === 'secondary-dark')>Secondary Dark</option>
                            <option value="sticky" @selected(old('sticky_variant', $form['sticky_variant']) === 'sticky')>Sticky Custom</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Footer Variant</label>
                        <select class="form-select" name="footer_variant">
                            <option value="primary" @selected(old('footer_variant', $form['footer_variant']) === 'primary')>Primary</option>
                            <option value="secondary-light" @selected(old('footer_variant', $form['footer_variant']) === 'secondary-light')>Secondary Light</option>
                            <option value="secondary-dark" @selected(old('footer_variant', $form['footer_variant']) === 'secondary-dark')>Secondary Dark</option>
                            <option value="monochrome" @selected(old('footer_variant', $form['footer_variant']) === 'monochrome')>Monochrome</option>
                            <option value="footer" @selected(old('footer_variant', $form['footer_variant']) === 'footer')>Footer Custom</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Framing Offset X (%)</label>
                        <input type="range" class="form-range" min="0" max="100" name="preview_offset_x" id="preview_offset_x" value="{{ old('preview_offset_x', $form['preview_offset_x']) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Framing Offset Y (%)</label>
                        <input type="range" class="form-range" min="0" max="100" name="preview_offset_y" id="preview_offset_y" value="{{ old('preview_offset_y', $form['preview_offset_y']) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Framing Scale</label>
                        <input type="range" class="form-range" min="0.6" max="2" step="0.01" name="preview_scale" id="preview_scale" value="{{ old('preview_scale', $form['preview_scale']) }}">
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Preview Real-time (Header)</h6>
                    </div>
                    <div class="col-12">
                        <div class="border rounded p-3 bg-dark-subtle">
                            <div id="logoHeaderPreviewBar" class="d-flex align-items-center py-2 px-3 bg-white border rounded" style="min-height:74px;">
                                <img
                                    id="logoHeaderPreviewImg"
                                    src="{{ $assetUrl($form['logo_primary']) }}"
                                    alt="Header Logo Preview"
                                    style="height:44px;max-width:280px;object-fit:contain;object-position:50% 50%;transform-origin:center center;"
                                >
                            </div>
                            <small id="logoPreviewStatus" class="text-muted d-inline-block mt-2">Preview siap.</small>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Current Assets</h6>
                    </div>
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Asset</th>
                                        <th>Path</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>Primary</td><td>{{ $form['logo_primary'] }}</td></tr>
                                    <tr><td>Secondary Light</td><td>{{ $form['logo_secondary_light'] }}</td></tr>
                                    <tr><td>Secondary Dark</td><td>{{ $form['logo_secondary_dark'] }}</td></tr>
                                    <tr><td>Monochrome</td><td>{{ $form['logo_monochrome'] }}</td></tr>
                                    <tr><td>Favicon 32x32</td><td>{{ $form['logo_favicon_32'] }}</td></tr>
                                    <tr><td>Apple Touch</td><td>{{ $form['logo_apple_touch'] }}</td></tr>
                                    <tr><td>Loading Logo</td><td>{{ $form['logo_loading_svg'] }}</td></tr>
                                    <tr><td>Sticky Logo</td><td>{{ $form['logo_sticky'] }}</td></tr>
                                    <tr><td>Footer Logo</td><td>{{ $form['logo_footer'] }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Simpan Pengaturan Logo</button>
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
        const primaryInput = document.getElementById('primary_logo');
        const headerPreviewImg = document.getElementById('logoHeaderPreviewImg');
        const previewBar = document.getElementById('logoHeaderPreviewBar');
        const headerPositionInput = document.getElementById('header_position');
        const offsetXInput = document.getElementById('preview_offset_x');
        const offsetYInput = document.getElementById('preview_offset_y');
        const scaleInput = document.getElementById('preview_scale');
        const previewStatus = document.getElementById('logoPreviewStatus');
        let statusTimer = null;

        const setStatus = (message) => {
            if (!(previewStatus instanceof HTMLElement)) {
                return;
            }
            previewStatus.textContent = message;
            previewStatus.classList.remove('text-muted');
            previewStatus.classList.add('text-success');
            if (statusTimer !== null) {
                window.clearTimeout(statusTimer);
            }
            statusTimer = window.setTimeout(() => {
                previewStatus.textContent = 'Preview siap.';
                previewStatus.classList.remove('text-success');
                previewStatus.classList.add('text-muted');
            }, 1300);
        };

        const applyFraming = () => {
            if (!(headerPreviewImg instanceof HTMLImageElement)) {
                return;
            }
            const offsetX = offsetXInput instanceof HTMLInputElement ? offsetXInput.value : '50';
            const offsetY = offsetYInput instanceof HTMLInputElement ? offsetYInput.value : '50';
            const scale = scaleInput instanceof HTMLInputElement ? scaleInput.value : '1';
            headerPreviewImg.style.objectPosition = `${offsetX}% ${offsetY}%`;
            headerPreviewImg.style.transform = `scale(${scale})`;
            setStatus('Preview diperbarui');
        };

        const applyHeaderPosition = () => {
            if (!(previewBar instanceof HTMLElement) || !(headerPositionInput instanceof HTMLSelectElement)) {
                return;
            }
            const map = {
                left: 'flex-start',
                center: 'center',
                right: 'flex-end',
            };
            previewBar.style.justifyContent = map[headerPositionInput.value] ?? 'flex-start';
            setStatus('Preview diperbarui');
        };

        primaryInput?.addEventListener('change', (event) => {
            const target = event.currentTarget;
            if (!(target instanceof HTMLInputElement) || !(headerPreviewImg instanceof HTMLImageElement)) {
                return;
            }
            const file = target.files?.[0];
            if (!file) {
                return;
            }
            headerPreviewImg.src = URL.createObjectURL(file);
            setStatus('Preview diperbarui');
        });

        headerPositionInput?.addEventListener('change', applyHeaderPosition);
        offsetXInput?.addEventListener('input', applyFraming);
        offsetYInput?.addEventListener('input', applyFraming);
        scaleInput?.addEventListener('input', applyFraming);

        applyHeaderPosition();
        applyFraming();
    })();
</script>
@endsection
