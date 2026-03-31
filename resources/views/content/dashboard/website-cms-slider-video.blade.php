@extends('layouts/contentNavbarLayout')

@section('title', 'Website CMS - Slider Video')

@section('content')
<div class="row g-6">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-primary mb-1">WEBSITE CMS · Slider Video</h4>
                <p class="mb-0">SR7 Engine mode untuk hero video slideshow dengan workflow Design, Animation, dan Action.</p>
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

                <form method="POST" action="{{ route('dashboard-website-cms-slider-video.update') }}" enctype="multipart/form-data" id="sliderVideoForm" class="row g-4">
                    @csrf
                    <style>
                        .slider-preview-wrap {
                            border: 1px solid rgba(128, 128, 128, .24);
                            border-radius: .85rem;
                            padding: 1rem;
                            background: var(--bs-body-bg);
                        }
                        .slider-preview-stage {
                            position: relative;
                            min-height: 280px;
                            border-radius: .75rem;
                            overflow: hidden;
                            background: #090909;
                            border: 1px solid rgba(255, 255, 255, .08);
                        }
                        .slider-preview-stage.preview-device-desktop {
                            width: 100%;
                            aspect-ratio: 16 / 9;
                            margin: 0 auto;
                        }
                        .slider-preview-stage.preview-device-tablet {
                            width: min(820px, 100%);
                            aspect-ratio: 16 / 10;
                            margin: 0 auto;
                        }
                        .slider-preview-stage.preview-device-mobile {
                            width: min(390px, 100%);
                            aspect-ratio: 9 / 16;
                            margin: 0 auto;
                        }
                        .slider-preview-media,
                        .slider-preview-fallback {
                            position: absolute;
                            inset: 0;
                            width: 100%;
                            height: 100%;
                            object-fit: cover;
                            opacity: 0;
                            transform: translate3d(0, 0, 0) scale(1.02);
                            transition: opacity .55s ease, transform .55s ease;
                        }
                        .slider-preview-media.active,
                        .slider-preview-fallback.active {
                            opacity: 1;
                            transform: translate3d(0, 0, 0) scale(1);
                        }
                        .slider-preview-stage.effect-slide .slider-preview-media,
                        .slider-preview-stage.effect-slide .slider-preview-fallback {
                            transform: translate3d(7%, 0, 0);
                        }
                        .slider-preview-stage.effect-slide .slider-preview-media.active,
                        .slider-preview-stage.effect-slide .slider-preview-fallback.active {
                            transform: translate3d(0, 0, 0);
                        }
                        .slider-preview-stage.effect-cube .slider-preview-media,
                        .slider-preview-stage.effect-cube .slider-preview-fallback {
                            transform: rotateY(14deg) scale(.96);
                            transform-origin: center right;
                        }
                        .slider-preview-stage.effect-cube .slider-preview-media.active,
                        .slider-preview-stage.effect-cube .slider-preview-fallback.active {
                            transform: rotateY(0deg) scale(1);
                        }
                        .slider-preview-overlay {
                            position: absolute;
                            inset: 0;
                            pointer-events: none;
                            background: linear-gradient(180deg, rgba(0, 0, 0, .3), rgba(0, 0, 0, .78));
                        }
                        .slider-preview-content {
                            position: absolute;
                            inset: 0;
                            z-index: 3;
                            color: #fff;
                            display: grid;
                            align-content: center;
                            justify-items: center;
                            text-align: center;
                            padding: 1.4rem;
                            gap: .35rem;
                        }
                        .slider-preview-content.pos-left {
                            justify-items: start;
                            text-align: left;
                        }
                        .slider-preview-content.pos-right {
                            justify-items: end;
                            text-align: right;
                        }
                        .slider-preview-content h5 {
                            margin: 0;
                            font-weight: 700;
                            font-size: clamp(1.05rem, 2vw, 1.55rem);
                            max-width: min(92%, 720px);
                        }
                        .slider-preview-content p {
                            margin: 0;
                            color: rgba(255, 255, 255, .86);
                            max-width: min(92%, 780px);
                        }
                        .slider-preview-content a {
                            margin-top: .5rem;
                            display: inline-flex;
                            padding: .45rem .95rem;
                            border-radius: 999px;
                            background: linear-gradient(135deg, #f3c469, #d89a2b);
                            text-decoration: none;
                            color: #1d1204;
                            font-weight: 600;
                            font-size: .85rem;
                        }
                        .slider-preview-controls {
                            margin-top: .85rem;
                            display: flex;
                            align-items: center;
                            gap: .6rem;
                            flex-wrap: wrap;
                        }
                        .slider-preview-controls .btn {
                            min-width: 98px;
                        }
                        .slider-preview-modes {
                            display: inline-flex;
                            gap: .35rem;
                            align-items: center;
                        }
                        .slider-preview-modes .btn {
                            min-width: 84px;
                        }
                        .slider-preview-progress {
                            height: 6px;
                            border-radius: 999px;
                            background: rgba(128, 128, 128, .26);
                            overflow: hidden;
                            width: min(320px, 100%);
                            margin-left: auto;
                        }
                        .slider-preview-progress > span {
                            display: block;
                            height: 100%;
                            width: 0%;
                            background: linear-gradient(90deg, #f3c469, #d89a2b);
                            transition: width .15s linear;
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
                        <div class="nav nav-pills gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary js-mode-btn" data-mode="design">Design Mode</button>
                            <button type="button" class="btn btn-sm btn-outline-primary js-mode-btn" data-mode="animation">Animation Mode</button>
                            <button type="button" class="btn btn-sm btn-outline-primary js-mode-btn" data-mode="action">Action Mode</button>
                        </div>
                        <input type="hidden" name="editing_mode" id="editing_mode" value="{{ old('editing_mode', $form['editing_mode']) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Engine</label>
                        <select class="form-select" name="engine_mode">
                            <option value="sr6" @selected(old('engine_mode', $form['engine_mode']) === 'sr6')>SR6</option>
                            <option value="sr7" @selected(old('engine_mode', $form['engine_mode']) === 'sr7')>SR7</option>
                            <option value="xslider" @selected(old('engine_mode', $form['engine_mode']) === 'xslider')>XSLIDER Compatible</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Transition Effect</label>
                        <select class="form-select" name="transition_effect">
                            <option value="fade" @selected(old('transition_effect', $form['transition_effect']) === 'fade')>Fade</option>
                            <option value="slide" @selected(old('transition_effect', $form['transition_effect']) === 'slide')>Slide</option>
                            <option value="cube" @selected(old('transition_effect', $form['transition_effect']) === 'cube')>Cube</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex flex-wrap align-items-end gap-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="autoplay" value="1" @checked(old('autoplay', $form['autoplay']))>
                            <label class="form-check-label">Autoplay</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="loop" value="1" @checked(old('loop', $form['loop']))>
                            <label class="form-check-label">Loop</label>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex flex-wrap align-items-end gap-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="lazy_load" value="1" @checked(old('lazy_load', $form['lazy_load']))>
                            <label class="form-check-label">Lazy Loading</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="preload_critical" value="1" @checked(old('preload_critical', $form['preload_critical']))>
                            <label class="form-check-label">Preload Critical Slide</label>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="mobile_disable_video" value="1" @checked(old('mobile_disable_video', $form['mobile_disable_video']))>
                            <label class="form-check-label">Mobile Fallback Image (Video Disabled)</label>
                        </div>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" id="btnAddSlide">Tambah Slide</button>
                        <button type="button" class="btn btn-outline-secondary" id="btnReindexSlides">Reindex Order</button>
                    </div>
                    <div class="col-12">
                        <div class="slider-preview-wrap">
                            <div id="sliderPreviewStage" class="slider-preview-stage preview-device-desktop effect-{{ old('transition_effect', $form['transition_effect']) }}">
                                <video id="sliderPreviewVideo" class="slider-preview-media" playsinline muted></video>
                                <iframe id="sliderPreviewExternal" class="slider-preview-media" allowfullscreen></iframe>
                                <div id="sliderPreviewFallback" class="slider-preview-fallback"></div>
                                <div id="sliderPreviewOverlay" class="slider-preview-overlay"></div>
                                <div id="sliderPreviewContent" class="slider-preview-content">
                                    <h5 id="sliderPreviewTitle">Preview Slider</h5>
                                    <p id="sliderPreviewCaption">Isi slide akan tampil realtime sesuai input.</p>
                                    <a id="sliderPreviewCta" href="#" target="_blank">CTA</a>
                                </div>
                            </div>
                            <div class="slider-preview-controls">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="sliderPreviewPrev">Prev</button>
                                <button type="button" class="btn btn-sm btn-primary" id="sliderPreviewPlay">Play</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="sliderPreviewNext">Next</button>
                                <div class="slider-preview-modes">
                                    <button type="button" class="btn btn-sm btn-primary js-preview-mode-btn" data-preview-mode="desktop">Desktop</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary js-preview-mode-btn" data-preview-mode="tablet">Tablet</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary js-preview-mode-btn" data-preview-mode="mobile">Mobile</button>
                                </div>
                                <span id="sliderPreviewMeta" class="text-muted small">Slide 1/1 · 7s</span>
                                <div class="slider-preview-progress"><span id="sliderPreviewProgressBar"></span></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div id="slideList" class="d-flex flex-column gap-4">
                            @php
                                $initialSlides = old('slides', $slides);
                                if (!is_array($initialSlides) || count($initialSlides) === 0) {
                                    $initialSlides = $slides;
                                }
                            @endphp
                            @foreach ($initialSlides as $index => $slide)
                                <div class="border rounded p-3 js-slide-item" data-index="{{ $index }}" draggable="true">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">Slide #<span class="js-slide-order">{{ $index + 1 }}</span></h6>
                                        <button type="button" class="btn btn-sm btn-outline-danger js-remove-slide">Hapus</button>
                                    </div>
                                    <input type="hidden" name="slides[{{ $index }}][id]" value="{{ $slide['id'] ?? '' }}">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Nama Slider (Optional)</label>
                                            <input class="form-control" type="text" name="slides[{{ $index }}][slider_name]" value="{{ $slide['slider_name'] ?? '' }}" placeholder="Nama internal, tidak tampil di front-end">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Headline (Optional)</label>
                                            <input class="form-control" type="text" name="slides[{{ $index }}][title]" value="{{ $slide['title'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">CTA Text (Optional)</label>
                                            <input class="form-control" type="text" name="slides[{{ $index }}][cta_text]" value="{{ $slide['cta_text'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">CTA Link (Optional)</label>
                                            <input class="form-control" type="text" name="slides[{{ $index }}][cta_url]" value="{{ $slide['cta_url'] ?? '' }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Subheadline / Description (Optional)</label>
                                            <textarea class="form-control" rows="2" name="slides[{{ $index }}][caption]">{{ $slide['caption'] ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Video Source</label>
                                            <select class="form-select js-video-source" name="slides[{{ $index }}][video_source]">
                                                <option value="upload" @selected(($slide['video_source'] ?? 'upload') === 'upload')>Upload Video (Auto WebM)</option>
                                                <option value="external" @selected(($slide['video_source'] ?? 'upload') === 'external')>External URL</option>
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Video URL / Path</label>
                                            <input class="form-control" type="text" name="slides[{{ $index }}][video_url_text]" value="{{ $slide['video_url'] ?? '' }}" placeholder="https://... atau path storage">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">External Embed URL</label>
                                            <input class="form-control" type="text" name="slides[{{ $index }}][external_url]" value="{{ $slide['external_url'] ?? '' }}" placeholder="YouTube/Vimeo embed URL">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Upload Video (max 500MB · auto convert WebM)</label>
                                            <input class="form-control" type="file" name="slides[{{ $index }}][video_file]" accept="video/*">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Tablet Video (compressed · auto convert WebM)</label>
                                            <input class="form-control" type="file" name="slides[{{ $index }}][tablet_video_file]" accept="video/*">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Poster Image</label>
                                            <input class="form-control" type="file" name="slides[{{ $index }}][poster_image_file]" accept=".jpg,.jpeg,.png,.webp">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Mobile Fallback Image</label>
                                            <input class="form-control" type="file" name="slides[{{ $index }}][mobile_fallback_image_file]" accept=".jpg,.jpeg,.png,.webp">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Text Position</label>
                                            <select class="form-select" name="slides[{{ $index }}][text_position]">
                                                <option value="left" @selected(($slide['text_position'] ?? 'center') === 'left')>Left</option>
                                                <option value="center" @selected(($slide['text_position'] ?? 'center') === 'center')>Center</option>
                                                <option value="right" @selected(($slide['text_position'] ?? 'center') === 'right')>Right</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Text Animation</label>
                                            <select class="form-select" name="slides[{{ $index }}][text_animation]">
                                                <option value="fade-up" @selected(($slide['text_animation'] ?? 'fade-up') === 'fade-up')>Fade Up</option>
                                                <option value="slide-up" @selected(($slide['text_animation'] ?? 'fade-up') === 'slide-up')>Slide Up</option>
                                                <option value="zoom-in" @selected(($slide['text_animation'] ?? 'fade-up') === 'zoom-in')>Zoom In</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Overlay Color</label>
                                            <input class="form-control form-control-color w-100" type="color" name="slides[{{ $index }}][overlay_color]" value="{{ $slide['overlay_color'] ?? '#000000' }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Overlay Opacity</label>
                                            <input class="form-control" type="number" min="0" max="1" step="0.01" name="slides[{{ $index }}][overlay_opacity]" value="{{ $slide['overlay_opacity'] ?? '0.78' }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Duration (sec)</label>
                                            <input class="form-control" type="number" min="0" max="60" name="slides[{{ $index }}][duration_seconds]" value="{{ $slide['duration_seconds'] ?? 0 }}">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end gap-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" name="slides[{{ $index }}][mute_default]" value="1" @checked(($slide['mute_default'] ?? true))>
                                                <label class="form-check-label">Mute</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" name="slides[{{ $index }}][is_active]" value="1" @checked(($slide['is_active'] ?? true))>
                                                <label class="form-check-label">Active</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <template id="slideItemTemplate">
                            <div class="border rounded p-3 js-slide-item" data-index="__INDEX__" draggable="true">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Slide #<span class="js-slide-order">__ORDER__</span></h6>
                                    <button type="button" class="btn btn-sm btn-outline-danger js-remove-slide">Hapus</button>
                                </div>
                                <input type="hidden" name="slides[__INDEX__][id]" value="">
                                <div class="row g-3">
                                    <div class="col-md-3"><label class="form-label">Nama Slider (Optional)</label><input class="form-control" type="text" name="slides[__INDEX__][slider_name]" value="" placeholder="Nama internal, tidak tampil di front-end"></div>
                                    <div class="col-md-3"><label class="form-label">Headline (Optional)</label><input class="form-control" type="text" name="slides[__INDEX__][title]" value=""></div>
                                    <div class="col-md-3"><label class="form-label">CTA Text (Optional)</label><input class="form-control" type="text" name="slides[__INDEX__][cta_text]" value=""></div>
                                    <div class="col-md-3"><label class="form-label">CTA Link (Optional)</label><input class="form-control" type="text" name="slides[__INDEX__][cta_url]" value=""></div>
                                    <div class="col-12"><label class="form-label">Subheadline / Description (Optional)</label><textarea class="form-control" rows="2" name="slides[__INDEX__][caption]"></textarea></div>
                                    <div class="col-md-3"><label class="form-label">Video Source</label><select class="form-select js-video-source" name="slides[__INDEX__][video_source]"><option value="upload" selected>Upload Video (Auto WebM)</option><option value="external">External URL</option></select></div>
                                    <div class="col-md-5"><label class="form-label">Video URL / Path</label><input class="form-control" type="text" name="slides[__INDEX__][video_url_text]" value=""></div>
                                    <div class="col-md-4"><label class="form-label">External Embed URL</label><input class="form-control" type="text" name="slides[__INDEX__][external_url]" value=""></div>
                                    <div class="col-md-4"><label class="form-label">Upload Video (max 500MB · auto convert WebM)</label><input class="form-control" type="file" name="slides[__INDEX__][video_file]" accept="video/*"></div>
                                    <div class="col-md-4"><label class="form-label">Tablet Video (compressed · auto convert WebM)</label><input class="form-control" type="file" name="slides[__INDEX__][tablet_video_file]" accept="video/*"></div>
                                    <div class="col-md-4"><label class="form-label">Poster Image</label><input class="form-control" type="file" name="slides[__INDEX__][poster_image_file]" accept=".jpg,.jpeg,.png,.webp"></div>
                                    <div class="col-md-4"><label class="form-label">Mobile Fallback Image</label><input class="form-control" type="file" name="slides[__INDEX__][mobile_fallback_image_file]" accept=".jpg,.jpeg,.png,.webp"></div>
                                    <div class="col-md-2"><label class="form-label">Text Position</label><select class="form-select" name="slides[__INDEX__][text_position]"><option value="left">Left</option><option value="center" selected>Center</option><option value="right">Right</option></select></div>
                                    <div class="col-md-2"><label class="form-label">Text Animation</label><select class="form-select" name="slides[__INDEX__][text_animation]"><option value="fade-up" selected>Fade Up</option><option value="slide-up">Slide Up</option><option value="zoom-in">Zoom In</option></select></div>
                                    <div class="col-md-2"><label class="form-label">Overlay Color</label><input class="form-control form-control-color w-100" type="color" name="slides[__INDEX__][overlay_color]" value="#000000"></div>
                                    <div class="col-md-2"><label class="form-label">Overlay Opacity</label><input class="form-control" type="number" min="0" max="1" step="0.01" name="slides[__INDEX__][overlay_opacity]" value="0.78"></div>
                                    <div class="col-md-2"><label class="form-label">Duration (sec)</label><input class="form-control" type="number" min="0" max="60" name="slides[__INDEX__][duration_seconds]" value="0"></div>
                                    <div class="col-md-2 d-flex align-items-end gap-3">
                                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="slides[__INDEX__][mute_default]" value="1" checked><label class="form-check-label">Mute</label></div>
                                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="slides[__INDEX__][is_active]" value="1" checked><label class="form-check-label">Active</label></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Simpan Slider Video</button>
                        <div class="upload-progress-wrap" id="sliderUploadProgressWrap">
                            <div class="progress mt-2" role="progressbar" aria-label="Upload progress" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" id="sliderUploadProgressBar" style="width: 0%">0%</div>
                            </div>
                            <small class="text-muted" id="sliderUploadProgressText">Menyiapkan upload...</small>
                            <small class="text-muted d-block" id="sliderUploadSummaryText"></small>
                        </div>
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
        const slideList = document.getElementById('slideList');
        const addSlideButton = document.getElementById('btnAddSlide');
        const reindexButton = document.getElementById('btnReindexSlides');
        const editingModeInput = document.getElementById('editing_mode');
        const modeButtons = document.querySelectorAll('.js-mode-btn');
        const transitionEffectInput = document.querySelector('[name="transition_effect"]');
        const autoplayInput = document.querySelector('[name="autoplay"]');
        const loopInput = document.querySelector('[name="loop"]');
        const mobileDisableVideoInput = document.querySelector('[name="mobile_disable_video"]');
        const previewStage = document.getElementById('sliderPreviewStage');
        const previewVideo = document.getElementById('sliderPreviewVideo');
        const previewExternal = document.getElementById('sliderPreviewExternal');
        const previewFallback = document.getElementById('sliderPreviewFallback');
        const previewOverlay = document.getElementById('sliderPreviewOverlay');
        const previewContent = document.getElementById('sliderPreviewContent');
        const previewTitle = document.getElementById('sliderPreviewTitle');
        const previewCaption = document.getElementById('sliderPreviewCaption');
        const previewCta = document.getElementById('sliderPreviewCta');
        const previewMeta = document.getElementById('sliderPreviewMeta');
        const previewPlayButton = document.getElementById('sliderPreviewPlay');
        const previewPrevButton = document.getElementById('sliderPreviewPrev');
        const previewNextButton = document.getElementById('sliderPreviewNext');
        const previewModeButtons = document.querySelectorAll('.js-preview-mode-btn');
        const slideItemTemplate = document.getElementById('slideItemTemplate');
        const previewProgressBar = document.getElementById('sliderPreviewProgressBar');
        const sliderForm = document.getElementById('sliderVideoForm');
        const uploadProgressWrap = document.getElementById('sliderUploadProgressWrap');
        const uploadProgressBar = document.getElementById('sliderUploadProgressBar');
        const uploadProgressText = document.getElementById('sliderUploadProgressText');
        const uploadSummaryText = document.getElementById('sliderUploadSummaryText');
        let previewSlides = [];
        let previewIndex = 0;
        let previewTimer = null;
        let previewProgressTimer = null;
        let previewProgressElapsed = 0;
        let previewPlaying = false;
        let previewMode = 'desktop';

        const applyModeState = () => {
            const activeMode = editingModeInput instanceof HTMLInputElement ? editingModeInput.value : 'design';
            modeButtons.forEach((button) => {
                const isActive = button.getAttribute('data-mode') === activeMode;
                button.classList.toggle('btn-primary', isActive);
                button.classList.toggle('btn-outline-primary', !isActive);
            });
        };

        modeButtons.forEach((button) => {
            button.addEventListener('click', () => {
                if (!(editingModeInput instanceof HTMLInputElement)) {
                    return;
                }
                editingModeInput.value = button.getAttribute('data-mode') || 'design';
                applyModeState();
            });
        });
        applyModeState();

        const reindexSlides = () => {
            if (!(slideList instanceof HTMLElement)) {
                return;
            }
            const items = Array.from(slideList.querySelectorAll('.js-slide-item'));
            items.forEach((item, index) => {
                item.dataset.index = `${index}`;
                const orderLabel = item.querySelector('.js-slide-order');
                if (orderLabel instanceof HTMLElement) {
                    orderLabel.textContent = `${index + 1}`;
                }
                item.querySelectorAll('[name]').forEach((input) => {
                    const currentName = input.getAttribute('name') || '';
                    input.setAttribute('name', currentName.replace(/slides\[\d+\]/, `slides[${index}]`));
                });
            });
            rebuildPreviewSlides();
        };

        const normalizeMediaUrl = (value) => {
            const url = `${value || ''}`.trim();
            if (url === '') {
                return '';
            }
            if (/^https?:\/\//i.test(url) || url.startsWith('/')) {
                return url;
            }

            return `/storage/${url.replace(/^\/+/, '')}`;
        };

        const toEmbedUrl = (rawUrl) => {
            const url = `${rawUrl || ''}`.trim();
            if (url === '') {
                return '';
            }
            if (url.includes('youtube.com/embed/') || url.includes('player.vimeo.com/video/')) {
                return url;
            }
            try {
                const parsed = new URL(url);
                if (parsed.hostname.includes('youtube.com')) {
                    const videoId = parsed.searchParams.get('v');
                    if (videoId) {
                        return `https://www.youtube.com/embed/${videoId}`;
                    }
                }
                if (parsed.hostname.includes('youtu.be')) {
                    const id = parsed.pathname.replace('/', '');
                    if (id) {
                        return `https://www.youtube.com/embed/${id}`;
                    }
                }
                if (parsed.hostname.includes('vimeo.com')) {
                    const id = parsed.pathname.split('/').filter(Boolean).pop();
                    if (id) {
                        return `https://player.vimeo.com/video/${id}`;
                    }
                }
            } catch {}

            return url;
        };

        const mediaFromInput = (item, selector, isImage = false) => {
            const input = item.querySelector(selector);
            if (!(input instanceof HTMLInputElement)) {
                return '';
            }
            const file = input.files?.[0];
            if (file) {
                return URL.createObjectURL(file);
            }
            if (isImage) {
                return '';
            }

            return '';
        };

        const collectSlideModel = (item) => {
            const read = (suffix) => {
                const element = item.querySelector(`[name$="${suffix}"]`);
                if (element instanceof HTMLInputElement || element instanceof HTMLTextAreaElement || element instanceof HTMLSelectElement) {
                    return `${element.value || ''}`.trim();
                }

                return '';
            };
            const source = read('[video_source]') || 'upload';
            const uploadVideo = mediaFromInput(item, '[name$="[video_file]"]');
            const tabletVideo = mediaFromInput(item, '[name$="[tablet_video_file]"]');
            const posterImage = mediaFromInput(item, '[name$="[poster_image_file]"]', true);
            const mobileFallback = mediaFromInput(item, '[name$="[mobile_fallback_image_file]"]', true);
            const mappedVideo = normalizeMediaUrl(read('[video_url_text]'));
            const externalUrl = read('[external_url]');
            const ctaLink = read('[cta_url]');
            const title = read('[title]');
            const caption = read('[caption]');
            const textPosition = read('[text_position]') || 'center';
            const overlayColor = read('[overlay_color]') || '#000000';
            const overlayOpacity = Number(read('[overlay_opacity]') || 0.78);
            const rawDuration = Number(read('[duration_seconds]') || 0);
            const duration = Number.isFinite(rawDuration)
                ? (rawDuration <= 0 ? 0 : Math.max(2, rawDuration))
                : 0;

            return {
                source,
                title,
                caption,
                ctaText: read('[cta_text]'),
                ctaUrl: ctaLink,
                videoUrl: uploadVideo || mappedVideo,
                externalUrl: toEmbedUrl(externalUrl),
                tabletVideo,
                posterImage,
                mobileFallback,
                textPosition,
                overlayColor,
                overlayOpacity: Number.isFinite(overlayOpacity) ? Math.min(1, Math.max(0, overlayOpacity)) : 0.78,
                duration,
            };
        };

        const rebuildPreviewSlides = () => {
            if (!(slideList instanceof HTMLElement)) {
                return;
            }
            previewSlides = Array.from(slideList.querySelectorAll('.js-slide-item')).map((item) => collectSlideModel(item));
            if (previewSlides.length === 0) {
                previewIndex = 0;
            } else if (previewIndex >= previewSlides.length) {
                previewIndex = previewSlides.length - 1;
            }
            renderPreview();
        };

        const clearPreviewTimers = () => {
            if (previewTimer !== null) {
                window.clearTimeout(previewTimer);
                previewTimer = null;
            }
            if (previewProgressTimer !== null) {
                window.clearInterval(previewProgressTimer);
                previewProgressTimer = null;
            }
        };

        const applyTransitionClass = () => {
            if (!(previewStage instanceof HTMLElement) || !(transitionEffectInput instanceof HTMLSelectElement)) {
                return;
            }
            previewStage.classList.remove('effect-fade', 'effect-slide', 'effect-cube');
            previewStage.classList.add(`effect-${transitionEffectInput.value || 'fade'}`);
        };

        const applyPreviewModeState = () => {
            if (!(previewStage instanceof HTMLElement)) {
                return;
            }
            previewStage.classList.remove('preview-device-desktop', 'preview-device-tablet', 'preview-device-mobile');
            previewStage.classList.add(`preview-device-${previewMode}`);
            previewModeButtons.forEach((button) => {
                const active = button.getAttribute('data-preview-mode') === previewMode;
                button.classList.toggle('btn-primary', active);
                button.classList.toggle('btn-outline-primary', !active);
            });
        };

        const renderPreview = () => {
            applyTransitionClass();
            const slide = previewSlides[previewIndex] || null;
            if (!slide) {
                if (previewTitle instanceof HTMLElement) {
                    previewTitle.textContent = 'Preview Slider';
                }
                if (previewCaption instanceof HTMLElement) {
                    previewCaption.textContent = 'Tambahkan slide untuk mulai preview.';
                }
                if (previewMeta instanceof HTMLElement) {
                    previewMeta.textContent = 'Slide 0/0';
                }
                if (previewCta instanceof HTMLAnchorElement) {
                    previewCta.style.display = 'none';
                }
                if (previewVideo instanceof HTMLVideoElement) {
                    previewVideo.classList.remove('active');
                    previewVideo.pause();
                    previewVideo.removeAttribute('src');
                }
                if (previewExternal instanceof HTMLIFrameElement) {
                    previewExternal.classList.remove('active');
                    previewExternal.src = '';
                }
                if (previewFallback instanceof HTMLElement) {
                    previewFallback.classList.remove('active');
                    previewFallback.style.backgroundImage = '';
                }
                if (previewProgressBar instanceof HTMLElement) {
                    previewProgressBar.style.width = '0%';
                }

                return;
            }

            if (previewTitle instanceof HTMLElement) {
                previewTitle.textContent = slide.title || `Slide ${previewIndex + 1}`;
            }
            if (previewCaption instanceof HTMLElement) {
                previewCaption.textContent = slide.caption || '';
            }
            if (previewMeta instanceof HTMLElement) {
                const durationLabel = slide.duration <= 0 ? 'auto(video)' : `${slide.duration}s`;
                previewMeta.textContent = `Slide ${previewIndex + 1}/${previewSlides.length} · ${durationLabel} · ${previewMode}`;
            }
            if (previewOverlay instanceof HTMLElement) {
                previewOverlay.style.background = `linear-gradient(180deg, ${slide.overlayColor}55 0%, ${slide.overlayColor}${Math.round(slide.overlayOpacity * 255).toString(16).padStart(2, '0')} 70%, ${slide.overlayColor}ee 100%)`;
            }
            if (previewContent instanceof HTMLElement) {
                previewContent.classList.remove('pos-left', 'pos-center', 'pos-right');
                previewContent.classList.add(`pos-${slide.textPosition}`);
            }
            if (previewCta instanceof HTMLAnchorElement) {
                if (slide.ctaText !== '') {
                    previewCta.textContent = slide.ctaText;
                    previewCta.href = slide.ctaUrl || '#';
                    previewCta.style.display = 'inline-flex';
                } else {
                    previewCta.style.display = 'none';
                }
            }

            const useExternal = slide.source === 'external' && slide.externalUrl !== '';
            const mobileDisabled = mobileDisableVideoInput instanceof HTMLInputElement ? mobileDisableVideoInput.checked : true;
            const forceMobileFallback = previewMode === 'mobile' && mobileDisabled;
            const useVideo = slide.videoUrl !== '' && !forceMobileFallback;
            const useFallback = forceMobileFallback || (!useExternal && !useVideo);
            if (previewVideo instanceof HTMLVideoElement) {
                previewVideo.classList.toggle('active', useVideo && !useExternal);
                if (useVideo && !useExternal) {
                    const chosenVideo = previewMode === 'tablet' && slide.tabletVideo !== '' ? slide.tabletVideo : slide.videoUrl;
                    if (previewVideo.src !== chosenVideo) {
                        previewVideo.src = chosenVideo;
                    }
                    if (slide.posterImage !== '') {
                        previewVideo.poster = slide.posterImage;
                    }
                    if (previewPlaying) {
                        previewVideo.play().catch(() => null);
                    } else {
                        previewVideo.pause();
                    }
                } else {
                    previewVideo.pause();
                    previewVideo.removeAttribute('src');
                }
            }
            if (previewExternal instanceof HTMLIFrameElement) {
                previewExternal.classList.toggle('active', useExternal);
                previewExternal.src = useExternal ? `${slide.externalUrl}${slide.externalUrl.includes('?') ? '&' : '?'}autoplay=${previewPlaying ? '1' : '0'}&mute=1` : '';
            }
            if (previewFallback instanceof HTMLElement) {
                const fallbackUrl = slide.mobileFallback || slide.posterImage || '';
                previewFallback.classList.toggle('active', useFallback || fallbackUrl !== '');
                previewFallback.style.backgroundImage = fallbackUrl !== '' ? `url('${fallbackUrl}')` : '';
                previewFallback.style.backgroundSize = 'cover';
                previewFallback.style.backgroundPosition = 'center';
            }
            if (previewProgressBar instanceof HTMLElement) {
                previewProgressBar.style.width = '0%';
            }
        };

        const schedulePreviewProgress = () => {
            clearPreviewTimers();
            const slide = previewSlides[previewIndex] || null;
            if (!slide) {
                return;
            }
            const resolveDurationMs = () => {
                if (slide.duration > 0) {
                    return slide.duration * 1000;
                }
                if (previewVideo instanceof HTMLVideoElement && previewVideo.classList.contains('active')) {
                    const mediaDuration = Number(previewVideo.duration || 0);
                    if (Number.isFinite(mediaDuration) && mediaDuration > 0) {
                        return mediaDuration * 1000;
                    }
                }

                return 7000;
            };
            const durationMs = resolveDurationMs();
            previewProgressElapsed = 0;
            if (previewProgressBar instanceof HTMLElement) {
                previewProgressBar.style.width = '0%';
            }
            previewProgressTimer = window.setInterval(() => {
                previewProgressElapsed += 100;
                const progress = Math.min(100, (previewProgressElapsed / durationMs) * 100);
                if (previewProgressBar instanceof HTMLElement) {
                    previewProgressBar.style.width = `${progress}%`;
                }
            }, 100);
            previewTimer = window.setTimeout(() => {
                const loopEnabled = loopInput instanceof HTMLInputElement ? loopInput.checked : true;
                if (previewIndex < previewSlides.length - 1) {
                    previewIndex += 1;
                } else if (loopEnabled) {
                    previewIndex = 0;
                } else {
                    previewPlaying = false;
                    updatePlayButton();
                    clearPreviewTimers();
                    return;
                }
                renderPreview();
                if (previewPlaying) {
                    schedulePreviewProgress();
                }
            }, durationMs);
        };

        const updatePlayButton = () => {
            if (!(previewPlayButton instanceof HTMLButtonElement)) {
                return;
            }
            previewPlayButton.textContent = previewPlaying ? 'Pause' : 'Play';
            previewPlayButton.classList.toggle('btn-primary', previewPlaying);
            previewPlayButton.classList.toggle('btn-outline-primary', !previewPlaying);
        };

        const startPreview = () => {
            if (previewSlides.length === 0) {
                return;
            }
            previewPlaying = true;
            updatePlayButton();
            renderPreview();
            schedulePreviewProgress();
        };

        const pausePreview = () => {
            previewPlaying = false;
            updatePlayButton();
            clearPreviewTimers();
            if (previewVideo instanceof HTMLVideoElement) {
                previewVideo.pause();
            }
        };

        const stepPreview = (delta) => {
            if (previewSlides.length === 0) {
                return;
            }
            const loopEnabled = loopInput instanceof HTMLInputElement ? loopInput.checked : true;
            const tentative = previewIndex + delta;
            if (loopEnabled) {
                previewIndex = (tentative + previewSlides.length) % previewSlides.length;
            } else {
                previewIndex = Math.max(0, Math.min(previewSlides.length - 1, tentative));
            }
            renderPreview();
            if (previewPlaying) {
                schedulePreviewProgress();
            } else {
                clearPreviewTimers();
            }
        };

        const newSlideTemplate = (index) => {
            if (!(slideItemTemplate instanceof HTMLTemplateElement)) {
                return '';
            }
            return slideItemTemplate.innerHTML
                .replaceAll('__INDEX__', `${index}`)
                .replaceAll('__ORDER__', `${index + 1}`);
        };

        addSlideButton?.addEventListener('click', () => {
            if (!(slideList instanceof HTMLElement)) {
                return;
            }
            const nextIndex = slideList.querySelectorAll('.js-slide-item').length;
            const html = newSlideTemplate(nextIndex);
            if (html === '') {
                return;
            }
            slideList.insertAdjacentHTML('afterbegin', html);
            reindexSlides();
            const firstTitleInput = slideList.querySelector('.js-slide-item:first-child input[name$="[title]"]');
            if (firstTitleInput instanceof HTMLInputElement) {
                firstTitleInput.focus();
            }
        });

        reindexButton?.addEventListener('click', reindexSlides);

        slideList?.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement) || !target.classList.contains('js-remove-slide')) {
                return;
            }
            const card = target.closest('.js-slide-item');
            if (!(card instanceof HTMLElement)) {
                return;
            }
            card.remove();
            reindexSlides();
        });

        let dragged = null;
        slideList?.addEventListener('dragstart', (event) => {
            const target = event.target;
            if (target instanceof HTMLElement && target.classList.contains('js-slide-item')) {
                dragged = target;
            }
        });
        slideList?.addEventListener('dragover', (event) => event.preventDefault());
        slideList?.addEventListener('drop', (event) => {
            event.preventDefault();
            const target = event.target;
            if (!(target instanceof HTMLElement) || !(dragged instanceof HTMLElement) || !(slideList instanceof HTMLElement)) {
                return;
            }
            const dropItem = target.closest('.js-slide-item');
            if (!(dropItem instanceof HTMLElement) || dropItem === dragged) {
                return;
            }
            const rows = Array.from(slideList.querySelectorAll('.js-slide-item'));
            const draggedIndex = rows.indexOf(dragged);
            const dropIndex = rows.indexOf(dropItem);
            if (draggedIndex < 0 || dropIndex < 0) {
                return;
            }
            if (draggedIndex < dropIndex) {
                slideList.insertBefore(dragged, dropItem.nextSibling);
            } else {
                slideList.insertBefore(dragged, dropItem);
            }
            reindexSlides();
            dragged = null;
        });

        previewPlayButton?.addEventListener('click', () => {
            if (previewPlaying) {
                pausePreview();
            } else {
                startPreview();
            }
        });
        previewPrevButton?.addEventListener('click', () => stepPreview(-1));
        previewNextButton?.addEventListener('click', () => stepPreview(1));
        previewModeButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const nextMode = button.getAttribute('data-preview-mode');
                if (!nextMode) {
                    return;
                }
                previewMode = nextMode;
                applyPreviewModeState();
                renderPreview();
                if (previewPlaying) {
                    schedulePreviewProgress();
                }
            });
        });

        autoplayInput?.addEventListener('change', () => {
            if (!(autoplayInput instanceof HTMLInputElement)) {
                return;
            }
            if (autoplayInput.checked) {
                startPreview();
            } else {
                pausePreview();
            }
        });
        transitionEffectInput?.addEventListener('change', renderPreview);
        mobileDisableVideoInput?.addEventListener('change', renderPreview);
        loopInput?.addEventListener('change', () => {
            if (previewPlaying) {
                schedulePreviewProgress();
            }
        });
        previewVideo?.addEventListener('loadedmetadata', () => {
            const slide = previewSlides[previewIndex] || null;
            if (previewPlaying && slide && slide.duration <= 0) {
                schedulePreviewProgress();
            }
        });
        slideList?.addEventListener('input', rebuildPreviewSlides);
        slideList?.addEventListener('change', rebuildPreviewSlides);

        applyPreviewModeState();
        rebuildPreviewSlides();
        if (autoplayInput instanceof HTMLInputElement && autoplayInput.checked) {
            startPreview();
        } else {
            pausePreview();
            renderPreview();
        }

        if (sliderForm instanceof HTMLFormElement) {
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
            sliderForm.addEventListener('submit', (event) => {
                if (uploading) {
                    event.preventDefault();
                    return;
                }
                event.preventDefault();
                const totalUploadBytes = Array.from(sliderForm.querySelectorAll('input[type="file"]')).reduce((carry, input) => {
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
                const selectedFiles = Array.from(sliderForm.querySelectorAll('input[type="file"]'))
                    .flatMap((input) => input instanceof HTMLInputElement && input.files ? Array.from(input.files) : []);
                const fileCount = selectedFiles.length;
                const fileNames = selectedFiles.slice(0, 3).map((file) => file.name);
                uploading = true;
                const submitButton = sliderForm.querySelector('button[type="submit"]');
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

                const formActionUrl = sliderForm.getAttribute('action') || window.location.href;
                const xhr = new XMLHttpRequest();
                const uploadStartedAt = Date.now();
                xhr.open((sliderForm.method || 'POST').toUpperCase(), formActionUrl, true);
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
                xhr.send(new FormData(sliderForm));
            });
        }
    })();
</script>
@endsection
