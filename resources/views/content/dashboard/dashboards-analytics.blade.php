@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard')

@section('content')
<div class="row g-6">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-primary mb-1">Production House Nusantara Dashboard</h4>
                <p class="mb-0">Monitoring data operasional studio secara real-time.</p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <p class="mb-1">Total Projects</p>
                <h4 class="mb-0">{{ number_format($stats['totalSlides']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <p class="mb-1">Active Projects</p>
                <h4 class="mb-0">{{ number_format($stats['activeSlides']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <p class="mb-1">Total Assets</p>
                <h4 class="mb-0">{{ number_format($stats['totalAssets']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <p class="mb-1">Ready Assets</p>
                <h4 class="mb-0">{{ number_format($stats['readyAssets']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <p class="mb-1">Uploaded Queue</p>
                <h4 class="mb-0">{{ number_format($stats['uploadedAssets']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <p class="mb-1">Processing Queue</p>
                <h4 class="mb-0">{{ number_format($stats['processingAssets']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <p class="mb-1">Failed Queue</p>
                <h4 class="mb-0">{{ number_format($stats['failedAssets']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <p class="mb-1">Total Users</p>
                <h4 class="mb-0">{{ number_format($stats['totalUsers']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <p class="mb-1">Portfolio Projects</p>
                <h4 class="mb-0">{{ number_format($stats['totalPortfolioProjects']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <p class="mb-1">Published Portfolio</p>
                <h4 class="mb-0">{{ number_format($stats['publishedPortfolioProjects']) }}</h4>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-9">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Activity Feed</h5>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activities as $activity)
                            <tr>
                                <td>{{ $activity['message'] }}</td>
                                <td>{{ $activity['time'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">Belum ada aktivitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Queue Conversion Monitor</h5>
                <a href="{{ route('dashboard-analytics') }}" class="btn btn-sm btn-outline-primary">Refresh</a>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Video</th>
                            <th>Context</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Path</th>
                            <th>Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($conversionAssets as $asset)
                            @php
                                $status = $asset['status'];
                                $badgeClass = match ($status) {
                                    'ready' => 'bg-label-success',
                                    'processing' => 'bg-label-info',
                                    'failed' => 'bg-label-danger',
                                    default => 'bg-label-warning',
                                };
                                $progressBarClass = match ($status) {
                                    'ready' => 'bg-success',
                                    'processing' => 'bg-info',
                                    'failed' => 'bg-danger',
                                    default => 'bg-warning',
                                };
                            @endphp
                            <tr>
                                <td>#{{ $asset['id'] }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $asset['title'] }}</div>
                                    @if (!empty($asset['error_message']))
                                        <small class="text-danger">{{ \Illuminate\Support\Str::limit($asset['error_message'], 90) }}</small>
                                    @elseif (!empty($asset['duration_seconds']))
                                        <small class="text-muted">Duration: {{ $asset['duration_seconds'] }}s</small>
                                    @endif
                                </td>
                                <td>{{ $asset['context'] }}</td>
                                <td><span class="badge {{ $badgeClass }}">{{ $status }}</span></td>
                                <td style="min-width: 180px;">
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar {{ $progressBarClass }} js-progress-value" data-progress="{{ (int) ($asset['progress'] ?? 0) }}"></div>
                                    </div>
                                    <small class="text-muted">{{ $asset['progress'] }}%</small>
                                </td>
                                <td>
                                    <small class="text-muted d-block">{{ \Illuminate\Support\Str::limit((string) $asset['source_path'], 48) }}</small>
                                    @if (!empty($asset['webm_path']))
                                        <small class="text-success d-block">{{ \Illuminate\Support\Str::limit((string) $asset['webm_path'], 48) }}</small>
                                    @endif
                                </td>
                                <td>{{ $asset['updated_at_human'] ?? '-' }}</td>
                                <td>
                                    @if ($status === 'failed')
                                        <form method="POST" action="{{ route('dashboard-media-videos.retry', ['id' => $asset['id']]) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Retry</button>
                                        </form>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">Belum ada queue video.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Projects (Auto-sync dari CMS Portfolio)</h5>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Client</th>
                            <th>Category</th>
                            <th>Year</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentProjects as $project)
                            <tr>
                                <td>{{ $project['title'] }}</td>
                                <td>{{ $project['client'] }}</td>
                                <td>{{ $project['category'] }}</td>
                                <td>{{ $project['year'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">Belum ada project portfolio.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-3">
        <div class="card h-100">
            <div class="card-body d-flex flex-column gap-3">
                <a class="btn btn-primary" href="/">Buka Landing Page</a>
                <a class="btn btn-outline-primary" href="/api/admin/dashboard" target="_blank">Lihat API Dashboard</a>
                <a class="btn btn-outline-secondary" href="/api/landing/content" target="_blank">Lihat API Landing</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    (() => {
        document.querySelectorAll('.js-progress-value').forEach((bar) => {
            if (!(bar instanceof HTMLElement)) {
                return;
            }
            const valueRaw = Number.parseInt(bar.dataset.progress || '0', 10);
            const value = Number.isNaN(valueRaw) ? 0 : Math.min(100, Math.max(0, valueRaw));
            bar.style.width = `${value}%`;
        });
    })();
</script>
@endsection
