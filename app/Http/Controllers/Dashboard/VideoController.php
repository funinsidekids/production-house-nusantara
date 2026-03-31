<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\ConvertVideoJob;
use App\Models\VideoAsset;
use App\Support\VideoConversionEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $data = $request->validate([
            'video' => ['required', 'file', 'mimetypes:video/*', 'max:512000'],
            'title' => ['nullable', 'string', 'max:190'],
            'context' => ['nullable', 'string', 'max:80'],
        ]);

        $asset = app(VideoConversionEngine::class)->registerUpload(
            $data['video'],
            'cms/media/videos',
            (string) ($data['context'] ?? 'media'),
            (string) ($data['title'] ?? $data['video']->getClientOriginalName()),
            []
        );

        return response()->json([
            'message' => 'Upload diterima. Konversi berjalan di queue.',
            'video_id' => $asset->id,
            'status' => $asset->status,
            'source_path' => $asset->source_path,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $asset = VideoAsset::query()->findOrFail($id);
        $meta = is_array($asset->meta) ? $asset->meta : [];

        return response()->json([
            'id' => $asset->id,
            'title' => $asset->title,
            'context' => $asset->context,
            'status' => $asset->status,
            'source_path' => $asset->source_path,
            'webm_path' => $asset->webm_path,
            'mp4_path' => $asset->mp4_path,
            'thumb_path' => $asset->thumb_path,
            'duration_seconds' => $asset->duration_seconds,
            'error_message' => $asset->error_message,
            'webm_url' => $meta['webm_url'] ?? null,
            'mp4_url' => $meta['mp4_url'] ?? null,
            'thumb_url' => $meta['thumb_url'] ?? null,
            'converted_at' => optional($asset->converted_at)?->toDateTimeString(),
        ]);
    }

    public function retry(int $id): RedirectResponse
    {
        $asset = VideoAsset::query()->findOrFail($id);

        $asset->update([
            'status' => 'uploaded',
            'error_message' => null,
        ]);

        ConvertVideoJob::dispatch($asset->id)->onQueue('media');

        return redirect()
            ->route('dashboard-analytics')
            ->with('success', 'Retry konversi video berhasil dijadwalkan.');
    }
}
