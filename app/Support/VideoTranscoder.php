<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class VideoTranscoder
{
    public function transcodeToWebm(UploadedFile $file, string $targetDir, string $profileKey = 'default'): string
    {
        $targetDir = trim($targetDir, '/');
        $disk = Storage::disk('public');
        $inputExtension = strtolower($file->getClientOriginalExtension() ?: 'video');
        $tempInputRelative = 'tmp/video-transcode/'.Str::uuid().'.'.$inputExtension;
        $tempInputPath = Storage::disk('local')->path($tempInputRelative);
        if (! is_dir(dirname($tempInputPath))) {
            mkdir(dirname($tempInputPath), 0777, true);
        }
        copy($file->getRealPath(), $tempInputPath);

        $outputRelative = $targetDir.'/'.Str::uuid().'.webm';
        $outputPath = $disk->path($outputRelative);
        if (! is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0777, true);
        }

        try {
            $profile = $this->resolveProfile($profileKey);
            $this->prepareRuntime($profile);
            $this->convert($tempInputPath, $outputPath, $profile);
        } catch (Throwable $e) {
            if (is_file($tempInputPath)) {
                @unlink($tempInputPath);
            }
            if (is_file($outputPath)) {
                @unlink($outputPath);
            }
            throw new RuntimeException('Gagal konversi video ke WebM. Pastikan engine transcode tersedia di server.', 0, $e);
        }

        if (is_file($tempInputPath)) {
            @unlink($tempInputPath);
        }

        return $outputRelative;
    }

    private function convert(string $inputPath, string $outputPath, array $profile): void
    {
        $engine = (string) config('media.video_transcode.engine', 'ffmpeg');
        $codec = (string) ($profile['codec'] ?? config('media.video_transcode.codec', 'vp9'));

        if ($engine === 'gstreamer') {
            if ($this->runGstreamer($inputPath, $outputPath, $profile)) {
                return;
            }
            throw new RuntimeException('GStreamer conversion failed.');
        }

        if ($engine === 'handbrake') {
            if ($this->runHandBrake($inputPath, $outputPath, $codec, $profile)) {
                return;
            }
            throw new RuntimeException('HandBrake conversion failed.');
        }

        if ($this->runFfmpeg($inputPath, $outputPath, $codec, $profile)) {
            return;
        }

        throw new RuntimeException('FFmpeg conversion failed.');
    }

    private function runFfmpeg(string $inputPath, string $outputPath, string $codec, array $profile): bool
    {
        $ffmpeg = (string) config('media.ffmpeg', 'ffmpeg');
        if (! $this->binaryExists($ffmpeg)) {
            return false;
        }

        $crf = (int) ($profile['crf'] ?? 31);
        $bitrate = (string) ($profile['video_bitrate'] ?? '0');
        $audioBitrate = (string) ($profile['audio_bitrate'] ?? '128k');
        $cpuUsed = (int) ($profile['cpu_used'] ?? 1);
        $deadline = (string) ($profile['ffmpeg_deadline'] ?? 'good');
        if (! in_array($deadline, ['best', 'good', 'realtime'], true)) {
            $deadline = 'good';
        }
        $threads = max(0, (int) ($profile['ffmpeg_threads'] ?? 0));
        $codecArgs = match ($codec) {
            'av1' => sprintf('-c:v libaom-av1 -crf %d -b:v %s -cpu-used %d -row-mt 1 -tiles 2x2', $crf, escapeshellarg($bitrate), max(0, min(8, $cpuUsed))),
            'vp8' => sprintf('-c:v libvpx -crf %d -b:v %s', $crf, escapeshellarg($bitrate === '0' ? '1M' : $bitrate)),
            default => sprintf('-c:v libvpx-vp9 -b:v %s -crf %d -row-mt 1 -deadline %s -cpu-used %d -threads %d', escapeshellarg($bitrate), $crf, $deadline, max(0, min(8, $cpuUsed)), $threads),
        };

        $command = sprintf(
            '%s -y -i %s %s -c:a libopus -b:a %s %s',
            escapeshellarg($ffmpeg),
            escapeshellarg($inputPath),
            $codecArgs,
            escapeshellarg($audioBitrate),
            escapeshellarg($outputPath)
        );

        return $this->runCommand($command, (int) ($profile['process_timeout_seconds'] ?? 0));
    }

    private function runGstreamer(string $inputPath, string $outputPath, array $profile): bool
    {
        $binary = (string) config('media.video_transcode.gstreamer_bin', 'gst-launch-1.0');
        if (! $this->binaryExists($binary)) {
            return false;
        }
        $deadline = (int) ($profile['gstreamer_deadline'] ?? 1);
        $cpuUsed = (int) ($profile['gstreamer_cpu_used'] ?? 2);

        $command = sprintf(
            '%s -e filesrc location=%s ! decodebin name=d d. ! videoconvert ! vp9enc deadline=%d cpu-used=%d ! webmmux name=mux ! filesink location=%s d. ! audioconvert ! audioresample ! opusenc ! mux.',
            escapeshellarg($binary),
            escapeshellarg($inputPath),
            max(1, min(8, $deadline)),
            max(0, min(16, $cpuUsed)),
            escapeshellarg($outputPath)
        );

        return $this->runCommand($command, (int) ($profile['process_timeout_seconds'] ?? 0));
    }

    private function runHandBrake(string $inputPath, string $outputPath, string $codec, array $profile): bool
    {
        $binary = (string) config('media.video_transcode.handbrake_bin', 'HandBrakeCLI');
        if (! $this->binaryExists($binary)) {
            return false;
        }
        $encoder = $codec === 'av1' ? 'av1' : 'VP9';
        $quality = (int) ($profile['handbrake_quality'] ?? 28);
        $command = sprintf(
            '%s -i %s -o %s -f av_webm -e %s -q %d --aencoder opus --mixdown stereo',
            escapeshellarg($binary),
            escapeshellarg($inputPath),
            escapeshellarg($outputPath),
            escapeshellarg($encoder),
            max(10, min(40, $quality))
        );

        return $this->runCommand($command, (int) ($profile['process_timeout_seconds'] ?? 0));
    }

    private function resolveProfile(string $profileKey): array
    {
        $profiles = config('media.video_transcode.profiles', []);
        $defaultProfile = is_array($profiles['default'] ?? null) ? $profiles['default'] : [];
        $contextProfile = is_array($profiles[$profileKey] ?? null) ? $profiles[$profileKey] : [];

        return array_merge($defaultProfile, $contextProfile);
    }

    private function runCommand(string $command, int $timeoutSeconds = 0): bool
    {
        if (function_exists('proc_open')) {
            $descriptorSpec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];
            $process = proc_open($command, $descriptorSpec, $pipes);
            if (is_resource($process)) {
                fclose($pipes[0]);
                stream_set_blocking($pipes[1], false);
                stream_set_blocking($pipes[2], false);
                $startedAt = microtime(true);
                while (true) {
                    $status = proc_get_status($process);
                    if (! $status['running']) {
                        break;
                    }
                    if ($timeoutSeconds > 0 && (microtime(true) - $startedAt) > $timeoutSeconds) {
                        proc_terminate($process);
                        fclose($pipes[1]);
                        fclose($pipes[2]);
                        proc_close($process);
                        throw new RuntimeException('Konversi video timeout.');
                    }
                    usleep(150000);
                }
                fclose($pipes[1]);
                fclose($pipes[2]);
                $exitCode = proc_close($process);

                return $exitCode === 0;
            }
        }

        $output = [];
        $status = 1;
        exec($command.' 2>&1', $output, $status);

        return $status === 0;
    }

    private function prepareRuntime(array $profile): void
    {
        $maxExecution = (int) ($profile['max_execution_time'] ?? 0);
        if ($maxExecution <= 0) {
            @set_time_limit(0);
            @ini_set('max_execution_time', '0');
        } else {
            @set_time_limit($maxExecution);
            @ini_set('max_execution_time', (string) $maxExecution);
        }
        if ((int) ini_get('max_input_time') > 0) {
            @ini_set('max_input_time', '-1');
        }
        if ((int) ini_get('default_socket_timeout') > 0 && (int) ini_get('default_socket_timeout') < 600) {
            @ini_set('default_socket_timeout', '600');
        }
    }

    private function binaryExists(string $binary): bool
    {
        $checkCommand = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? 'where '.escapeshellarg($binary)
            : 'command -v '.escapeshellarg($binary);
        $output = [];
        $status = 1;
        exec($checkCommand.' 2>&1', $output, $status);

        return $status === 0;
    }
}
