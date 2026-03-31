<?php

namespace App\Support;

use App\Models\VideoAsset;
use Illuminate\Http\UploadedFile;

class VideoConversionEngine
{
    public function registerUpload(
        UploadedFile $file,
        string $targetDir,
        string $context,
        string $title = '',
        array $meta = []
    ): VideoAsset {
        $storedPath = $file->store(trim($targetDir, '/').'/original', 'public');

        return VideoAsset::query()->create([
            'title' => $title !== '' ? $title : ($file->getClientOriginalName() ?: 'Untitled Video'),
            'context' => $context,
            'source_path' => $storedPath,
            'status' => 'uploaded',
            'meta' => $meta,
        ]);
    }
}
