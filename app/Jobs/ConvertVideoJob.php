<?php

namespace App\Jobs;

use App\Models\HeroSlide;
use App\Models\LandingSetting;
use App\Models\VideoAsset;
use FFMpeg\Format\Video\WebM;
use FFMpeg\Format\Video\X264;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ConvertVideoJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $videoAssetId) {}

    public function handle(): void
    {
        $asset = VideoAsset::query()->find($this->videoAssetId);
        if (! $asset) {
            return;
        }

        $asset->update([
            'status' => 'processing',
            'error_message' => null,
        ]);

        $diskName = (string) config('media.disk', 'public');
        $baseDir = 'media/converted/'.$asset->id;
        $webmPath = $baseDir.'/video.webm';
        $mp4Path = $baseDir.'/video.mp4';
        $thumbPath = $baseDir.'/thumb.jpg';
        $duration = null;

        try {
            $source = FFMpeg::fromDisk($diskName)->open($asset->source_path);
            $duration = $source->getDurationInSeconds();

            $source
                ->export()
                ->toDisk($diskName)
                ->inFormat(new WebM)
                ->save($webmPath);

            FFMpeg::fromDisk($diskName)
                ->open($asset->source_path)
                ->export()
                ->toDisk($diskName)
                ->inFormat(new X264('aac'))
                ->save($mp4Path);

            FFMpeg::fromDisk($diskName)
                ->open($asset->source_path)
                ->getFrameFromSeconds(1)
                ->export()
                ->toDisk($diskName)
                ->save($thumbPath);
        } catch (\Throwable $e) {
            $asset->update([
                'status' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 1000),
                'meta' => array_merge(is_array($asset->meta) ? $asset->meta : [], [
                    'conversion_error' => mb_substr($e->getMessage(), 0, 1500),
                ]),
            ]);

            return;
        }

        $cdnBase = rtrim((string) config('media.cdn_base_url', ''), '/');
        $webmCdn = $cdnBase !== '' ? $cdnBase.'/'.ltrim($webmPath, '/') : '/storage/'.ltrim($webmPath, '/');
        $mp4Cdn = $cdnBase !== '' ? $cdnBase.'/'.ltrim($mp4Path, '/') : '/storage/'.ltrim($mp4Path, '/');
        $thumbCdn = $cdnBase !== '' ? $cdnBase.'/'.ltrim($thumbPath, '/') : '/storage/'.ltrim($thumbPath, '/');

        $asset->update([
            'status' => 'ready',
            'webm_path' => $webmPath,
            'mp4_path' => $mp4Path,
            'thumb_path' => $thumbPath,
            'stream_path' => $webmPath,
            'cdn_url' => $webmCdn,
            'duration_seconds' => $duration !== null ? (int) round((float) $duration) : null,
            'converted_at' => now(),
            'meta' => array_merge(is_array($asset->meta) ? $asset->meta : [], [
                'webm_url' => $webmCdn,
                'mp4_url' => $mp4Cdn,
                'thumb_url' => $thumbCdn,
            ]),
        ]);

        $this->applyToCmsAndSlides($asset);
    }

    private function applyToCmsAndSlides(VideoAsset $asset): void
    {
        if (! is_string($asset->source_path) || $asset->source_path === '' || ! is_string($asset->webm_path) || $asset->webm_path === '') {
            return;
        }

        $from = $asset->source_path;
        $to = $asset->webm_path;

        HeroSlide::query()
            ->where('video_url', $from)
            ->update(['video_url' => $to]);

        $settingKeys = [
            'cms_slider_video_slide_meta',
            'cms_portfolio_payload',
            'cms_blog_news_payload',
        ];
        foreach ($settingKeys as $key) {
            $value = LandingSetting::query()->where('key', $key)->value('value');
            if (! is_string($value) || $value === '' || ! str_contains($value, $from)) {
                continue;
            }
            LandingSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => str_replace($from, $to, $value)]
            );
        }

        $meta = is_array($asset->meta) ? $asset->meta : [];
        $settingKey = isset($meta['setting_key']) && is_string($meta['setting_key']) ? $meta['setting_key'] : '';
        if ($settingKey !== '') {
            $value = LandingSetting::query()->where('key', $settingKey)->value('value');
            if (is_string($value) && str_contains($value, $from)) {
                LandingSetting::query()->updateOrCreate(
                    ['key' => $settingKey],
                    ['value' => str_replace($from, $to, $value)]
                );
            }
        }
    }
}
