<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Models\LandingSetting;
use App\Support\VideoConversionEngine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class WebsiteCmsSliderVideoController extends Controller
{
    public function index(): View
    {
        $settings = LandingSetting::query()->pluck('value', 'key')->all();
        $metaMap = $this->decodeSlideMetaMap((string) ($settings['cms_slider_video_slide_meta'] ?? ''));
        $slides = HeroSlide::query()->orderBy('sort_order')->get();
        $slidesData = $slides->map(function (HeroSlide $slide) use ($metaMap): array {
            $meta = $metaMap[$slide->id] ?? [];

            return [
                'id' => $slide->id,
                'slider_name' => (string) ($meta['slider_name'] ?? ''),
                'title' => (string) $slide->title,
                'caption' => (string) ($slide->caption ?? ''),
                'video_url' => (string) ($slide->video_url ?? ''),
                'cta_text' => (string) ($slide->cta_text ?? ''),
                'cta_url' => (string) ($slide->cta_url ?? ''),
                'duration_seconds' => $slide->duration_seconds !== null ? (int) $slide->duration_seconds : 0,
                'overlay_opacity' => (string) ($slide->overlay_opacity ?: '0.78'),
                'is_active' => (bool) $slide->is_active,
                'video_source' => (string) ($meta['video_source'] ?? 'upload'),
                'external_url' => (string) ($meta['external_url'] ?? ''),
                'poster_image' => (string) ($meta['poster_image'] ?? ''),
                'tablet_video' => (string) ($meta['tablet_video'] ?? ''),
                'mobile_fallback_image' => (string) ($meta['mobile_fallback_image'] ?? ''),
                'mute_default' => (bool) ($meta['mute_default'] ?? true),
                'text_position' => (string) ($meta['text_position'] ?? 'center'),
                'overlay_color' => (string) ($meta['overlay_color'] ?? '#000000'),
                'text_animation' => (string) ($meta['text_animation'] ?? 'fade-up'),
            ];
        })->values()->all();
        if ($slidesData === []) {
            $slidesData = [$this->emptySlideData()];
        }

        return view('content.dashboard.website-cms-slider-video', [
            'form' => [
                'engine_mode' => (string) ($settings['cms_slider_engine_mode'] ?? 'sr7'),
                'transition_effect' => (string) ($settings['cms_slider_transition_effect'] ?? 'fade'),
                'autoplay' => ($settings['cms_slider_autoplay'] ?? '1') === '1',
                'loop' => ($settings['cms_slider_loop'] ?? '1') === '1',
                'lazy_load' => ($settings['cms_slider_lazy_load'] ?? '1') === '1',
                'preload_critical' => ($settings['cms_slider_preload_critical'] ?? '1') === '1',
                'mobile_disable_video' => ($settings['cms_slider_mobile_disable_video'] ?? '1') === '1',
                'editing_mode' => (string) ($settings['cms_slider_editing_mode'] ?? 'design'),
            ],
            'slides' => $slidesData,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'engine_mode' => ['required', 'in:sr6,sr7,xslider'],
            'transition_effect' => ['required', 'in:fade,slide,cube'],
            'autoplay' => ['nullable', 'boolean'],
            'loop' => ['nullable', 'boolean'],
            'lazy_load' => ['nullable', 'boolean'],
            'preload_critical' => ['nullable', 'boolean'],
            'mobile_disable_video' => ['nullable', 'boolean'],
            'editing_mode' => ['required', 'in:design,animation,action'],
            'slides' => ['required', 'array', 'min:1'],
            'slides.*.id' => ['nullable', 'integer'],
            'slides.*.slider_name' => ['nullable', 'string', 'max:180'],
            'slides.*.title' => ['nullable', 'string', 'max:180'],
            'slides.*.caption' => ['nullable', 'string', 'max:600'],
            'slides.*.cta_text' => ['nullable', 'string', 'max:80'],
            'slides.*.cta_url' => ['nullable', 'string', 'max:255'],
            'slides.*.duration_seconds' => ['required', 'integer', 'min:0', 'max:60'],
            'slides.*.overlay_opacity' => ['required', 'numeric', 'min:0', 'max:1'],
            'slides.*.is_active' => ['nullable', 'boolean'],
            'slides.*.video_source' => ['required', 'in:upload,external'],
            'slides.*.video_url_text' => ['nullable', 'string', 'max:1000'],
            'slides.*.external_url' => ['nullable', 'string', 'max:1000'],
            'slides.*.mute_default' => ['nullable', 'boolean'],
            'slides.*.text_position' => ['required', 'in:left,center,right'],
            'slides.*.overlay_color' => ['required', 'string', 'max:20'],
            'slides.*.text_animation' => ['required', 'in:fade-up,slide-up,zoom-in'],
            'slides.*.video_file' => ['nullable', 'file', 'mimetypes:video/*', 'max:512000'],
            'slides.*.tablet_video_file' => ['nullable', 'file', 'mimetypes:video/*', 'max:512000'],
            'slides.*.poster_image_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'slides.*.mobile_fallback_image_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $existingSlides = HeroSlide::query()->get()->keyBy('id');
        $submittedIds = [];
        $metaMap = [];
        $slides = $data['slides'] ?? [];

        foreach ($slides as $index => $slideInput) {
            $slideId = isset($slideInput['id']) ? (int) $slideInput['id'] : null;
            $slide = $slideId && $existingSlides->has($slideId) ? $existingSlides[$slideId] : new HeroSlide;

            $videoSource = (string) ($slideInput['video_source'] ?? 'upload');
            $videoUrl = $videoSource === 'external'
                ? (string) ($slideInput['external_url'] ?? $slideInput['video_url_text'] ?? '')
                : (string) ($slideInput['video_url_text'] ?? ($slide->video_url ?? ''));

            $uploadedVideo = $slideInput['video_file'] ?? null;
            if ($uploadedVideo instanceof UploadedFile) {
                $videoUrl = $this->storeVideoMedia($uploadedVideo, 'cms/slider-video', 'slider', (string) ($slideInput['slider_name'] ?? $slideInput['title'] ?? 'Slider Video'));
            }

            $slide->fill([
                'title' => (string) ($slideInput['title'] ?? ''),
                'caption' => (string) ($slideInput['caption'] ?? ''),
                'video_url' => $videoUrl,
                'cta_text' => (string) ($slideInput['cta_text'] ?? ''),
                'cta_url' => (string) ($slideInput['cta_url'] ?? ''),
                'sort_order' => $index + 1,
                'duration_seconds' => max(0, (int) ($slideInput['duration_seconds'] ?? 0)),
                'overlay_opacity' => (float) ($slideInput['overlay_opacity'] ?? 0.78),
                'is_active' => ! empty($slideInput['is_active']),
            ]);
            $slide->save();

            $submittedIds[] = (int) $slide->id;

            $tabletVideoPath = (string) (($this->storeOptionalVideo($slideInput['tablet_video_file'] ?? null, 'cms/slider-video/tablet', 'slider_tablet', (string) ($slideInput['slider_name'] ?? $slideInput['title'] ?? 'Slider Video Tablet')) ?? ''));
            $posterPath = (string) (($this->storeOptional($slideInput['poster_image_file'] ?? null, 'cms/slider-video/posters')) ?? '');
            $mobileFallbackPath = (string) (($this->storeOptional($slideInput['mobile_fallback_image_file'] ?? null, 'cms/slider-video/mobile')) ?? '');

            $metaMap[(string) $slide->id] = [
                'slider_name' => (string) ($slideInput['slider_name'] ?? ''),
                'video_source' => $videoSource,
                'external_url' => (string) ($slideInput['external_url'] ?? ''),
                'tablet_video' => $tabletVideoPath !== '' ? $tabletVideoPath : ($this->existingMetaValue((string) $slide->id, 'tablet_video') ?? ''),
                'poster_image' => $posterPath !== '' ? $posterPath : ($this->existingMetaValue((string) $slide->id, 'poster_image') ?? ''),
                'mobile_fallback_image' => $mobileFallbackPath !== '' ? $mobileFallbackPath : ($this->existingMetaValue((string) $slide->id, 'mobile_fallback_image') ?? ''),
                'mute_default' => ! empty($slideInput['mute_default']),
                'text_position' => (string) ($slideInput['text_position'] ?? 'center'),
                'overlay_color' => (string) ($slideInput['overlay_color'] ?? '#000000'),
                'text_animation' => (string) ($slideInput['text_animation'] ?? 'fade-up'),
            ];
        }

        if (! empty($submittedIds)) {
            HeroSlide::query()->whereNotIn('id', $submittedIds)->delete();
        }

        $payload = [
            'cms_slider_engine_mode' => $data['engine_mode'],
            'cms_slider_transition_effect' => $data['transition_effect'],
            'cms_slider_autoplay' => $request->boolean('autoplay') ? '1' : '0',
            'cms_slider_loop' => $request->boolean('loop') ? '1' : '0',
            'cms_slider_lazy_load' => $request->boolean('lazy_load') ? '1' : '0',
            'cms_slider_preload_critical' => $request->boolean('preload_critical') ? '1' : '0',
            'cms_slider_mobile_disable_video' => $request->boolean('mobile_disable_video') ? '1' : '0',
            'cms_slider_editing_mode' => $data['editing_mode'],
            'cms_slider_video_slide_meta' => json_encode($metaMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];

        foreach ($payload as $key => $value) {
            LandingSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()
            ->route('dashboard-website-cms-slider-video')
            ->with('success', 'Slider Video berhasil diperbarui.');
    }

    private array $existingMetaMap = [];

    private function decodeSlideMetaMap(string $raw): array
    {
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function storeMedia(UploadedFile $file, string $dir): string
    {
        return $file->store($dir, 'public');
    }

    private function storeVideoMedia(UploadedFile $file, string $dir, string $profile, string $title): string
    {
        $asset = app(VideoConversionEngine::class)->registerUpload(
            $file,
            $dir,
            $profile,
            $title,
            ['setting_key' => 'cms_slider_video_slide_meta']
        );

        return (string) $asset->source_path;
    }

    private function storeOptional(?UploadedFile $file, string $dir): ?string
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        return $this->storeMedia($file, $dir);
    }

    private function storeOptionalVideo(?UploadedFile $file, string $dir, string $profile, string $title): ?string
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        return $this->storeVideoMedia($file, $dir, $profile, $title);
    }

    private function existingMetaValue(string $slideId, string $key): ?string
    {
        if ($this->existingMetaMap === []) {
            $settings = LandingSetting::query()->where('key', 'cms_slider_video_slide_meta')->value('value');
            $this->existingMetaMap = $this->decodeSlideMetaMap((string) $settings);
        }

        $value = $this->existingMetaMap[$slideId][$key] ?? null;

        return is_string($value) ? $value : null;
    }

    private function emptySlideData(): array
    {
        return [
            'id' => null,
            'slider_name' => '',
            'title' => '',
            'caption' => '',
            'video_url' => '',
            'cta_text' => '',
            'cta_url' => '',
            'duration_seconds' => 0,
            'overlay_opacity' => '0.78',
            'is_active' => true,
            'video_source' => 'upload',
            'external_url' => '',
            'poster_image' => '',
            'tablet_video' => '',
            'mobile_fallback_image' => '',
            'mute_default' => true,
            'text_position' => 'center',
            'overlay_color' => '#000000',
            'text_animation' => 'fade-up',
        ];
    }
}
