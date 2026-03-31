<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Models\LandingSetting;
use App\Models\User;
use App\Models\VideoAsset;
use Illuminate\Contracts\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        $totalSlides = HeroSlide::query()->count();
        $activeSlides = HeroSlide::query()->where('is_active', true)->count();
        $totalAssets = VideoAsset::query()->count();
        $readyAssets = VideoAsset::query()->where('status', 'ready')->count();
        $uploadedAssets = VideoAsset::query()->where('status', 'uploaded')->count();
        $processingAssets = VideoAsset::query()->where('status', 'processing')->count();
        $failedAssets = VideoAsset::query()->where('status', 'failed')->count();
        $totalUsers = User::query()->count();
        $portfolioPayloadRaw = (string) (LandingSetting::query()->where('key', 'cms_portfolio_payload')->value('value') ?? '');
        $portfolioPayload = json_decode($portfolioPayloadRaw, true);
        if (! is_array($portfolioPayload)) {
            $portfolioPayload = [];
        }
        $portfolioProjects = collect($portfolioPayload['projects'] ?? [])->filter(fn ($item): bool => is_array($item))->values();
        $workflow = is_array($portfolioPayload['workflow'] ?? null) ? $portfolioPayload['workflow'] : [];
        $recentLimit = max(1, min(20, (int) ($workflow['recent_projects_limit'] ?? 6)));
        $recentMode = (string) ($workflow['recent_projects_mode'] ?? 'newest');
        $totalPortfolioProjects = $portfolioProjects->count();
        $publishedPortfolioProjects = $portfolioProjects
            ->filter(fn (array $project): bool => ! (bool) ($project['is_draft'] ?? false))
            ->count();

        $recentProjects = match ($recentMode) {
            'alphabetical' => $portfolioProjects
                ->sortBy(fn (array $project): string => mb_strtolower((string) ($project['title'] ?? ''), 'UTF-8')),
            'popular' => $portfolioProjects
                ->sortByDesc(fn (array $project): int => (int) ($project['popular_score'] ?? 0)),
            default => $portfolioProjects
                ->sortByDesc(fn (array $project): int => (int) ($project['year'] ?? 0)),
        };
        $recentProjects = $recentProjects
            ->take($recentLimit)
            ->values()
            ->map(fn (array $project): array => [
                'title' => (string) ($project['title'] ?? 'Untitled Project'),
                'client' => (string) ($project['client_name'] ?? '-'),
                'category' => (string) ($project['category'] ?? '-'),
                'year' => (string) ($project['year'] ?? '-'),
                'thumbnail' => (string) ($project['featured_image'] ?? ''),
            ]);

        $activities = collect()
            ->merge(
                HeroSlide::query()
                    ->latest('updated_at')
                    ->limit(4)
                    ->get(['title', 'updated_at'])
                    ->map(fn (HeroSlide $slide): array => [
                        'message' => 'Slide updated: '.($slide->title ?: 'Untitled Slide'),
                        'time' => optional($slide->updated_at)?->diffForHumans() ?? 'recently',
                        'timestamp' => optional($slide->updated_at)?->timestamp ?? 0,
                    ])
            )
            ->merge(
                VideoAsset::query()
                    ->latest('updated_at')
                    ->limit(4)
                    ->get(['title', 'status', 'updated_at'])
                    ->map(fn (VideoAsset $asset): array => [
                        'message' => 'Asset '.$asset->status.': '.$asset->title,
                        'time' => optional($asset->updated_at)?->diffForHumans() ?? 'recently',
                        'timestamp' => optional($asset->updated_at)?->timestamp ?? 0,
                    ])
            )
            ->sortByDesc('timestamp')
            ->take(6)
            ->values()
            ->map(fn (array $activity): array => [
                'message' => $activity['message'],
                'time' => $activity['time'],
            ]);

        $conversionAssets = VideoAsset::query()
            ->latest('created_at')
            ->limit(20)
            ->get([
                'id',
                'title',
                'context',
                'status',
                'source_path',
                'webm_path',
                'duration_seconds',
                'error_message',
                'created_at',
                'updated_at',
            ])
            ->map(function (VideoAsset $asset): array {
                $status = (string) ($asset->status ?? 'uploaded');
                $progress = match ($status) {
                    'ready' => 100,
                    'failed' => 100,
                    'processing' => 65,
                    default => 20,
                };

                return [
                    'id' => $asset->id,
                    'title' => $asset->title,
                    'context' => (string) ($asset->context ?? 'media'),
                    'status' => $status,
                    'progress' => $progress,
                    'source_path' => $asset->source_path,
                    'webm_path' => $asset->webm_path,
                    'duration_seconds' => $asset->duration_seconds,
                    'error_message' => $asset->error_message,
                    'created_at' => optional($asset->created_at)?->toDateTimeString(),
                    'updated_at_human' => optional($asset->updated_at)?->diffForHumans(),
                ];
            })
            ->values();

        return view('content.dashboard.dashboards-analytics', [
            'stats' => [
                'totalSlides' => $totalSlides,
                'activeSlides' => $activeSlides,
                'totalAssets' => $totalAssets,
                'readyAssets' => $readyAssets,
                'uploadedAssets' => $uploadedAssets,
                'processingAssets' => $processingAssets,
                'failedAssets' => $failedAssets,
                'totalUsers' => $totalUsers,
                'totalPortfolioProjects' => $totalPortfolioProjects,
                'publishedPortfolioProjects' => $publishedPortfolioProjects,
            ],
            'activities' => $activities,
            'recentProjects' => $recentProjects,
            'conversionAssets' => $conversionAssets,
        ]);
    }
}
