@extends('layouts.admin')
@section('page-title'){{ __('Finance Dashboard') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Finance') }}</li>
@endsection

@push('css-page')
<style>
.finance-kpi .card-body { padding: 1.25rem; }
.ageing-bar { height: 8px; border-radius: 4px; }
</style>
@endpush

@section('content')
{{-- ── KPI Row ─────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <div class="card finance-kpi border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 rounded p-3"><i class="ti ti-building-bank text-primary fs-4"></i></div>
                <div>
                    <p class="text-muted mb-0 small">{{ __('Cash Position') }}</p>
                    <h5 class="mb-0">{{ \Auth::user()->priceFormat($totalCash) }}</h5>
                    <small class="text-muted">{{ $bankAccounts->count() }} {{ __('accounts') }}</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card finance-kpi border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 rounded p-3"><i class="ti ti-file-invoice text-success fs-4"></i></div>
                <div>
                    <p class="text-muted mb-0 small">{{ __('Accounts Receivable') }}</p>
                    <h5 class="mb-0">{{ \Auth::user()->priceFormat($totalAR) }}</h5>
                    <small class="text-danger">{{ \Auth::user()->priceFormat($overdueAR) }} {{ __('overdue') }}</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card finance-kpi border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-warning bg-opacity-10 rounded p-3"><i class="ti ti-receipt text-warning fs-4"></i></div>
                <div>
                    <p class="text-muted mb-0 small">{{ __('Accounts Payable') }}</p>
                    <h5 class="mb-0">{{ \Auth::user()->priceFormat($totalAP) }}</h5>
                    <small class="text-danger">{{ \Auth::user()->priceFormat($overdueAP) }} {{ __('overdue') }}</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card finance-kpi border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-info bg-opacity-10 rounded p-3"><i class="ti ti-clipboard-list text-info fs-4"></i></div>
                <div>
                    <p class="text-muted mb-0 small">{{ __('Pending Claims / Recon') }}</p>
                    <h5 class="mb-0">{{ $pendingClaims }}</h5>
                    <small class="text-muted">{{ $openRecon }} {{ __('open reconciliations') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Revenue vs Expense Chart & AR Ageing ────────────────────────────── --}}
<div class="row g-3 mb-3">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="mb-0">{{ __('Revenue vs Expenses (Last 6 Months)') }}</h6>
            </div>
            <div class="card-body">
                <div id="rev-exp-chart" style="min-height:260px;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="mb-0">{{ __('AR Ageing') }}</h6>
            </div>
            <div class="card-body">
                @php
                    $arTotal = array_sum($arAgeing) ?: 1;
                    $arLabels = ['current'=>'Current','1_30'=>'1-30 days','31_60'=>'31-60 days','61_90'=>'61-90 days','over_90'=>'90+ days'];
                    $arColors = ['current'=>'bg-success','1_30'=>'bg-warning','31_60'=>'bg-orange','61_90'=>'bg-danger','over_90'=>'bg-dark'];
                @endphp
                @foreach($arAgeing as $key => $amount)
                @if($amount > 0)
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small>{{ $arLabels[$key] }}</small>
                        <small class="fw-semibold">{{ \Auth::user()->priceFormat($amount) }}</small>
                    </div>
                    <div class="progress ageing-bar">
                        <div class="progress-bar {{ $arColors[$key] }}" style="width:{{ ($amount/$arTotal)*100 }}%"></div>
                    </div>
                </div>
                @endif
                @endforeach
                <div class="mt-3 pt-2 border-top d-flex justify-content-between">
                    <small class="text-muted">{{ __('Total AR') }}</small>
                    <strong>{{ \Auth::user()->priceFormat($totalAR) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── AP Ageing & Bank Accounts ────────────────────────────────────────── --}}
<div class="row g-3 mb-3">
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="mb-0">{{ __('AP Ageing') }}</h6>
            </div>
            <div class="card-body">
                @php $apTotal = array_sum($apAgeing) ?: 1; @endphp
                @foreach($apAgeing as $key => $amount)
                @if($amount > 0)
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small>{{ $arLabels[$key] }}</small>
                        <small class="fw-semibold">{{ \Auth::user()->priceFormat($amount) }}</small>
                    </div>
                    <div class="progress ageing-bar">
                        <div class="progress-bar {{ $arColors[$key] }}" style="width:{{ ($amount/$apTotal)*100 }}%"></div>
                    </div>
                </div>
                @endif
                @endforeach
                <div class="mt-3 pt-2 border-top d-flex justify-content-between">
                    <small class="text-muted">{{ __('Total AP') }}</small>
                    <strong>{{ \Auth::user()->priceFormat($totalAP) }}</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between">
                <h6 class="mb-0">{{ __('Bank Accounts') }}</h6>
                <a href="{{ route('bank-account.index') }}" class="btn btn-sm btn-outline-primary">{{ __('Manage') }}</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr>
                            <th>{{ __('Account') }}</th>
                            <th>{{ __('Bank') }}</th>
                            <th class="text-end">{{ __('Balance') }}</th>
                        </tr></thead>
                        <tbody>
                        @forelse($bankAccounts as $acct)
                        <tr>
                            <td>{{ $acct->holder_name }}<br><small class="text-muted">{{ $acct->account_number }}</small></td>
                            <td>{{ $acct->bank_name }}</td>
                            <td class="text-end fw-semibold">{{ \Auth::user()->priceFormat($acct->opening_balance) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted">{{ __('No bank accounts configured.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Quick Actions & Recent Activity ─────────────────────────────────── --}}
<div class="row g-3 mb-3">
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pb-0"><h6 class="mb-0">{{ __('Quick Actions') }}</h6></div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('invoice.create', 0) }}"            class="btn btn-outline-success btn-sm text-start"><i class="ti ti-plus me-2"></i>{{ __('New Invoice') }}</a>
                <a href="{{ route('bill.create') }}"                 class="btn btn-outline-warning btn-sm text-start"><i class="ti ti-plus me-2"></i>{{ __('New Bill') }}</a>
                <a href="{{ route('revenue.create') }}"              class="btn btn-outline-primary btn-sm text-start"><i class="ti ti-plus me-2"></i>{{ __('Record Revenue') }}</a>
                <a href="{{ route('payment.create') }}"              class="btn btn-outline-danger btn-sm text-start"><i class="ti ti-plus me-2"></i>{{ __('Record Payment') }}</a>
                <a href="{{ route('journal-entry.create') }}"        class="btn btn-outline-secondary btn-sm text-start"><i class="ti ti-book me-2"></i>{{ __('Journal Entry') }}</a>
                <a href="{{ route('accounting.expense-claims.create') }}" class="btn btn-outline-info btn-sm text-start"><i class="ti ti-receipt-2 me-2"></i>{{ __('Expense Claim') }}</a>
                <a href="{{ route('accounting.reconciliation.create') }}" class="btn btn-outline-dark btn-sm text-start"><i class="ti ti-adjustments me-2"></i>{{ __('Bank Reconciliation') }}</a>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between">
                <h6 class="mb-0">{{ __('Recent Revenue') }}</h6>
                <a href="{{ route('revenue.index') }}" class="small">{{ __('View all') }}</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                @forelse($recentRevenues as $rev)
                <li class="list-group-item d-flex justify-content-between align-items-start px-3">
                    <div>
                        <p class="mb-0 small fw-semibold">{{ $rev->customer->name ?? '—' }}</p>
                        <small class="text-muted">{{ $rev->date }} · {{ $rev->payment_method }}</small>
                    </div>
                    <span class="badge bg-success-subtle text-success">{{ \Auth::user()->priceFormat($rev->amount) }}</span>
                </li>
                @empty
                <li class="list-group-item text-center text-muted small">{{ __('No recent revenue.') }}</li>
                @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between">
                <h6 class="mb-0">{{ __('Recent Payments') }}</h6>
                <a href="{{ route('payment.index') }}" class="small">{{ __('View all') }}</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                @forelse($recentPayments as $pay)
                <li class="list-group-item d-flex justify-content-between align-items-start px-3">
                    <div>
                        <p class="mb-0 small fw-semibold">{{ $pay->vender->name ?? '—' }}</p>
                        <small class="text-muted">{{ $pay->date }} · {{ $pay->payment_method }}</small>
                    </div>
                    <span class="badge bg-danger-subtle text-danger">{{ \Auth::user()->priceFormat($pay->amount) }}</span>
                </li>
                @empty
                <li class="list-group-item text-center text-muted small">{{ __('No recent payments.') }}</li>
                @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- ── Active Budget ─────────────────────────────────────────────────────── --}}
@if($activeBudget)
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">{{ __('Active Budget') }}: {{ $activeBudget->name }} ({{ $activeBudget->fiscal_year }})</h6>
        <a href="{{ route('accounting.budget.show', $activeBudget->id) }}" class="btn btn-sm btn-outline-primary">{{ __('Full View') }}</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr>
                    <th>{{ __('Account') }}</th>
                    <th class="text-end">{{ __('Budgeted') }}</th>
                    <th class="text-end">{{ __('Actual') }}</th>
                    <th class="text-end">{{ __('Variance') }}</th>
                    <th style="width:140px">{{ __('Progress') }}</th>
                </tr></thead>
                <tbody>
                @foreach($activeBudget->lines->take(8) as $line)
                @php
                    $bud = $line->annualBudget();
                    $act = $line->actualSpend();
                    $var = $bud - $act;
                    $pct = $bud > 0 ? min(($act/$bud)*100, 100) : 0;
                @endphp
                <tr>
                    <td>{{ $line->chartAccount->code ?? '' }} {{ $line->chartAccount->name ?? '—' }}</td>
                    <td class="text-end">{{ \Auth::user()->priceFormat($bud) }}</td>
                    <td class="text-end">{{ \Auth::user()->priceFormat($act) }}</td>
                    <td class="text-end {{ $var >= 0 ? 'text-success' : 'text-danger' }}">{{ \Auth::user()->priceFormat(abs($var)) }}</td>
                    <td>
                        <div class="progress" style="height:6px;">
                            <div class="progress-bar {{ $pct > 90 ? 'bg-danger' : ($pct > 70 ? 'bg-warning' : 'bg-success') }}" style="width:{{ $pct }}%"></div>
                        </div>
                        <small class="text-muted">{{ number_format($pct, 0) }}%</small>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ── Financial Report Links ───────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pb-0"><h6 class="mb-0">{{ __('Financial Reports') }}</h6></div>
    <div class="card-body">
        <div class="row g-2">
            @foreach([
                [route('report.profit.loss'),          'ti-trending-up',    'success', 'Profit & Loss'],
                [route('report.balance.sheet'),        'ti-scale',          'primary', 'Balance Sheet'],
                [route('trial.balance'),               'ti-list-check',     'info',    'Trial Balance'],
                [route('report.receivables'),          'ti-arrow-down-circle','success','Receivables'],
                [route('report.payables'),             'ti-arrow-up-circle', 'danger', 'Payables'],
                [route('report.invoice.summary'),      'ti-file-invoice',   'warning', 'Invoice Summary'],
                [route('report.bill.summary'),         'ti-receipt',        'dark',    'Bill Summary'],
                [route('report.income.vs.expense.summary'),'ti-chart-bar',  'secondary','Income VS Expense'],
            ] as [$url, $icon, $color, $label])
            <div class="col-6 col-md-3">
                <a href="{{ $url }}" class="card text-decoration-none border-0 bg-{{ $color }}-subtle h-100">
                    <div class="card-body text-center py-3">
                        <i class="ti {{ $icon }} text-{{ $color }} fs-4 d-block mb-1"></i>
                        <small class="text-{{ $color }} fw-semibold">{{ __($label) }}</small>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('script-page')
<script src="{{ asset('js/plugins/apexcharts.min.js') }}"></script>
<script>
const months  = @json(array_keys($revenueChart));
const revenue = @json(array_values($revenueChart));
const expense = @json(array_values($expenseChart));

new ApexCharts(document.querySelector('#rev-exp-chart'), {
    chart: { type: 'bar', height: 260, toolbar: { show: false } },
    series: [
        { name: '{{ __("Revenue") }}', data: revenue },
        { name: '{{ __("Expenses") }}', data: expense },
    ],
    xaxis: { categories: months },
    colors: ['#6fd944', '#ff3a6e'],
    plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
    dataLabels: { enabled: false },
    legend: { position: 'top' },
    yaxis: { labels: { formatter: v => v.toLocaleString() } },
}).render();
</script>
@endpush
