@extends('layouts.admin')

@section('page-title')
    {{ __('Executive Dashboard') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Reports') }}</li>
    <li class="breadcrumb-item">{{ __('Executive Dashboard') }}</li>
@endsection

@section('content')

{{-- ── Weekly Report Download ─────────────────────────────────────────────── --}}
@if(isset($latestWeeklyReport) && $latestWeeklyReport)
<div class="alert alert-info d-flex align-items-center justify-content-between mb-3 shadow-sm">
    <span><i class="ti ti-file-description me-1"></i> {{ __('Latest weekly report:') }} {{ $latestWeeklyReport->week_start->format('d M') }} – {{ $latestWeeklyReport->week_end->format('d M Y') }}</span>
    <a href="{{ route('reports.weekly.download', $latestWeeklyReport->id) }}" class="btn btn-sm btn-primary">
        <i class="ti ti-download me-1"></i>{{ __('Download') }}
    </a>
</div>
@endif

{{-- ── Real-time Alerts ───────────────────────────────────────────────────── --}}
@if(isset($centersBelowTarget) && count($centersBelowTarget) > 0)
<div class="alert alert-warning shadow-sm mb-3">
    <i class="ti ti-alert-triangle me-1"></i>
    <strong>{{ __('Centers with no collection today') }}:</strong> {{ implode(', ', $centersBelowTarget) }}
</div>
@endif
@if(isset($lowStockCount) && $lowStockCount > 0)
<div class="alert alert-danger shadow-sm mb-3">
    <i class="ti ti-package me-1"></i>
    <strong>{{ $lowStockCount }} {{ __('inventory item(s) at or below reorder level.') }}</strong>
    <a href="{{ route('reports.inventory') }}" class="ms-2 text-decoration-underline">{{ __('View Inventory Report') }}</a>
</div>
@endif

{{-- ── KPI Cards ──────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    {{-- Active Farmers --}}
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded p-3" style="background:#e8f4fd">
                    <i class="ti ti-users" style="font-size:1.75rem; color:#1b6ca8"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold">{{ number_format($activeFarmers) }}</h3>
                    <p class="text-muted mb-0" style="font-size:.82rem">Active Farmers</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Milk --}}
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded p-3" style="background:#fff4e0">
                    <i class="ti ti-droplet" style="font-size:1.75rem; color:#fd7e14"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold">{{ number_format($todayLitres, 0) }}<small class="fs-6 fw-normal text-muted ms-1">L</small></h3>
                    <p class="text-muted mb-0" style="font-size:.82rem">Today's Milk Collected</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Centers Operational --}}
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded p-3" style="background:#e6f9f1">
                    <i class="ti ti-building" style="font-size:1.75rem; color:#198754"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold">{{ number_format($centersOperational) }}</h3>
                    <p class="text-muted mb-0" style="font-size:.82rem">Centers Operational Today</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Financial Inclusion --}}
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded p-3" style="background:#f5e8fd">
                    <i class="ti ti-chart-pie" style="font-size:1.75rem; color:#7c3aed"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold">{{ $financialInclusion }}<small class="fs-6 fw-normal text-muted ms-1">%</small></h3>
                    <p class="text-muted mb-0" style="font-size:.82rem">Financial Inclusion Rate</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Alerts ─────────────────────────────────────────────────────────────── --}}
@if($centersBelow->isNotEmpty() || $failedPayments > 0)
    <div class="row g-3 mb-4">
        @if($failedPayments > 0)
            <div class="col-12">
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-0" style="font-size:.875rem">
                    <i class="ti ti-alert-triangle fs-5"></i>
                    <strong>Payment Alert:</strong>
                    {{ $failedPayments }} payment batch(es) have failed status. Please review the payment batches.
                    <a href="{{ route('reports.requisitions') }}" class="ms-auto btn btn-sm btn-danger">Review</a>
                </div>
            </div>
        @endif

        @if($centersBelow->isNotEmpty())
            <div class="col-12">
                <div class="alert alert-warning mb-0" style="font-size:.875rem">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="ti ti-alert-circle fs-5"></i>
                        <strong>{{ $centersBelow->count() }} center(s) below daily target of {{ number_format($dailyTarget) }}L today:</strong>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($centersBelow as $center)
                            <span class="badge bg-warning text-dark">
                                {{ $center->mcc_name }}: {{ number_format($center->today_litres, 0) }}L
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif

