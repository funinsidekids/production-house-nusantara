<?php

return [
    'ffmpeg' => [
        'binaries' => env('FFMPEG_BINARIES', env('FFMPEG_BIN', PHP_OS_FAMILY === 'Windows' ? 'ffmpeg.exe' : '/usr/bin/ffmpeg')),
        'threads' => 12,
    ],

    'ffprobe' => [
        'binaries' => env('FFPROBE_BINARIES', env('FFPROBE_BIN', PHP_OS_FAMILY === 'Windows' ? 'ffprobe.exe' : '/usr/bin/ffprobe')),
    ],

    'timeout' => 3600,

    'log_channel' => env('LOG_CHANNEL', 'stack'),

    'temporary_files_root' => env('FFMPEG_TEMPORARY_FILES_ROOT', sys_get_temp_dir()),

    'temporary_files_encrypted_hls' => env('FFMPEG_TEMPORARY_ENCRYPTED_HLS', env('FFMPEG_TEMPORARY_FILES_ROOT', sys_get_temp_dir())),
];
