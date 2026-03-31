@extends('layouts/contentNavbarLayout')

@section('title', 'Website CMS - General')

@section('content')
<div class="row g-6">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-primary mb-1">WEBSITE CMS · General</h4>
                <p class="mb-0">Konfigurasi global website & identitas dasar.</p>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success mb-4">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
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

                <form method="POST" action="{{ route('dashboard-website-cms-general.update') }}" class="row g-4">
                    @csrf

                    <div class="col-12">
                        <h6 class="mb-0">CMS Engine</h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Dashboard CMS Engine</label>
                        <select class="form-select" id="cms_engine" name="cms_engine">
                            <option value="native" @selected(old('cms_engine', $form['cms_engine']) === 'native')>Native Laravel CMS</option>
                            <option value="directus" @selected(old('cms_engine', $form['cms_engine']) === 'directus')>Directus (Super Modern CMS)</option>
                        </select>
                    </div>
                    <div class="col-md-8 d-flex align-items-end">
                        <small class="text-muted">Jika memilih Directus, endpoint akan disimpan sebagai konfigurasi headless untuk integrasi konten.</small>
                    </div>
                    <div class="col-12" id="directusConfigBlock">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Directus Base URL</label>
                                <input type="url" class="form-control" name="directus_url" value="{{ old('directus_url', $form['directus_url']) }}" placeholder="https://cms.domain.com">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Project / Space</label>
                                <input type="text" class="form-control" name="directus_project" value="{{ old('directus_project', $form['directus_project']) }}" placeholder="phn-main">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Primary Collection</label>
                                <input type="text" class="form-control" name="directus_collection" value="{{ old('directus_collection', $form['directus_collection']) }}" placeholder="landing_content">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Slides Collection</label>
                                <input type="text" class="form-control" name="directus_slides_collection" value="{{ old('directus_slides_collection', $form['directus_slides_collection']) }}" placeholder="hero_slides">
                            </div>
                            <div class="col-md-9">
                                <label class="form-label">Static Access Token</label>
                                <input type="password" class="form-control" name="directus_token" value="{{ old('directus_token', $form['directus_token']) }}" autocomplete="off">
                            </div>
                            <div class="col-12">
                                <small class="text-muted">Push/Pull sinkronisasi data live. Provision membuat collection + field + role + permission secara otomatis.</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <h6 class="mb-0">General Identity</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Site Title</label>
                        <input type="text" class="form-control" name="site_title" value="{{ old('site_title', $form['site_title']) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tagline</label>
                        <input type="text" class="form-control" name="tagline" value="{{ old('tagline', $form['tagline']) }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Site Description</label>
                        <textarea class="form-control" rows="3" name="site_description">{{ old('site_description', $form['site_description']) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Keywords</label>
                        <textarea class="form-control" rows="2" name="keywords">{{ old('keywords', $form['keywords']) }}</textarea>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Contact Info Global</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Utama</label>
                        <input type="email" class="form-control" name="contact_email" value="{{ old('contact_email', $form['contact_email']) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone / WhatsApp</label>
                        <input type="text" class="form-control" name="contact_phone" value="{{ old('contact_phone', $form['contact_phone']) }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Alamat Studio/Kantor</label>
                        <input type="text" class="form-control" id="office_address" name="office_address" value="{{ old('office_address', $form['office_address']) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jam Operasional</label>
                        <input type="text" class="form-control" name="operational_hours" value="{{ old('operational_hours', $form['operational_hours']) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Map Query (Google Maps)</label>
                        <input type="text" class="form-control" id="map_query" name="map_query" value="{{ old('map_query', $form['map_query']) }}" placeholder="Contoh: -8.112972,112.311306 atau alamat lengkap studio">
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary" id="btnUseAddressMapQuery">Gunakan Alamat sebagai Map Query</button>
                        <button type="button" class="btn btn-outline-secondary" id="btnPreviewMap">Preview Map</button>
                    </div>
                    <div class="col-12">
                        <small id="mapPreviewStatus" class="text-muted">Autosync aktif.</small>
                    </div>
                    <div class="col-12">
                        <iframe
                            id="cmsMapPreview"
                            src="https://www.google.com/maps?q={{ urlencode(old('map_query', $form['map_query'])) }}&output=embed"
                            title="Preview Map"
                            loading="lazy"
                            style="width:100%;min-height:320px;border:0;border-radius:.5rem;"
                        ></iframe>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Social Media Links</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Instagram</label>
                        <input type="url" class="form-control" name="social_instagram" value="{{ old('social_instagram', $form['social_instagram']) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">YouTube</label>
                        <input type="url" class="form-control" name="social_youtube" value="{{ old('social_youtube', $form['social_youtube']) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Vimeo</label>
                        <input type="url" class="form-control" name="social_vimeo" value="{{ old('social_vimeo', $form['social_vimeo']) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">TikTok</label>
                        <input type="url" class="form-control" name="social_tiktok" value="{{ old('social_tiktok', $form['social_tiktok']) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">LinkedIn</label>
                        <input type="url" class="form-control" name="social_linkedin" value="{{ old('social_linkedin', $form['social_linkedin']) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Facebook</label>
                        <input type="url" class="form-control" name="social_facebook" value="{{ old('social_facebook', $form['social_facebook']) }}">
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Landing Section · Testimonials</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Judul Testimoni</label>
                        <input type="text" class="form-control" name="testimonial_title" value="{{ old('testimonial_title', $form['testimonial_title']) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Items JSON</label>
                        <textarea class="form-control font-monospace" rows="5" name="testimonial_items" placeholder='[{"name":"Brand A","role":"Marketing Lead","quote":"Tim PHN sangat profesional."}]'>{{ old('testimonial_items', $form['testimonial_items']) }}</textarea>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Analytics & SEO</h6>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Analytics Code (Google Analytics/GTM)</label>
                        <textarea class="form-control" rows="3" name="analytics_code">{{ old('analytics_code', $form['analytics_code']) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Default OG Tags</label>
                        <textarea class="form-control" rows="3" name="seo_default_og">{{ old('seo_default_og', $form['seo_default_og']) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Schema Markup</label>
                        <textarea class="form-control" rows="3" name="seo_schema_markup">{{ old('seo_schema_markup', $form['seo_schema_markup']) }}</textarea>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Localization & Maintenance</h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Language / Localization</label>
                        <select class="form-select" name="language_mode">
                            <option value="id" @selected(old('language_mode', $form['language_mode']) === 'id')>ID</option>
                            <option value="en" @selected(old('language_mode', $form['language_mode']) === 'en')>EN</option>
                            <option value="id-en" @selected(old('language_mode', $form['language_mode']) === 'id-en')>ID/EN</option>
                        </select>
                    </div>
                    <div class="col-md-8 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="maintenance_mode" name="maintenance_mode" value="1" @checked(old('maintenance_mode', $form['maintenance_mode']))>
                            <label class="form-check-label" for="maintenance_mode">Maintenance Mode (Site Under Construction)</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                    </div>
                </form>
                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <form method="POST" action="{{ route('dashboard-website-cms-general.directus.provision') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-success w-100">Provision Collections + Roles + Permissions</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form method="POST" action="{{ route('dashboard-website-cms-general.directus.push') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary w-100">Push ke Directus</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form method="POST" action="{{ route('dashboard-website-cms-general.directus.pull') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary w-100">Pull dari Directus</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('dashboard-website-cms-general.directus.blueprint') }}" class="btn btn-outline-info w-100">Download Directus Schema Blueprint (JSON)</a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('dashboard-website-cms-general.directus.field-mapping') }}" class="btn btn-outline-dark w-100">Download Field Mapping (JSON)</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    (() => {
        const officeAddressInput = document.getElementById('office_address');
        const mapQueryInput = document.getElementById('map_query');
        const useAddressButton = document.getElementById('btnUseAddressMapQuery');
        const previewButton = document.getElementById('btnPreviewMap');
        const mapPreview = document.getElementById('cmsMapPreview');
        const previewStatus = document.getElementById('mapPreviewStatus');
        const cmsEngineSelect = document.getElementById('cms_engine');
        const directusConfigBlock = document.getElementById('directusConfigBlock');
        let previewDebounceTimer = null;
        let statusResetTimer = null;

        const setPreviewStatus = (text) => {
            if (!(previewStatus instanceof HTMLElement)) {
                return;
            }
            previewStatus.textContent = text;
            previewStatus.classList.remove('text-muted');
            previewStatus.classList.add('text-success');
            if (statusResetTimer !== null) {
                window.clearTimeout(statusResetTimer);
            }
            statusResetTimer = window.setTimeout(() => {
                previewStatus.textContent = 'Autosync aktif.';
                previewStatus.classList.remove('text-success');
                previewStatus.classList.add('text-muted');
            }, 1400);
        };

        const updatePreview = () => {
            if (!(mapQueryInput instanceof HTMLInputElement) || !(mapPreview instanceof HTMLIFrameElement)) {
                return;
            }
            const query = mapQueryInput.value.trim();
            if (query === '') {
                return;
            }
            mapPreview.src = `https://www.google.com/maps?q=${encodeURIComponent(query)}&output=embed`;
            setPreviewStatus('Preview diperbarui');
        };

        useAddressButton?.addEventListener('click', () => {
            if (!(officeAddressInput instanceof HTMLInputElement) || !(mapQueryInput instanceof HTMLInputElement)) {
                return;
            }
            mapQueryInput.value = officeAddressInput.value.trim();
            updatePreview();
        });

        previewButton?.addEventListener('click', updatePreview);
        mapQueryInput?.addEventListener('input', () => {
            if (previewDebounceTimer !== null) {
                window.clearTimeout(previewDebounceTimer);
            }
            previewDebounceTimer = window.setTimeout(updatePreview, 450);
        });
        mapQueryInput?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                updatePreview();
            }
        });

        const syncDirectusVisibility = () => {
            if (!(cmsEngineSelect instanceof HTMLSelectElement) || !(directusConfigBlock instanceof HTMLElement)) {
                return;
            }
            const isDirectus = cmsEngineSelect.value === 'directus';
            directusConfigBlock.style.display = isDirectus ? '' : 'none';
        };
        syncDirectusVisibility();
        cmsEngineSelect?.addEventListener('change', syncDirectusVisibility);
    })();
</script>
@endsection