{{-- ── Milk Trend Chart ───────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-semibold">
                <i class="ti ti-chart-line text-warning me-2"></i>
                Milk Collection Trend — Last 30 Days (per MCC)
            </h6>
            <a href="{{ route('reports.milk') }}" class="btn btn-sm btn-outline-primary">
                Full Report <i class="ti ti-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <div class="card-body">
        <div style="position:relative; height:300px">
            <canvas id="milkTrendChart"></canvas>
        </div>
    </div>
</div>

{{-- ── Two-column tables ──────────────────────────────────────────────────── --}}
<div class="row g-4 mb-4">
    {{-- Recent Payment Batches --}}
    <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="ti ti-credit-card text-primary me-2"></i>
                    Recent Payment Batches
                </h6>
            </div>
            <div class="card-body p-0">
                @if($recentPayments->isEmpty())
                    <div class="text-center py-4 text-muted small">No payment batches found.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Batch #</th>
                                    <th>Date</th>
                                    <th class="text-end">Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPayments as $batch)
                                    <tr>
                                        <td style="font-size:.82rem">{{ $batch->batch_no ?? '#' . $batch->id }}</td>
                                        <td style="font-size:.82rem">{{ \Carbon\Carbon::parse($batch->created_at)->format('d M Y') }}</td>
                                        <td class="text-end" style="font-size:.82rem">
                                            ₦{{ number_format($batch->total_amount ?? 0, 0) }}
                                        </td>
                                        <td>
                                            @php $st = $batch->status ?? 'pending'; @endphp
                                            <span class="badge
                                                @if($st === 'paid') bg-success
                                                @elseif($st === 'approved') bg-info
                                                @elseif($st === 'failed') bg-danger
                                                @else bg-secondary
                                                @endif">
                                                {{ ucfirst($st) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Active Centers Status --}}
    <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="ti ti-building text-success me-2"></i>
                    MCC Centers — Last 7 Days
                </h6>
                <a href="{{ route('reports.centers') }}" class="btn btn-sm btn-outline-success">
                    Full Report <i class="ti ti-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                @if($activeCenters->isEmpty())
                    <div class="text-center py-4 text-muted small">No center activity in the last 7 days.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>MCC</th>
                                    <th class="text-end">7-Day Litres</th>
                                    <th class="text-end">Days Active</th>
                                    <th>Last Collection</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activeCenters as $center)
                                    <tr>
                                        <td style="font-size:.85rem">{{ $center->mcc_name }}</td>
                                        <td class="text-end fw-semibold" style="font-size:.85rem">
                                            {{ number_format($center->week_litres, 0) }}L
                                        </td>
                                        <td class="text-end" style="font-size:.85rem">{{ $center->days_active }}/7</td>
                                        <td style="font-size:.82rem; color:#6c757d">
                                            {{ \Carbon\Carbon::parse($center->last_collection)->format('d M') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Quick Links --}}
<div class="row g-3">
    @foreach([
        ['route' => 'reports.milk',         'icon' => 'ti-droplet',       'label' => 'Milk Collection',       'color' => '#fd7e14'],
        ['route' => 'reports.logistics',     'icon' => 'ti-truck',         'label' => 'Logistics',             'color' => '#1b6ca8'],
        ['route' => 'reports.centers',       'icon' => 'ti-building',      'label' => 'Center Operations',     'color' => '#198754'],
        ['route' => 'reports.requisitions',  'icon' => 'ti-file-invoice',  'label' => 'Requisitions',          'color' => '#7c3aed'],
        ['route' => 'reports.extension',     'icon' => 'ti-map',           'label' => 'Extension Services',    'color' => '#0dcaf0'],
        ['route' => 'reports.inventory',     'icon' => 'ti-package',       'label' => 'Inventory',             'color' => '#dc3545'],
        ['route' => 'reports.agent',         'icon' => 'ti-box',           'label' => 'Agent Distribution',    'color' => '#20c997'],
    ] as $link)
        <div class="col-xl col-md-4 col-6">
            <a href="{{ route($link['route']) }}" class="card border-0 shadow-sm text-decoration-none h-100">
                <div class="card-body text-center py-3">
                    <i class="ti {{ $link['icon'] }}" style="font-size:1.5rem; color:{{ $link['color'] }}"></i>
                    <p class="mb-0 mt-1 fw-semibold" style="font-size:.8rem; color:#374151">{{ $link['label'] }}</p>
                </div>
            </a>
        </div>
    @endforeach
</div>

@endsection

@section('scripts')
<script>
(function() {
    const ctx = document.getElementById('milkTrendChart');
    if (!ctx) return;

    const labels   = @json($milkDates);
    const datasets = @json($milkDatasets);

    new Chart(ctx, {
        type: 'line',
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 11 }, padding: 12, boxWidth: 14 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f0f0f0' },
                    ticks: { font: { size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 }, maxRotation: 45 }
                }
            }
        }
    });
})();
</script>
@endsection
