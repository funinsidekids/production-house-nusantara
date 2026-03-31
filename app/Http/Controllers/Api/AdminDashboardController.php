<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Models\VideoAsset;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalSlides = HeroSlide::query()->count();
        $activeSlides = HeroSlide::query()->where('is_active', true)->count();
        $totalAssets = VideoAsset::query()->count();
        $readyAssets = VideoAsset::query()->where('status', 'ready')->count();

        $year = (int) now()->year;
        $slideMonthly = $this->buildMonthlyCounts($year, HeroSlide::query());
        $assetMonthly = $this->buildMonthlyCounts($year, VideoAsset::query());
        $readyMonthly = $this->buildMonthlyCounts($year, VideoAsset::query()->where('status', 'ready'));

        $revenueSeries = collect(range(1, 12))
            ->map(fn (int $month): array => [
                'month' => Carbon::create()->month($month)->format('M'),
                'slides' => (int) ($slideMonthly[$month] ?? 0),
                'assets' => (int) ($assetMonthly[$month] ?? 0),
                'ready' => (int) ($readyMonthly[$month] ?? 0),
            ])
            ->all();

        $nowMonth = (int) now()->month;
        $prevMonth = $nowMonth === 1 ? 12 : $nowMonth - 1;

        $slideTrend = $this->percentDelta((int) ($slideMonthly[$nowMonth] ?? 0), (int) ($slideMonthly[$prevMonth] ?? 0));
        $assetTrend = $this->percentDelta((int) ($assetMonthly[$nowMonth] ?? 0), (int) ($assetMonthly[$prevMonth] ?? 0));
        $activeTrend = $this->percentDelta($activeSlides, max(1, $totalSlides - $activeSlides));
        $readyTrend = $this->percentDelta((int) ($readyMonthly[$nowMonth] ?? 0), (int) ($readyMonthly[$prevMonth] ?? 0));

        $slideActivities = HeroSlide::query()
            ->latest('updated_at')
            ->limit(3)
            ->get(['title', 'updated_at'])
            ->map(fn (HeroSlide $slide): array => [
                'message' => 'Slide updated: '.($slide->title ?: 'Untitled Slide'),
                'time' => optional($slide->updated_at)?->diffForHumans() ?? 'recently',
                'timestamp' => optional($slide->updated_at)?->timestamp ?? 0,
            ]);

        $assetActivities = VideoAsset::query()
            ->latest('updated_at')
            ->limit(3)
            ->get(['title', 'status', 'updated_at'])
            ->map(fn (VideoAsset $asset): array => [
                'message' => 'Video asset '.$asset->status.': '.$asset->title,
                'time' => optional($asset->updated_at)?->diffForHumans() ?? 'recently',
                'timestamp' => optional($asset->updated_at)?->timestamp ?? 0,
            ]);

        $activities = $slideActivities
            ->merge($assetActivities)
            ->sortByDesc(fn (array $item): int => $item['timestamp'])
            ->take(6)
            ->map(fn (array $item): array => [
                'message' => $item['message'],
                'time' => $item['time'],
            ])
            ->values()
            ->all();

        return response()->json([
            'kpi' => [
                'totalSlides' => $totalSlides,
                'totalAssets' => $totalAssets,
                'activeSlides' => $activeSlides,
                'readyAssets' => $readyAssets,
            ],
            'kpiTrends' => [
                'totalSlides' => $slideTrend,
                'totalAssets' => $assetTrend,
                'activeSlides' => $activeTrend,
                'readyAssets' => $readyTrend,
            ],
            'revenueSeries' => $revenueSeries,
            'activities' => $activities,
        ]);
    }

    private function percentDelta(int $current, int $previous): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function buildMonthlyCounts(int $year, Builder $query): array
    {
        return collect(range(1, 12))
            ->mapWithKeys(function (int $month) use ($year, $query): array {
                $start = Carbon::create($year, $month, 1)->startOfDay();
                $end = (clone $start)->endOfMonth()->endOfDay();
                $count = (clone $query)
                    ->whereBetween('created_at', [$start, $end])
                    ->count();

                return [$month => $count];
            })
            ->all();
    }
}
