@extends('sponsorportal::layouts.sponsor')
@section('page-title', $project->title)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('sponsor.dashboard') }}" class="btn btn-sm btn-outline-secondary me-2">
            <i class="ti ti-arrow-left"></i> {{ __('Back') }}
        </a>
        <span class="badge bg-{{ $project->status === 'Active' ? 'success' : 'secondary' }}">{{ $project->status }}</span>
    </div>
    <a href="{{ route('sponsor.report', $project->id) }}" class="btn btn-sm btn-outline-primary">
        <i class="ti ti-file-download"></i> {{ __('Download Report') }}
    </a>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm">
            <div class="card-body">
                <div class="fs-4 fw-bold text-primary">{{ number_format($milkMetrics['total_litres'] ?? 0, 0) }}</div>
                <div class="text-muted small">{{ __('Total Litres Collected') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm">
            <div class="card-body">
                <div class="fs-4 fw-bold text-success">{{ $totalFarmers }}</div>
                <div class="text-muted small">{{ __('Beneficiary Farmers') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm">
            <div class="card-body">
                <div class="fs-4 fw-bold text-info">{{ $activeFarmers }}</div>
                <div class="text-muted small">{{ __('Active Farmers') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm">
            <div class="card-body">
                <div class="fs-4 fw-bold">
                    {{ $totalFarmers > 0 ? number_format(($milkMetrics['grade_a_count'] ?? 0) / max($milkMetrics['total_count'] ?? 1, 1) * 100, 0) . '%' : '—' }}
                </div>
                <div class="text-muted small">{{ __('Grade A Rate') }}</div>
            </div>
        </div>
    </div>
</div>

@if(! empty($milkMetrics['weekly_trend']))
<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0">{{ __('7-Day Milk Collection Trend (Litres)') }}</h6></div>
    <div class="card-body">
        <canvas id="trendChart" height="80"></canvas>
    </div>
</div>
@endif

<!-- Demographics -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ __('Farmer Demographics') }}</h6></div>
            <div class="card-body">
                <div class="d-flex justify-content-around">
                    <div class="text-center">
                        <div class="fs-3 fw-bold text-primary">{{ $maleCount }}</div>
                        <div class="text-muted">{{ __('Male') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="fs-3 fw-bold text-danger">{{ $femaleCount }}</div>
                        <div class="text-muted">{{ __('Female') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ __('Project Details') }}</h6></div>
            <div class="card-body">
                <p class="mb-1"><strong>{{ __('Budget:') }}</strong> ₦{{ number_format($project->budget, 2) }}</p>
                <p class="mb-1"><strong>{{ __('Start:') }}</strong> {{ $project->start_date?->format('d M Y') ?? '—' }}</p>
                <p class="mb-0"><strong>{{ __('End:') }}</strong> {{ $project->end_date?->format('d M Y') ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@if(! empty($milkMetrics['weekly_trend']))
<script>
const ctx = document.getElementById('trendChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($milkMetrics['weekly_trend'], 'date')) !!},
        datasets: [{
            label: '{{ __('Litres') }}',
            data: {!! json_encode(array_column($milkMetrics['weekly_trend'], 'litres')) !!},
            borderColor: 'rgba(26,60,94,0.8)',
            backgroundColor: 'rgba(26,60,94,0.1)',
            fill: true,
            tension: 0.3,
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>
@endif
@endpush
