<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LandingSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebsiteCmsLogoController extends Controller
{
    public function index(): View
    {
        $settings = LandingSetting::query()->pluck('value', 'key');

        return view('content.dashboard.website-cms-logo', [
            'form' => [
                'logo_primary' => (string) ($settings['cms_logo_primary'] ?? ''),
                'logo_primary_2x' => (string) ($settings['cms_logo_primary_2x'] ?? ''),
                'logo_primary_3x' => (string) ($settings['cms_logo_primary_3x'] ?? ''),
                'logo_secondary_light' => (string) ($settings['cms_logo_secondary_light'] ?? ''),
                'logo_secondary_dark' => (string) ($settings['cms_logo_secondary_dark'] ?? ''),
                'logo_monochrome' => (string) ($settings['cms_logo_monochrome'] ?? ''),
                'logo_favicon_32' => (string) ($settings['cms_logo_favicon_32'] ?? ''),
                'logo_apple_touch' => (string) ($settings['cms_logo_apple_touch'] ?? ''),
                'logo_loading_svg' => (string) ($settings['cms_logo_loading_svg'] ?? ''),
                'logo_sticky' => (string) ($settings['cms_logo_sticky'] ?? ''),
                'logo_footer' => (string) ($settings['cms_logo_footer'] ?? ''),
                'header_position' => (string) ($settings['cms_logo_header_position'] ?? 'left'),
                'sticky_variant' => (string) ($settings['cms_logo_sticky_variant'] ?? 'primary'),
                'footer_variant' => (string) ($settings['cms_logo_footer_variant'] ?? 'primary'),
                'preview_offset_x' => (string) ($settings['cms_logo_preview_offset_x'] ?? '50'),
                'preview_offset_y' => (string) ($settings['cms_logo_preview_offset_y'] ?? '50'),
                'preview_scale' => (string) ($settings['cms_logo_preview_scale'] ?? '1'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'primary_logo' => ['nullable', 'file', 'mimes:png,svg,jpg,jpeg,webp', 'max:4096'],
            'secondary_logo_light' => ['nullable', 'file', 'mimes:png,svg,jpg,jpeg,webp', 'max:4096'],
            'secondary_logo_dark' => ['nullable', 'file', 'mimes:png,svg,jpg,jpeg,webp', 'max:4096'],
            'monochrome_logo' => ['nullable', 'file', 'mimes:png,svg,jpg,jpeg,webp', 'max:4096'],
            'favicon_32' => ['nullable', 'file', 'mimes:png,ico', 'max:2048'],
            'apple_touch_icon' => ['nullable', 'file', 'mimes:png', 'max:4096'],
            'loading_logo' => ['nullable', 'file', 'mimes:svg,png,webp,gif', 'max:4096'],
            'sticky_logo' => ['nullable', 'file', 'mimes:png,svg,jpg,jpeg,webp', 'max:4096'],
            'footer_logo' => ['nullable', 'file', 'mimes:png,svg,jpg,jpeg,webp', 'max:4096'],
            'header_position' => ['required', 'in:left,center,right'],
            'sticky_variant' => ['required', 'in:primary,secondary-light,secondary-dark,sticky'],
            'footer_variant' => ['required', 'in:primary,secondary-light,secondary-dark,monochrome,footer'],
            'preview_offset_x' => ['required', 'integer', 'min:0', 'max:100'],
            'preview_offset_y' => ['required', 'integer', 'min:0', 'max:100'],
            'preview_scale' => ['required', 'numeric', 'min:0.6', 'max:2'],
        ]);

        $settings = LandingSetting::query()->pluck('value', 'key');

        $primary = $this->persistAsset($request->file('primary_logo'), (string) ($settings['cms_logo_primary'] ?? ''), 'cms/logo', true);
        $secondaryLight = $this->persistAsset($request->file('secondary_logo_light'), (string) ($settings['cms_logo_secondary_light'] ?? ''), 'cms/logo', true);
        $secondaryDark = $this->persistAsset($request->file('secondary_logo_dark'), (string) ($settings['cms_logo_secondary_dark'] ?? ''), 'cms/logo', true);
        $monochrome = $this->persistAsset($request->file('monochrome_logo'), (string) ($settings['cms_logo_monochrome'] ?? ''), 'cms/logo', true);
        $favicon = $this->persistAsset($request->file('favicon_32'), (string) ($settings['cms_logo_favicon_32'] ?? ''), 'cms/logo', true);
        $appleTouch = $this->persistAsset($request->file('apple_touch_icon'), (string) ($settings['cms_logo_apple_touch'] ?? ''), 'cms/logo', true);
        $loadingLogo = $this->persistAsset($request->file('loading_logo'), (string) ($settings['cms_logo_loading_svg'] ?? ''), 'cms/logo', false);
        $stickyLogo = $this->persistAsset($request->file('sticky_logo'), (string) ($settings['cms_logo_sticky'] ?? ''), 'cms/logo', true);
        $footerLogo = $this->persistAsset($request->file('footer_logo'), (string) ($settings['cms_logo_footer'] ?? ''), 'cms/logo', true);

        $retina2x = (string) ($settings['cms_logo_primary_2x'] ?? '');
        $retina3x = (string) ($settings['cms_logo_primary_3x'] ?? '');
        if ($request->hasFile('primary_logo') && $primary !== '') {
            $retina = $this->createRetinaVariants($primary, 'cms/logo');
            $retina2x = $retina['2x'];
            $retina3x = $retina['3x'];
        }

        $payload = [
            'cms_logo_primary' => $primary,
            'cms_logo_primary_2x' => $retina2x,
            'cms_logo_primary_3x' => $retina3x,
            'cms_logo_secondary_light' => $secondaryLight,
            'cms_logo_secondary_dark' => $secondaryDark,
            'cms_logo_monochrome' => $monochrome,
            'cms_logo_favicon_32' => $favicon,
            'cms_logo_apple_touch' => $appleTouch,
            'cms_logo_loading_svg' => $loadingLogo,
            'cms_logo_sticky' => $stickyLogo,
            'cms_logo_footer' => $footerLogo,
            'cms_logo_header_position' => $data['header_position'],
            'cms_logo_sticky_variant' => $data['sticky_variant'],
            'cms_logo_footer_variant' => $data['footer_variant'],
            'cms_logo_preview_offset_x' => (string) $data['preview_offset_x'],
            'cms_logo_preview_offset_y' => (string) $data['preview_offset_y'],
            'cms_logo_preview_scale' => (string) $data['preview_scale'],
        ];

        foreach ($payload as $key => $value) {
            LandingSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()
            ->route('dashboard-website-cms-logo')
            ->with('success', 'Website CMS Logo berhasil diperbarui.');
    }

    private function persistAsset(?UploadedFile $file, string $oldPath, string $dir, bool $optimize): string
    {
        if (! $file) {
            return $oldPath;
        }

        if ($oldPath !== '' && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $fileName = Str::uuid()->toString().'.'.$extension;
        $storedPath = $file->storeAs($dir, $fileName, 'public');

        if ($optimize) {
            $this->optimizeImage(Storage::disk('public')->path($storedPath), $extension);
        }

        return $storedPath;
    }

    private function createRetinaVariants(string $path, string $dir): array
    {
        $absolutePath = Storage::disk('public')->path($path);
        if (! file_exists($absolutePath)) {
            return ['2x' => '', '3x' => ''];
        }

        [$width, $height] = getimagesize($absolutePath) ?: [0, 0];
        if ($width <= 0 || $height <= 0) {
            return ['2x' => '', '3x' => ''];
        }

        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        if (! in_array($extension, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            return ['2x' => '', '3x' => ''];
        }

        $target2x = [400, 120];
        $target3x = [600, 180];
        $baseName = Str::uuid()->toString();
        $path2x = $dir.'/'.$baseName.'@2x.'.$extension;
        $path3x = $dir.'/'.$baseName.'@3x.'.$extension;
        $absolute2x = Storage::disk('public')->path($path2x);
        $absolute3x = Storage::disk('public')->path($path3x);

        $this->resizeImage($absolutePath, $absolute2x, $extension, $target2x[0], $target2x[1]);
        $this->resizeImage($absolutePath, $absolute3x, $extension, $target3x[0], $target3x[1]);

        return ['2x' => $path2x, '3x' => $path3x];
    }

    private function optimizeImage(string $path, string $extension): void
    {
        if (! extension_loaded('gd') || ! file_exists($path)) {
            return;
        }

        if (in_array($extension, ['jpg', 'jpeg'], true)) {
            $resource = imagecreatefromjpeg($path);
            if ($resource !== false) {
                imagejpeg($resource, $path, 82);
            }
        }

        if ($extension === 'png') {
            $resource = imagecreatefrompng($path);
            if ($resource !== false) {
                imagealphablending($resource, false);
                imagesavealpha($resource, true);
                imagepng($resource, $path, 6);
            }
        }

        if ($extension === 'webp' && function_exists('imagecreatefromwebp')) {
            $resource = imagecreatefromwebp($path);
            if ($resource !== false) {
                imagewebp($resource, $path, 82);
            }
        }
    }

    private function resizeImage(string $source, string $destination, string $extension, int $targetWidth, int $targetHeight): void
    {
        if (! extension_loaded('gd')) {
            return;
        }

        $srcResource = match ($extension) {
            'png' => imagecreatefrompng($source),
            'jpg', 'jpeg' => imagecreatefromjpeg($source),
            'webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($source) : false,
            default => false,
        };

        if ($srcResource === false) {
            return;
        }

        [$srcWidth, $srcHeight] = getimagesize($source) ?: [0, 0];
        if ($srcWidth <= 0 || $srcHeight <= 0) {
            return;
        }

        $dstResource = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($dstResource === false) {
            return;
        }

        if ($extension === 'png' || $extension === 'webp') {
            imagealphablending($dstResource, false);
            imagesavealpha($dstResource, true);
            $transparent = imagecolorallocatealpha($dstResource, 0, 0, 0, 127);
            imagefilledrectangle($dstResource, 0, 0, $targetWidth, $targetHeight, $transparent);
        }

        imagecopyresampled($dstResource, $srcResource, 0, 0, 0, 0, $targetWidth, $targetHeight, $srcWidth, $srcHeight);

        if ($extension === 'png') {
            imagepng($dstResource, $destination, 6);
        } elseif (in_array($extension, ['jpg', 'jpeg'], true)) {
            imagejpeg($dstResource, $destination, 82);
        } elseif ($extension === 'webp' && function_exists('imagewebp')) {
            imagewebp($dstResource, $destination, 82);
        }

    }
}
