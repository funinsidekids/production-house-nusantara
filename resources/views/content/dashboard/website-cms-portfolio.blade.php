@extends('layouts/contentNavbarLayout')

@section('title', 'Website CMS - Portfolio')

@section('content')
<div class="row g-6">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-primary mb-1">WEBSITE CMS · Portfolio</h4>
                <p class="mb-0">Core showcase feature untuk Production House: project entry, category SEO, display layout concept, single project behavior, advanced security, dan workflow integration.</p>
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

                <form method="POST" action="{{ route('dashboard-website-cms-portfolio.update') }}" enctype="multipart/form-data" class="row g-4">
                    @csrf
                    <input type="hidden" name="action" value="save">
                    <style>
                        .project-card {
                            border: 1px solid var(--bs-border-color);
                            border-radius: .75rem;
                            padding: .9rem;
                            background: var(--bs-body-bg);
                        }
                        .project-card.dragging {
                            opacity: .65;
                            border-color: var(--bs-primary);
                        }
                        .project-card.drop-target {
                            outline: 2px dashed var(--bs-primary);
                            outline-offset: 2px;
                        }
                        .project-drag-handle {
                            cursor: move;
                        }
                        .portfolio-preview-shell {
                            border: 1px dashed var(--bs-border-color);
                            border-radius: .75rem;
                            padding: .85rem;
                            background: color-mix(in srgb, var(--bs-body-bg) 85%, #000 15%);
                        }
                        .portfolio-preview-topbar {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            gap: .75rem;
                            flex-wrap: wrap;
                            margin-bottom: .6rem;
                        }
                        .preview-device-toggle {
                            display: inline-flex;
                            gap: .35rem;
                            flex-wrap: wrap;
                        }
                        .preview-device-btn {
                            border: 1px solid var(--bs-border-color);
                            border-radius: 999px;
                            background: transparent;
                            color: var(--bs-body-color);
                            font-size: .75rem;
                            padding: .28rem .65rem;
                            cursor: pointer;
                        }
                        .preview-device-btn.active {
                            border-color: var(--bs-primary);
                            color: var(--bs-primary);
                            font-weight: 600;
                        }
                        .portfolio-preview-frame {
                            width: min(100%, 1100px);
                            margin-inline: auto;
                            transition: width .25s ease;
                        }
                        .portfolio-preview-shell.device-tablet .portfolio-preview-frame {
                            width: min(100%, 820px);
                        }
                        .portfolio-preview-shell.device-mobile .portfolio-preview-frame {
                            width: min(100%, 420px);
                        }
                        .portfolio-preview-grid {
                            display: grid;
                            gap: .6rem;
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                        }
                        .portfolio-preview-grid.cols-2 {
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                        }
                        .portfolio-preview-grid.cols-3 {
                            grid-template-columns: repeat(3, minmax(0, 1fr));
                        }
                        .portfolio-preview-grid.cols-4 {
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                        }
                        .portfolio-preview-grid.cols-6 {
                            grid-template-columns: repeat(6, minmax(0, 1fr));
                        }
                        .portfolio-preview-grid.mode-masonry .preview-item:nth-child(2n) {
                            min-height: 165px;
                        }
                        .portfolio-preview-grid.mode-cinematic .preview-item {
                            border-color: #d4a353;
                        }
                        .portfolio-preview-grid.mode-card .preview-body {
                            background: transparent;
                            position: static;
                            border-radius: 0;
                            margin-top: .35rem;
                            padding: 0;
                        }
                        .portfolio-preview-grid.mode-video .preview-item[data-media="photo"] {
                            display: none;
                        }
                        .portfolio-preview-shell.device-tablet .portfolio-preview-grid.mode-responsive {
                            grid-template-columns: repeat(3, minmax(0, 1fr));
                        }
                        .portfolio-preview-shell.device-mobile .portfolio-preview-grid.mode-responsive {
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                        }
                        .portfolio-preview-grid.mode-hover.hover-overlay .preview-body {
                            opacity: 0;
                            transform: translateY(8px);
                            transition: .2s ease;
                        }
                        .portfolio-preview-grid.mode-hover.hover-overlay .preview-item:hover .preview-body {
                            opacity: 1;
                            transform: translateY(0);
                        }
                        .portfolio-preview-grid.mode-hover.hover-zoom .preview-thumb {
                            transition: transform .25s ease;
                        }
                        .portfolio-preview-grid.mode-hover.hover-zoom .preview-item:hover .preview-thumb {
                            transform: scale(1.07);
                        }
                        .portfolio-preview-grid.mode-hover.hover-lift .preview-item {
                            transition: transform .2s ease;
                        }
                        .portfolio-preview-grid.mode-hover.hover-lift .preview-item:hover {
                            transform: translateY(-6px);
                        }
                        .preview-item {
                            border: 1px solid var(--bs-border-color);
                            border-radius: .65rem;
                            overflow: hidden;
                            min-height: 130px;
                            position: relative;
                            background: #101015;
                        }
                        .preview-item.bento-large {
                            grid-column: span 2;
                            min-height: 180px;
                        }
                        .preview-thumb {
                            width: 100%;
                            height: 100%;
                            object-fit: cover;
                            display: block;
                        }
                        .preview-body {
                            position: absolute;
                            left: .45rem;
                            right: .45rem;
                            bottom: .45rem;
                            background: rgba(0, 0, 0, .7);
                            border-radius: .5rem;
                            padding: .35rem .45rem;
                            color: #fff;
                            font-size: .72rem;
                        }
                        .preview-empty {
                            border: 1px dashed var(--bs-border-color);
                            border-radius: .6rem;
                            padding: .8rem;
                            color: var(--bs-secondary-color);
                            text-align: center;
                            grid-column: 1 / -1;
                        }
                        .upload-progress-wrap {
                            display: none;
                            margin-top: .7rem;
                        }
                        .upload-progress-wrap.active {
                            display: block;
                        }
                    </style>

                    <div class="col-12">
                        <h6 class="mb-0">Portfolio Management · Project Entry</h6>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Project Entries (Visual Repeater)</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddProject">Tambah Project</button>
                        </div>
                        <div id="projectRepeater" class="d-flex flex-column gap-3">
                            @php $projectsForm = old('projects_form', $form['projects_form']); @endphp
                            @foreach ($projectsForm as $index => $project)
                                <div class="project-card js-project-card" draggable="true">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary project-drag-handle">↕</button>
                                            <h6 class="mb-0">Project #<span class="js-project-order">{{ $index + 1 }}</span></h6>
                                        </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary js-move-up">↑</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary js-move-down">↓</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger js-remove-project">Hapus</button>
                                    </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-4"><label class="form-label">Project Title</label><input class="form-control" type="text" name="projects_form[{{ $index }}][title]" value="{{ $project['title'] ?? '' }}"></div>
                                        <div class="col-md-3"><label class="form-label">Slug</label><input class="form-control" type="text" name="projects_form[{{ $index }}][slug]" value="{{ $project['slug'] ?? '' }}"></div>
                                        <div class="col-md-3"><label class="form-label">Client Name</label><input class="form-control" type="text" name="projects_form[{{ $index }}][client_name]" value="{{ $project['client_name'] ?? '' }}"></div>
                                        <div class="col-md-2"><label class="form-label">Year</label><input class="form-control" type="number" min="2000" max="2100" name="projects_form[{{ $index }}][year]" value="{{ $project['year'] ?? '' }}"></div>
                                        <div class="col-md-3"><label class="form-label">Category</label><select class="form-select" name="projects_form[{{ $index }}][category]"><option @selected(($project['category'] ?? 'Commercial')==='Commercial')>Commercial</option><option @selected(($project['category'] ?? '')==='Film')>Film</option><option @selected(($project['category'] ?? '')==='Doc')>Doc</option><option @selected(($project['category'] ?? '')==='Event')>Event</option><option @selected(($project['category'] ?? '')==='Content')>Content</option></select></div>
                                        <div class="col-md-5"><label class="form-label">Featured Image URL</label><input class="form-control" type="text" name="projects_form[{{ $index }}][featured_image]" value="{{ $project['featured_image'] ?? '' }}"></div>
                                        <div class="col-md-3"><label class="form-label">Upload Featured Image</label><input class="form-control" type="file" name="projects_form[{{ $index }}][featured_image_file]" accept=".jpg,.jpeg,.png,.webp"></div>
                                        <div class="col-md-4"><label class="form-label">Video External Link</label><input class="form-control" type="text" name="projects_form[{{ $index }}][video_embed]" value="{{ $project['video_embed'] ?? '' }}"></div>
                                        <div class="col-md-4"><label class="form-label">Upload Video File (auto convert WebM)</label><input class="form-control" type="file" name="projects_form[{{ $index }}][video_file]" accept="video/*"></div>
                                        <div class="col-md-3"><label class="form-label">Duration</label><input class="form-control" type="text" name="projects_form[{{ $index }}][duration]" value="{{ $project['duration'] ?? '' }}"></div>
                                        <div class="col-md-3"><label class="form-label">Popular Score</label><input class="form-control" type="number" min="0" max="9999" name="projects_form[{{ $index }}][popular_score]" value="{{ $project['popular_score'] ?? 0 }}"></div>
                                        <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" rows="2" name="projects_form[{{ $index }}][description]">{{ $project['description'] ?? '' }}</textarea></div>
                                        <div class="col-md-6"><label class="form-label">Services Used (comma separated)</label><input class="form-control" type="text" name="projects_form[{{ $index }}][services_used_text]" value="{{ $project['services_used_text'] ?? '' }}"></div>
                                        <div class="col-md-4"><label class="form-label">Gallery URLs (1 URL per line)</label><textarea class="form-control font-monospace" rows="2" name="projects_form[{{ $index }}][gallery_images_text]">{{ $project['gallery_images_text'] ?? '' }}</textarea></div>
                                        <div class="col-md-2"><label class="form-label">Upload Gallery</label><input class="form-control" type="file" name="projects_form[{{ $index }}][gallery_files][]" accept=".jpg,.jpeg,.png,.webp" multiple></div>
                                        <div class="col-md-6"><label class="form-label">Team Credits (Role:Name per line)</label><textarea class="form-control font-monospace" rows="2" name="projects_form[{{ $index }}][team_credits_text]">{{ $project['team_credits_text'] ?? '' }}</textarea></div>
                                        <div class="col-md-6"><label class="form-label">Awards (1 item per line)</label><textarea class="form-control font-monospace" rows="2" name="projects_form[{{ $index }}][awards_text]">{{ $project['awards_text'] ?? '' }}</textarea></div>
                                        <div class="col-12"><label class="form-label">Client Testimonial</label><textarea class="form-control" rows="2" name="projects_form[{{ $index }}][testimonial]">{{ $project['testimonial'] ?? '' }}</textarea></div>
                                        <div class="col-md-4"><label class="form-label">Case Study Challenge</label><textarea class="form-control" rows="2" name="projects_form[{{ $index }}][challenge]">{{ $project['challenge'] ?? '' }}</textarea></div>
                                        <div class="col-md-4"><label class="form-label">Case Study Solution</label><textarea class="form-control" rows="2" name="projects_form[{{ $index }}][solution]">{{ $project['solution'] ?? '' }}</textarea></div>
                                        <div class="col-md-4"><label class="form-label">Case Study Result</label><textarea class="form-control" rows="2" name="projects_form[{{ $index }}][result]">{{ $project['result'] ?? '' }}</textarea></div>
                                        <div class="col-md-3"><label class="form-label">Before Image</label><input class="form-control" type="text" name="projects_form[{{ $index }}][before_image]" value="{{ $project['before_image'] ?? '' }}"></div>
                                        <div class="col-md-3"><label class="form-label">After Image</label><input class="form-control" type="text" name="projects_form[{{ $index }}][after_image]" value="{{ $project['after_image'] ?? '' }}"></div>
                                        <div class="col-md-3"><label class="form-label">Draft Share Link</label><input class="form-control" type="text" name="projects_form[{{ $index }}][shareable_draft_link]" value="{{ $project['shareable_draft_link'] ?? '' }}"></div>
                                        <div class="col-md-2"><label class="form-label">Press Kit URL</label><input class="form-control" type="text" name="projects_form[{{ $index }}][press_kit_pdf]" value="{{ $project['press_kit_pdf'] ?? '' }}"></div>
                                        <div class="col-md-2"><label class="form-label">Upload Press Kit</label><input class="form-control" type="file" name="projects_form[{{ $index }}][press_kit_file]" accept=".pdf"></div>
                                        <div class="col-md-3"><label class="form-label">Password (optional)</label><input class="form-control" type="text" name="projects_form[{{ $index }}][password]" value="{{ $project['password'] ?? '' }}"></div>
                                        <div class="col-md-3 d-flex gap-3 align-items-end">
                                            <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="projects_form[{{ $index }}][is_draft]" value="1" @checked($project['is_draft'] ?? false)><label class="form-check-label">Draft</label></div>
                                            <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="projects_form[{{ $index }}][password_protected]" value="1" @checked($project['password_protected'] ?? false)><label class="form-check-label">Protected</label></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <template id="projectCardTemplate">
                            <div class="project-card js-project-card" draggable="true">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary project-drag-handle">↕</button>
                                        <h6 class="mb-0">Project #<span class="js-project-order">__ORDER__</span></h6>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary js-move-up">↑</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary js-move-down">↓</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger js-remove-project">Hapus</button>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4"><label class="form-label">Project Title</label><input class="form-control" type="text" name="projects_form[__INDEX__][title]" value=""></div>
                                    <div class="col-md-3"><label class="form-label">Slug</label><input class="form-control" type="text" name="projects_form[__INDEX__][slug]" value=""></div>
                                    <div class="col-md-3"><label class="form-label">Client Name</label><input class="form-control" type="text" name="projects_form[__INDEX__][client_name]" value=""></div>
                                    <div class="col-md-2"><label class="form-label">Year</label><input class="form-control" type="number" min="2000" max="2100" name="projects_form[__INDEX__][year]" value=""></div>
                                    <div class="col-md-3"><label class="form-label">Category</label><select class="form-select" name="projects_form[__INDEX__][category]"><option selected>Commercial</option><option>Film</option><option>Doc</option><option>Event</option><option>Content</option></select></div>
                                    <div class="col-md-5"><label class="form-label">Featured Image URL</label><input class="form-control" type="text" name="projects_form[__INDEX__][featured_image]" value=""></div>
                                    <div class="col-md-3"><label class="form-label">Upload Featured Image</label><input class="form-control" type="file" name="projects_form[__INDEX__][featured_image_file]" accept=".jpg,.jpeg,.png,.webp"></div>
                                    <div class="col-md-4"><label class="form-label">Video External Link</label><input class="form-control" type="text" name="projects_form[__INDEX__][video_embed]" value=""></div>
                                    <div class="col-md-4"><label class="form-label">Upload Video File (auto convert WebM)</label><input class="form-control" type="file" name="projects_form[__INDEX__][video_file]" accept="video/*"></div>
                                    <div class="col-md-3"><label class="form-label">Duration</label><input class="form-control" type="text" name="projects_form[__INDEX__][duration]" value=""></div>
                                    <div class="col-md-3"><label class="form-label">Popular Score</label><input class="form-control" type="number" min="0" max="9999" name="projects_form[__INDEX__][popular_score]" value="0"></div>
                                    <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" rows="2" name="projects_form[__INDEX__][description]"></textarea></div>
                                    <div class="col-md-6"><label class="form-label">Services Used (comma separated)</label><input class="form-control" type="text" name="projects_form[__INDEX__][services_used_text]" value=""></div>
                                    <div class="col-md-4"><label class="form-label">Gallery URLs (1 URL per line)</label><textarea class="form-control font-monospace" rows="2" name="projects_form[__INDEX__][gallery_images_text]"></textarea></div>
                                    <div class="col-md-2"><label class="form-label">Upload Gallery</label><input class="form-control" type="file" name="projects_form[__INDEX__][gallery_files][]" accept=".jpg,.jpeg,.png,.webp" multiple></div>
                                    <div class="col-md-6"><label class="form-label">Team Credits (Role:Name per line)</label><textarea class="form-control font-monospace" rows="2" name="projects_form[__INDEX__][team_credits_text]"></textarea></div>
                                    <div class="col-md-6"><label class="form-label">Awards (1 item per line)</label><textarea class="form-control font-monospace" rows="2" name="projects_form[__INDEX__][awards_text]"></textarea></div>
                                    <div class="col-12"><label class="form-label">Client Testimonial</label><textarea class="form-control" rows="2" name="projects_form[__INDEX__][testimonial]"></textarea></div>
                                    <div class="col-md-4"><label class="form-label">Case Study Challenge</label><textarea class="form-control" rows="2" name="projects_form[__INDEX__][challenge]"></textarea></div>
                                    <div class="col-md-4"><label class="form-label">Case Study Solution</label><textarea class="form-control" rows="2" name="projects_form[__INDEX__][solution]"></textarea></div>
                                    <div class="col-md-4"><label class="form-label">Case Study Result</label><textarea class="form-control" rows="2" name="projects_form[__INDEX__][result]"></textarea></div>
                                    <div class="col-md-3"><label class="form-label">Before Image</label><input class="form-control" type="text" name="projects_form[__INDEX__][before_image]" value=""></div>
                                    <div class="col-md-3"><label class="form-label">After Image</label><input class="form-control" type="text" name="projects_form[__INDEX__][after_image]" value=""></div>
                                    <div class="col-md-3"><label class="form-label">Draft Share Link</label><input class="form-control" type="text" name="projects_form[__INDEX__][shareable_draft_link]" value=""></div>
                                    <div class="col-md-2"><label class="form-label">Press Kit URL</label><input class="form-control" type="text" name="projects_form[__INDEX__][press_kit_pdf]" value=""></div>
                                    <div class="col-md-2"><label class="form-label">Upload Press Kit</label><input class="form-control" type="file" name="projects_form[__INDEX__][press_kit_file]" accept=".pdf"></div>
                                    <div class="col-md-3"><label class="form-label">Password (optional)</label><input class="form-control" type="text" name="projects_form[__INDEX__][password]" value=""></div>
                                    <div class="col-md-3 d-flex gap-3 align-items-end">
                                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="projects_form[__INDEX__][is_draft]" value="1"><label class="form-check-label">Draft</label></div>
                                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="projects_form[__INDEX__][password_protected]" value="1"><label class="form-check-label">Protected</label></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <input type="hidden" name="projects_json" id="projects_json_hidden" value="{{ old('projects_json', $form['projects_json']) }}">
                        <details class="mt-3">
                            <summary class="small text-muted">Advanced: JSON manual (opsional)</summary>
                            <textarea class="form-control font-monospace mt-2" rows="8" id="projects_json_manual">{{ old('projects_json', $form['projects_json']) }}</textarea>
                        </details>
                        <small class="text-muted d-block mt-1">Form visual di atas akan otomatis menghasilkan JSON projects saat simpan. JSON manual tetap tersedia untuk mode advanced.</small>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Portfolio Categories</h6>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Categories JSON</label>
                        <textarea class="form-control font-monospace" rows="8" name="categories_json" placeholder='[{"slug":"commercial","name":"Commercial","thumbnail":"","description":"","seo_title":"","seo_description":""}]'>{{ old('categories_json', $form['categories_json']) }}</textarea>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Portfolio Display</h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Grid Columns</label>
                        <select class="form-select" name="display_grid_columns">
                            @foreach ([2,3,4,6] as $col)
                                <option value="{{ $col }}" @selected((int) old('display_grid_columns', $form['display_grid_columns']) === $col)>{{ $col }} Columns</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Grid Mode</label>
                        <select class="form-select" name="display_grid_mode">
                            <option value="basic" @selected(old('display_grid_mode', $form['display_grid_mode']) === 'basic')>🔲 Basic Grid</option>
                            <option value="masonry" @selected(old('display_grid_mode', $form['display_grid_mode']) === 'masonry')>🧩 Masonry Grid</option>
                            <option value="hover" @selected(old('display_grid_mode', $form['display_grid_mode']) === 'hover')>🎬 Hover Grid</option>
                            <option value="filterable" @selected(old('display_grid_mode', $form['display_grid_mode']) === 'filterable')>🎞 Filterable Grid</option>
                            <option value="video" @selected(old('display_grid_mode', $form['display_grid_mode']) === 'video')>📺 Video Grid</option>
                            <option value="card" @selected(old('display_grid_mode', $form['display_grid_mode']) === 'card')>🪟 Card Grid</option>
                            <option value="cinematic" @selected(old('display_grid_mode', $form['display_grid_mode']) === 'cinematic')>🎨 Cinematic Grid</option>
                            <option value="responsive" @selected(old('display_grid_mode', $form['display_grid_mode']) === 'responsive')>📱 Responsive Grid</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sort Default</label>
                        <select class="form-select" name="display_sort_default">
                            <option value="newest" @selected(old('display_sort_default', $form['display_sort_default']) === 'newest')>Newest</option>
                            <option value="popular" @selected(old('display_sort_default', $form['display_sort_default']) === 'popular')>Popular</option>
                            <option value="alphabetical" @selected(old('display_sort_default', $form['display_sort_default']) === 'alphabetical')>Alphabetical</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hover Effect</label>
                        <select class="form-select" name="display_hover_effect">
                            <option value="overlay" @selected(old('display_hover_effect', $form['display_hover_effect']) === 'overlay')>Overlay</option>
                            <option value="lift" @selected(old('display_hover_effect', $form['display_hover_effect']) === 'lift')>Lift</option>
                            <option value="zoom" @selected(old('display_hover_effect', $form['display_hover_effect']) === 'zoom')>Zoom</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" role="switch" name="display_enable_ajax_filter" value="1" @checked(old('display_enable_ajax_filter', $form['display_enable_ajax_filter']))>
                            <label class="form-check-label">Enable AJAX Category Filter</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Load Strategy</label>
                        <select class="form-select" name="display_load_strategy">
                            <option value="none" @selected(old('display_load_strategy', $form['display_load_strategy']) === 'none')>None</option>
                            <option value="load_more" @selected(old('display_load_strategy', $form['display_load_strategy']) === 'load_more')>⭐ Load More</option>
                            <option value="infinite" @selected(old('display_load_strategy', $form['display_load_strategy']) === 'infinite')>⭐ Infinite Scroll</option>
                            <option value="pagination" @selected(old('display_load_strategy', $form['display_load_strategy']) === 'pagination')>⭐ Pagination</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Items Per Page</label>
                        <input class="form-control" type="number" min="1" max="30" name="display_items_per_page" value="{{ old('display_items_per_page', $form['display_items_per_page']) }}">
                    </div>
                    <div class="col-12">
                        <div class="portfolio-preview-shell device-desktop" id="portfolioPreviewShell">
                            <div class="portfolio-preview-topbar">
                                <strong>Live Preview Mode (sebelum publish)</strong>
                                <div class="preview-device-toggle" id="previewDeviceToggle">
                                    <button type="button" class="preview-device-btn active" data-device="desktop">Desktop</button>
                                    <button type="button" class="preview-device-btn" data-device="tablet">Tablet</button>
                                    <button type="button" class="preview-device-btn" data-device="mobile">Mobile</button>
                                </div>
                                <div class="text-muted small" id="portfolioPreviewLabel">-</div>
                            </div>
                            <div class="portfolio-preview-frame">
                                <div class="portfolio-preview-grid" id="portfolioPreviewGrid"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Single Project Page</h6>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="single_enable_video_fullscreen" value="1" @checked(old('single_enable_video_fullscreen', $form['single_enable_video_fullscreen']))>
                            <label class="form-check-label">Full-screen video player</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="single_enable_related" value="1" @checked(old('single_enable_related', $form['single_enable_related']))>
                            <label class="form-check-label">Related projects</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="single_enable_share" value="1" @checked(old('single_enable_share', $form['single_enable_share']))>
                            <label class="form-check-label">Share buttons</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="single_enable_download_press_kit" value="1" @checked(old('single_enable_download_press_kit', $form['single_enable_download_press_kit']))>
                            <label class="form-check-label">Download press kit</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="single_enable_prev_next" value="1" @checked(old('single_enable_prev_next', $form['single_enable_prev_next']))>
                            <label class="form-check-label">Next/Previous nav</label>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Advanced Features</h6>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="advanced_password_protected" value="1" @checked(old('advanced_password_protected', $form['advanced_password_protected']))>
                            <label class="form-check-label">Password-protected portfolio</label>
                        </div>
                        <input class="form-control mt-2" type="text" name="advanced_portfolio_password" value="{{ old('advanced_portfolio_password', $form['advanced_portfolio_password']) }}" placeholder="portfolio password">
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="advanced_enable_draft_share" value="1" @checked(old('advanced_enable_draft_share', $form['advanced_enable_draft_share']))>
                            <label class="form-check-label">Draft mode shareable link</label>
                        </div>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="advanced_enable_lightbox" value="1" @checked(old('advanced_enable_lightbox', $form['advanced_enable_lightbox']))>
                            <label class="form-check-label">Video lightbox</label>
                        </div>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="advanced_enable_before_after" value="1" @checked(old('advanced_enable_before_after', $form['advanced_enable_before_after']))>
                            <label class="form-check-label">Before/After slider</label>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Layout, Susunan, Konsep Portfolio</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Judul Section Portfolio di Landing Page</label>
                        <input class="form-control" type="text" name="portfolio_section_title" value="{{ old('portfolio_section_title', $form['portfolio_section_title']) }}" placeholder="INFINITE GALERY">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Layout Sections JSON</label>
                        <textarea class="form-control font-monospace" rows="6" name="layout_sections_json">{{ old('layout_sections_json', $form['layout_sections_json']) }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Concept Notes</label>
                        <textarea class="form-control" rows="6" name="layout_concept_notes" placeholder="Ide layout, flow section, styling direction, visual hierarchy, dsb.">{{ old('layout_concept_notes', $form['layout_concept_notes']) }}</textarea>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Workflow Integration (Dashboard → Recent Projects)</h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Recent Projects Limit</label>
                        <input class="form-control" type="number" min="1" max="20" name="recent_projects_limit" value="{{ old('recent_projects_limit', $form['recent_projects_limit']) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Recent Projects Mode</label>
                        <select class="form-select" name="recent_projects_mode">
                            <option value="newest" @selected(old('recent_projects_mode', $form['recent_projects_mode']) === 'newest')>Newest</option>
                            <option value="popular" @selected(old('recent_projects_mode', $form['recent_projects_mode']) === 'popular')>Popular</option>
                            <option value="alphabetical" @selected(old('recent_projects_mode', $form['recent_projects_mode']) === 'alphabetical')>Alphabetical</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Simpan Portfolio CMS</button>
                        <div class="upload-progress-wrap" id="portfolioUploadProgressWrap">
                            <div class="progress mt-2" role="progressbar" aria-label="Upload progress" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" id="portfolioUploadProgressBar" style="width: 0%">0%</div>
                            </div>
                            <small class="text-muted" id="portfolioUploadProgressText">Menyiapkan upload...</small>
                            <small class="text-muted d-block" id="portfolioUploadSummaryText"></small>
                        </div>
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
                                            <form method="POST" action="{{ route('dashboard-website-cms-portfolio.update') }}">
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

@section('page-script')
<script>
    (() => {
        const form = document.querySelector('form[action="{{ route("dashboard-website-cms-portfolio.update") }}"]');
        const repeater = document.getElementById('projectRepeater');
        const addButton = document.getElementById('btnAddProject');
        const template = document.getElementById('projectCardTemplate');
        const hiddenJson = document.getElementById('projects_json_hidden');
        const manualJson = document.getElementById('projects_json_manual');
        const displayModeInput = document.querySelector('select[name="display_grid_mode"]');
        const displayColumnsInput = document.querySelector('select[name="display_grid_columns"]');
        const hoverEffectInput = document.querySelector('select[name="display_hover_effect"]');
        const previewShell = document.getElementById('portfolioPreviewShell');
        const previewGrid = document.getElementById('portfolioPreviewGrid');
        const previewLabel = document.getElementById('portfolioPreviewLabel');
        const previewDeviceToggle = document.getElementById('previewDeviceToggle');
        const uploadProgressWrap = document.getElementById('portfolioUploadProgressWrap');
        const uploadProgressBar = document.getElementById('portfolioUploadProgressBar');
        const uploadProgressText = document.getElementById('portfolioUploadProgressText');
        const uploadSummaryText = document.getElementById('portfolioUploadSummaryText');
        let draggingCard = null;
        let previewDevice = 'desktop';

        if (!(form instanceof HTMLFormElement) || !(repeater instanceof HTMLElement) || !(template instanceof HTMLTemplateElement) || !(hiddenJson instanceof HTMLInputElement)) {
            return;
        }

        const reindex = () => {
            const cards = Array.from(repeater.querySelectorAll('.js-project-card'));
            cards.forEach((card, index) => {
                const orderLabel = card.querySelector('.js-project-order');
                if (orderLabel instanceof HTMLElement) {
                    orderLabel.textContent = `${index + 1}`;
                }
                card.querySelectorAll('[name]').forEach((field) => {
                    const current = field.getAttribute('name') || '';
                    field.setAttribute('name', current.replace(/projects_form\[\d+\]/, `projects_form[${index}]`));
                });
            });
        };

        const cardTemplate = (index) => template.innerHTML
            .replaceAll('__INDEX__', `${index}`)
            .replaceAll('__ORDER__', `${index + 1}`);

        const parseLines = (value) => `${value || ''}`
            .split(/\r\n|\r|\n/)
            .map((item) => item.trim())
            .filter((item) => item !== '');

        const parseComma = (value) => `${value || ''}`
            .split(',')
            .map((item) => item.trim())
            .filter((item) => item !== '');

        const collectProjects = () => {
            const cards = Array.from(repeater.querySelectorAll('.js-project-card'));
            return cards.map((card) => {
                const get = (suffix) => {
                    const field = card.querySelector(`[name$="${suffix}"]`);
                    if (field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement || field instanceof HTMLSelectElement) {
                        return `${field.value || ''}`.trim();
                    }
                    return '';
                };
                const getBool = (suffix) => {
                    const field = card.querySelector(`[name$="${suffix}"]`);
                    return field instanceof HTMLInputElement ? field.checked : false;
                };
                const teamCredits = parseLines(get('[team_credits_text]')).map((line) => {
                    const [role, name] = line.split(':', 2);
                    return { role: `${role || ''}`.trim(), name: `${name || ''}`.trim() };
                }).filter((item) => item.role !== '' || item.name !== '');

                const title = get('[title]');
                const slugInput = get('[slug]');
                const slug = slugInput !== '' ? slugInput : title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');

                return {
                    slug,
                    title,
                    client_name: get('[client_name]'),
                    category: get('[category]') || 'Commercial',
                    year: Number(get('[year]') || new Date().getFullYear()),
                    featured_image: get('[featured_image]'),
                    gallery_images: parseLines(get('[gallery_images_text]')),
                    video_embed: get('[video_embed]'),
                    description: get('[description]'),
                    services_used: parseComma(get('[services_used_text]')),
                    team_credits: teamCredits,
                    duration: get('[duration]'),
                    awards: parseLines(get('[awards_text]')),
                    client_testimonial: get('[testimonial]'),
                    case_study: {
                        challenge: get('[challenge]'),
                        solution: get('[solution]'),
                        result: get('[result]'),
                    },
                    popular_score: Number(get('[popular_score]') || 0),
                    is_draft: getBool('[is_draft]'),
                    shareable_draft_link: get('[shareable_draft_link]'),
                    password_protected: getBool('[password_protected]'),
                    password: get('[password]'),
                    press_kit_pdf: get('[press_kit_pdf]'),
                    before_after: {
                        before: get('[before_image]'),
                        after: get('[after_image]'),
                    },
                };
            }).filter((project) => project.title !== '');
        };

        const syncHiddenJson = () => {
            const projects = collectProjects();
            const json = JSON.stringify(projects);
            hiddenJson.value = json;
            if (manualJson instanceof HTMLTextAreaElement) {
                manualJson.value = JSON.stringify(projects, null, 2);
            }
            renderPortfolioPreview();
        };

        const previewModeLabel = (mode) => ({
            basic: 'Basic Grid',
            masonry: 'Masonry Grid',
            hover: 'Hover Grid',
            filterable: 'Filterable Grid',
            video: 'Video Grid',
            card: 'Card Grid',
            cinematic: 'Cinematic Grid',
            responsive: 'Responsive Grid',
            uniform: 'Basic Grid',
        }[mode] || mode);

        const renderPortfolioPreview = () => {
            if (!(previewGrid instanceof HTMLElement)) {
                return;
            }
            const projects = collectProjects().slice(0, 8);
            const mode = displayModeInput instanceof HTMLSelectElement ? displayModeInput.value : 'cinematic';
            const columns = displayColumnsInput instanceof HTMLSelectElement ? displayColumnsInput.value : '4';
            const hover = hoverEffectInput instanceof HTMLSelectElement ? hoverEffectInput.value : 'overlay';
            previewGrid.className = `portfolio-preview-grid mode-${mode} cols-${columns} hover-${hover}`;
            if (previewShell instanceof HTMLElement) {
                previewShell.classList.remove('device-desktop', 'device-tablet', 'device-mobile');
                previewShell.classList.add(`device-${previewDevice}`);
            }
            if (previewLabel instanceof HTMLElement) {
                previewLabel.textContent = `${previewModeLabel(mode)} · ${columns} Col · Hover ${hover} · ${previewDevice}`;
            }
            if (projects.length === 0) {
                previewGrid.innerHTML = '<div class="preview-empty">Belum ada project untuk dipreview.</div>';
                return;
            }
            previewGrid.innerHTML = projects.map((project, index) => {
                const thumb = project.featured_image || project.video_embed || '';
                const media = project.video_embed !== '' ? 'video' : 'photo';
                const largeClass = (mode === 'cinematic' || mode === 'masonry') && index === 0 ? 'bento-large' : '';
                return `
                    <article class="preview-item ${largeClass}" data-media="${media}">
                        <img class="preview-thumb" src="${thumb}" alt="${project.title || 'Project'}">
                        <div class="preview-body">
                            <div>${project.title || '-'}</div>
                            <div>${project.category || 'General'} · ${project.year || '-'}</div>
                        </div>
                    </article>
                `;
            }).join('');
        };

        addButton?.addEventListener('click', () => {
            const nextIndex = repeater.querySelectorAll('.js-project-card').length;
            repeater.insertAdjacentHTML('beforeend', cardTemplate(nextIndex));
            reindex();
            syncHiddenJson();
            const newTitle = repeater.querySelector('.js-project-card:last-child input[name$="[title]"]');
            if (newTitle instanceof HTMLInputElement) {
                newTitle.focus();
            }
        });

        repeater.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement) || !target.classList.contains('js-remove-project')) {
                return;
            }
            const card = target.closest('.js-project-card');
            if (!(card instanceof HTMLElement)) {
                return;
            }
            card.remove();
            reindex();
            syncHiddenJson();
        });
        repeater.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement) || (!target.classList.contains('js-move-up') && !target.classList.contains('js-move-down'))) {
                return;
            }
            const card = target.closest('.js-project-card');
            if (!(card instanceof HTMLElement) || !(card.parentElement instanceof HTMLElement)) {
                return;
            }
            if (target.classList.contains('js-move-up')) {
                const prevCard = card.previousElementSibling;
                if (prevCard instanceof HTMLElement) {
                    card.parentElement.insertBefore(card, prevCard);
                }
            } else {
                const nextCard = card.nextElementSibling;
                if (nextCard instanceof HTMLElement) {
                    card.parentElement.insertBefore(nextCard, card);
                }
            }
            reindex();
            syncHiddenJson();
        });

        repeater.addEventListener('dragstart', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement) || !target.classList.contains('js-project-card')) {
                return;
            }
            draggingCard = target;
            target.classList.add('dragging');
            if (event.dataTransfer) {
                event.dataTransfer.effectAllowed = 'move';
            }
        });
        repeater.addEventListener('dragend', () => {
            if (draggingCard instanceof HTMLElement) {
                draggingCard.classList.remove('dragging');
            }
            repeater.querySelectorAll('.js-project-card').forEach((card) => card.classList.remove('drop-target'));
            draggingCard = null;
        });
        repeater.addEventListener('dragover', (event) => {
            event.preventDefault();
            const target = event.target;
            if (!(target instanceof HTMLElement) || !(draggingCard instanceof HTMLElement)) {
                return;
            }
            const dropCard = target.closest('.js-project-card');
            repeater.querySelectorAll('.js-project-card').forEach((card) => card.classList.remove('drop-target'));
            if (!(dropCard instanceof HTMLElement) || dropCard === draggingCard) {
                return;
            }
            dropCard.classList.add('drop-target');
        });
        repeater.addEventListener('drop', (event) => {
            event.preventDefault();
            const target = event.target;
            if (!(target instanceof HTMLElement) || !(draggingCard instanceof HTMLElement)) {
                return;
            }
            const dropCard = target.closest('.js-project-card');
            if (!(dropCard instanceof HTMLElement) || dropCard === draggingCard) {
                return;
            }
            const cards = Array.from(repeater.querySelectorAll('.js-project-card'));
            const dragIndex = cards.indexOf(draggingCard);
            const dropIndex = cards.indexOf(dropCard);
            if (dragIndex < 0 || dropIndex < 0) {
                return;
            }
            if (dragIndex < dropIndex) {
                repeater.insertBefore(draggingCard, dropCard.nextSibling);
            } else {
                repeater.insertBefore(draggingCard, dropCard);
            }
            repeater.querySelectorAll('.js-project-card').forEach((card) => card.classList.remove('drop-target'));
            reindex();
            syncHiddenJson();
        });

        repeater.addEventListener('input', syncHiddenJson);
        repeater.addEventListener('change', syncHiddenJson);
        manualJson?.addEventListener('input', () => {
            hiddenJson.value = manualJson.value;
        });
        previewDeviceToggle?.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLButtonElement)) {
                return;
            }
            const device = target.dataset.device;
            if (!device || !['desktop', 'tablet', 'mobile'].includes(device)) {
                return;
            }
            previewDevice = device;
            previewDeviceToggle.querySelectorAll('.preview-device-btn').forEach((btn) => btn.classList.toggle('active', btn === target));
            renderPortfolioPreview();
        });
        displayModeInput?.addEventListener('change', renderPortfolioPreview);
        displayColumnsInput?.addEventListener('change', renderPortfolioPreview);
        hoverEffectInput?.addEventListener('change', renderPortfolioPreview);
        form.addEventListener('submit', () => {
            if (manualJson instanceof HTMLTextAreaElement && manualJson.value.trim() !== '' && manualJson.value.trim() !== hiddenJson.value.trim()) {
                hiddenJson.value = manualJson.value;
                return;
            }
            syncHiddenJson();
        });

        let uploading = false;
        const maxPostBytes = 640 * 1024 * 1024;
        const toMbText = (bytes) => `${(bytes / (1024 * 1024)).toFixed(1)}MB`;
        const toSizeText = (bytes) => {
            if (bytes < 1024) {
                return `${bytes}B`;
            }
            if (bytes < 1024 * 1024) {
                return `${(bytes / 1024).toFixed(1)}KB`;
            }
            if (bytes < 1024 * 1024 * 1024) {
                return `${(bytes / (1024 * 1024)).toFixed(1)}MB`;
            }
            return `${(bytes / (1024 * 1024 * 1024)).toFixed(2)}GB`;
        };
        const toEtaText = (seconds) => {
            if (!Number.isFinite(seconds) || seconds < 0) {
                return '-';
            }
            const totalSeconds = Math.round(seconds);
            const mins = Math.floor(totalSeconds / 60);
            const secs = totalSeconds % 60;
            return mins > 0 ? `${mins}m ${secs}s` : `${secs}s`;
        };
        form.addEventListener('submit', (event) => {
            if (uploading) {
                event.preventDefault();
                return;
            }
            event.preventDefault();
            const totalUploadBytes = Array.from(form.querySelectorAll('input[type="file"]')).reduce((carry, input) => {
                if (!(input instanceof HTMLInputElement) || !input.files) {
                    return carry;
                }
                return carry + Array.from(input.files).reduce((sum, file) => sum + file.size, 0);
            }, 0);
            if (totalUploadBytes > maxPostBytes) {
                if (uploadProgressWrap instanceof HTMLElement) {
                    uploadProgressWrap.classList.add('active');
                }
                if (uploadProgressBar instanceof HTMLElement) {
                    uploadProgressBar.style.width = '100%';
                    uploadProgressBar.textContent = 'Batas Terlampaui';
                }
                if (uploadProgressText instanceof HTMLElement) {
                    uploadProgressText.textContent = `Total upload ${toMbText(totalUploadBytes)} melebihi batas request ${toMbText(maxPostBytes)}. Kurangi jumlah file atau ukuran video.`;
                }
                if (uploadSummaryText instanceof HTMLElement) {
                    uploadSummaryText.textContent = '';
                }
                return;
            }
            const selectedFiles = Array.from(form.querySelectorAll('input[type="file"]'))
                .flatMap((input) => input instanceof HTMLInputElement && input.files ? Array.from(input.files) : []);
            const fileCount = selectedFiles.length;
            const fileNames = selectedFiles.slice(0, 3).map((file) => file.name);
            uploading = true;
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton instanceof HTMLButtonElement) {
                submitButton.disabled = true;
            }
            if (uploadProgressWrap instanceof HTMLElement) {
                uploadProgressWrap.classList.add('active');
            }
            if (uploadProgressBar instanceof HTMLElement) {
                uploadProgressBar.style.width = '0%';
                uploadProgressBar.textContent = '0%';
            }
            if (uploadProgressText instanceof HTMLElement) {
                uploadProgressText.textContent = 'Mengupload file media...';
            }
            if (uploadSummaryText instanceof HTMLElement) {
                const namesText = fileNames.length > 0 ? ` (${fileNames.join(', ')}${fileCount > fileNames.length ? ', ...' : ''})` : '';
                uploadSummaryText.textContent = `File: ${fileCount} · Total: ${toSizeText(totalUploadBytes)}${namesText}`;
            }

            const formActionUrl = form.getAttribute('action') || window.location.href;
            const xhr = new XMLHttpRequest();
            const uploadStartedAt = Date.now();
            xhr.open((form.method || 'POST').toUpperCase(), formActionUrl, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.upload.addEventListener('progress', (progressEvent) => {
                if (!progressEvent.lengthComputable) {
                    return;
                }
                const percent = Math.min(100, Math.round((progressEvent.loaded / progressEvent.total) * 100));
                if (uploadProgressBar instanceof HTMLElement) {
                    uploadProgressBar.style.width = `${percent}%`;
                    uploadProgressBar.textContent = `${percent}%`;
                }
                if (uploadProgressText instanceof HTMLElement) {
                    const elapsed = Math.max((Date.now() - uploadStartedAt) / 1000, 0.1);
                    const speed = progressEvent.loaded / elapsed;
                    const remainingBytes = Math.max(progressEvent.total - progressEvent.loaded, 0);
                    const eta = speed > 0 ? remainingBytes / speed : 0;
                    uploadProgressText.textContent = percent < 100
                        ? `Mengupload file media... ETA ${toEtaText(eta)}`
                        : 'Upload selesai, memproses...';
                }
                if (uploadSummaryText instanceof HTMLElement) {
                    const elapsed = Math.max((Date.now() - uploadStartedAt) / 1000, 0.1);
                    const speed = progressEvent.loaded / elapsed;
                    uploadSummaryText.textContent = `Terkirim ${toSizeText(progressEvent.loaded)} / ${toSizeText(progressEvent.total)} · Kecepatan ${toSizeText(Math.round(speed))}/s`;
                }
            });
            xhr.onload = () => {
                if (uploadProgressBar instanceof HTMLElement) {
                    uploadProgressBar.style.width = '100%';
                    uploadProgressBar.textContent = '100%';
                }
                if (xhr.status >= 200 && xhr.status < 400) {
                    window.location.href = xhr.responseURL || formActionUrl;
                    return;
                }
                uploading = false;
                if (submitButton instanceof HTMLButtonElement) {
                    submitButton.disabled = false;
                }
                if (uploadProgressText instanceof HTMLElement) {
                    uploadProgressText.textContent = 'Upload gagal. Silakan periksa input lalu coba lagi.';
                }
                if (xhr.responseText) {
                    document.open();
                    document.write(xhr.responseText);
                    document.close();
                }
            };
            xhr.onerror = () => {
                uploading = false;
                if (submitButton instanceof HTMLButtonElement) {
                    submitButton.disabled = false;
                }
                if (uploadProgressText instanceof HTMLElement) {
                    uploadProgressText.textContent = 'Terjadi kendala jaringan saat upload.';
                }
            };
            xhr.send(new FormData(form));
        });

        reindex();
        syncHiddenJson();
        renderPortfolioPreview();
    })();
</script>
@endsection
