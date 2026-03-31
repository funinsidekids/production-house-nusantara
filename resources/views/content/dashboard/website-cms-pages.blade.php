@extends('layouts/contentNavbarLayout')

@section('title', 'Website CMS - Pages')

@section('content')
<div class="row g-6">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-primary mb-1">WEBSITE CMS · Pages</h4>
                <p class="mb-0">Manajemen halaman statis website dengan struktur hierarkis, mode editor, revision history, schedule publish, dan proteksi password preview.</p>
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

                <form method="POST" action="{{ route('dashboard-website-cms-pages.update') }}" class="row g-4">
                    @csrf
                    <input type="hidden" name="action" value="save">

                    <div class="col-12">
                        <h6 class="mb-0">Editor Features</h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Editing Mode</label>
                        <select class="form-select" name="editor_mode">
                            <option value="wysiwyg" @selected(old('editor_mode', $form['editor_mode']) === 'wysiwyg')>WYSIWYG Editor</option>
                            <option value="block" @selected(old('editor_mode', $form['editor_mode']) === 'block')>Block-based Editor</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">WYSIWYG Engine</label>
                        <select class="form-select" name="wysiwyg_engine">
                            <option value="tinymce" @selected(old('wysiwyg_engine', $form['wysiwyg_engine']) === 'tinymce')>TinyMCE</option>
                            <option value="ckeditor" @selected(old('wysiwyg_engine', $form['wysiwyg_engine']) === 'ckeditor')>CKEditor</option>
                            <option value="plain" @selected(old('wysiwyg_engine', $form['wysiwyg_engine']) === 'plain')>Plain HTML</option>
                            <option value="directus" @selected(old('wysiwyg_engine', $form['wysiwyg_engine']) === 'directus')>Directus Rich Text</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Schedule Publish</label>
                        <input class="form-control" type="datetime-local" name="publish_at" value="{{ old('publish_at', $form['publish_at']) }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Block Editor Data JSON (Gutenberg-style)</label>
                        <textarea class="form-control font-monospace" rows="5" name="block_editor_data_json">{{ old('block_editor_data_json', $form['block_editor_data_json']) }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password Preview</label>
                        <input class="form-control mb-2" type="text" name="preview_password" value="{{ old('preview_password', $form['preview_password']) }}" placeholder="client-preview-password">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="password_protection_enabled" name="password_protection_enabled" value="1" @checked(old('password_protection_enabled', $form['password_protection_enabled']))>
                            <label class="form-check-label" for="password_protection_enabled">Enable Password Protection</label>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Homepage (Front Page)</h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Homepage Template</label>
                        <select class="form-select" name="homepage_template">
                            <option value="full-width" @selected(old('homepage_template', $form['homepage_template']) === 'full-width')>Full-width</option>
                            <option value="boxed" @selected(old('homepage_template', $form['homepage_template']) === 'boxed')>Boxed</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Homepage Modules JSON (Hero, Services, Portfolio preview, About, CTA)</label>
                        <textarea class="form-control font-monospace" rows="4" name="homepage_modules_json">{{ old('homepage_modules_json', $form['homepage_modules_json']) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label d-block mb-2">Section Visibility (Landing Page)</label>
                        <div class="row g-3">
                            <div class="col-sm-6 col-lg-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="section_services_enabled" name="section_services_enabled" value="1" @checked(old('section_services_enabled', $form['section_services_enabled']))>
                                    <label class="form-check-label" for="section_services_enabled">Services</label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="section_portfolio_enabled" name="section_portfolio_enabled" value="1" @checked(old('section_portfolio_enabled', $form['section_portfolio_enabled']))>
                                    <label class="form-check-label" for="section_portfolio_enabled">Portfolio</label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="section_why_enabled" name="section_why_enabled" value="1" @checked(old('section_why_enabled', $form['section_why_enabled']))>
                                    <label class="form-check-label" for="section_why_enabled">Why Us + Counter</label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="section_testimonials_enabled" name="section_testimonials_enabled" value="1" @checked(old('section_testimonials_enabled', $form['section_testimonials_enabled']))>
                                    <label class="form-check-label" for="section_testimonials_enabled">Testimonials</label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="section_process_enabled" name="section_process_enabled" value="1" @checked(old('section_process_enabled', $form['section_process_enabled']))>
                                    <label class="form-check-label" for="section_process_enabled">Process</label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="section_team_enabled" name="section_team_enabled" value="1" @checked(old('section_team_enabled', $form['section_team_enabled']))>
                                    <label class="form-check-label" for="section_team_enabled">Team</label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="section_contact_enabled" name="section_contact_enabled" value="1" @checked(old('section_contact_enabled', $form['section_contact_enabled']))>
                                    <label class="form-check-label" for="section_contact_enabled">Contact CTA</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">About Us</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Company Story / Timeline JSON</label>
                        <textarea class="form-control font-monospace" rows="5" name="about_story_timeline_json">{{ old('about_story_timeline_json', $form['about_story_timeline_json']) }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vision</label>
                        <textarea class="form-control" rows="5" name="about_vision">{{ old('about_vision', $form['about_vision']) }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Mission</label>
                        <textarea class="form-control" rows="5" name="about_mission">{{ old('about_mission', $form['about_mission']) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Awards & Recognition JSON</label>
                        <textarea class="form-control font-monospace" rows="4" name="about_awards_json">{{ old('about_awards_json', $form['about_awards_json']) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Client List / Logos JSON</label>
                        <textarea class="form-control font-monospace" rows="4" name="about_clients_json">{{ old('about_clients_json', $form['about_clients_json']) }}</textarea>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Services</h6>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Service Categories JSON (Film Production, Commercial/Advertisement, Documentary, Content Creation, Post-Production, Equipment Rental)</label>
                        <textarea class="form-control font-monospace" rows="7" name="services_categories_json">{{ old('services_categories_json', $form['services_categories_json']) }}</textarea>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Process / Workflow</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Production Pipeline JSON (Pre-Prod → Prod → Post)</label>
                        <textarea class="form-control font-monospace" rows="5" name="process_pipeline_json">{{ old('process_pipeline_json', $form['process_pipeline_json']) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">What to Expect per Stage JSON</label>
                        <textarea class="form-control font-monospace" rows="5" name="process_expectations_json">{{ old('process_expectations_json', $form['process_expectations_json']) }}</textarea>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">FAQ</h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">FAQ Categories JSON</label>
                        <textarea class="form-control font-monospace" rows="5" name="faq_categories_json">{{ old('faq_categories_json', $form['faq_categories_json']) }}</textarea>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Question & Answer JSON</label>
                        <textarea class="form-control font-monospace" rows="5" name="faq_items_json">{{ old('faq_items_json', $form['faq_items_json']) }}</textarea>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Privacy Policy & Terms</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Privacy Policy (HTML)</label>
                        <textarea class="form-control font-monospace" rows="6" name="privacy_content_html">{{ old('privacy_content_html', $form['privacy_content_html']) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Terms of Service (HTML)</label>
                        <textarea class="form-control font-monospace" rows="6" name="terms_content_html">{{ old('terms_content_html', $form['terms_content_html']) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Legal Version History JSON</label>
                        <textarea class="form-control font-monospace" rows="4" name="legal_version_history_json">{{ old('legal_version_history_json', $form['legal_version_history_json']) }}</textarea>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Custom Pages</h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Default Layout</label>
                        <select class="form-select" name="custom_page_layout">
                            <option value="full-width" @selected(old('custom_page_layout', $form['custom_page_layout']) === 'full-width')>Full-width</option>
                            <option value="sidebar" @selected(old('custom_page_layout', $form['custom_page_layout']) === 'sidebar')>Sidebar</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Custom CSS per Page</label>
                        <textarea class="form-control font-monospace" rows="4" name="custom_css">{{ old('custom_css', $form['custom_css']) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Custom Pages JSON (drag-drop sections representation)</label>
                        <textarea class="form-control font-monospace" rows="7" name="custom_pages_json">{{ old('custom_pages_json', $form['custom_pages_json']) }}</textarea>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Simpan Pengaturan Pages</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Revision History</h5>
                <p class="text-muted">Total revision tersimpan: {{ $revisionCount }}</p>
                @if (empty($revisions))
                    <p class="mb-0">Belum ada revision.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Saved At</th>
                                    <th>Summary</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($revisions as $index => $revision)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $revision['saved_at'] ?? '-' }}</td>
                                        <td>{{ $revision['summary'] ?? 'Revision' }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('dashboard-website-cms-pages.update') }}">
                                                @csrf
                                                <input type="hidden" name="action" value="restore">
                                                <input type="hidden" name="restore_revision_index" value="{{ $index }}">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Restore</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
