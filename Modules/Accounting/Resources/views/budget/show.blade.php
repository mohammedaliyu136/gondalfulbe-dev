@extends('layouts.admin')
@section('page-title'){{ $budget->name }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accounting.dashboard') }}">{{ __('Finance') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('accounting.budget.index') }}">{{ __('Budgets') }}</a></li>
    <li class="breadcrumb-item">{{ $budget->budget_id }}</li>
@endsection
@section('action-btn')
    @can('edit budget')
    @if($budget->status !== 'closed')
    <a href="{{ route('accounting.budget.edit', $budget->id) }}" class="btn btn-sm btn-info"><i class="ti ti-pencil me-1"></i>{{ __('Edit') }}</a>
    @endif
    @endcan
@endsection
@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm text-center"><div class="card-body"><p class="text-muted mb-1 small">{{ __('Fiscal Year') }}</p><h5>{{ $budget->fiscal_year }}</h5></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm text-center"><div class="card-body"><p class="text-muted mb-1 small">{{ __('Period') }}</p><h6>{{ $budget->start_date->format('d M Y') }} – {{ $budget->end_date->format('d M Y') }}</h6></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm text-center"><div class="card-body"><p class="text-muted mb-1 small">{{ __('Total Budgeted') }}</p><h5>{{ \Auth::user()->priceFormat($budget->totalBudgeted()) }}</h5></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm text-center"><div class="card-body"><p class="text-muted mb-1 small">{{ __('Status') }}</p><span class="badge {{ $budget->statusBadge() }} fs-6">{{ ucfirst($budget->status) }}</span></div></div></div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent"><h6 class="mb-0">{{ __('Budget vs Actual') }}</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr>
                    <th>{{ __('Account') }}</th>
                    <th class="text-end">{{ __('Budgeted') }}</th>
                    <th class="text-end">{{ __('Actual') }}</th>
                    <th class="text-end">{{ __('Variance') }}</th>
                    <th style="min-width:160px">{{ __('Used') }}</th>
                </tr></thead>
                <tbody>
                @forelse($budget->lines as $line)
                @php
                    $bud = $line->annualBudget();
                    $act = $line->actualSpend();
                    $var = $bud - $act;
                    $pct = $bud > 0 ? min(($act/$bud)*100, 100) : 0;
                @endphp
                <tr>
                    <td>{{ $line->chartAccount->code ?? '' }} <strong>{{ $line->chartAccount->name ?? '—' }}</strong><br>
                        <small class="text-muted">{{ $line->description }}</small></td>
                    <td class="text-end">{{ \Auth::user()->priceFormat($bud) }}</td>
                    <td class="text-end">{{ \Auth::user()->priceFormat($act) }}</td>
                    <td class="text-end {{ $var >= 0 ? 'text-success' : 'text-danger fw-bold' }}">
                        {{ $var >= 0 ? '' : '-' }}{{ \Auth::user()->priceFormat(abs($var)) }}
                    </td>
                    <td>
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar {{ $pct > 90 ? 'bg-danger' : ($pct > 70 ? 'bg-warning' : 'bg-success') }}" style="width:{{ $pct }}%"></div>
                        </div>
                        <small class="text-muted">{{ number_format($pct,1) }}%</small>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No budget lines.') }}</td></tr>
                @endforelse
                </tbody>
                @if($budget->lines->count())
                @php
                    $totBud = $budget->lines->sum(fn($l)=>$l->annualBudget());
                    $totAct = $budget->lines->sum(fn($l)=>$l->actualSpend());
                    $totVar = $totBud - $totAct;
                @endphp
                <tfoot class="table-light"><tr>
                    <th>{{ __('Total') }}</th>
                    <th class="text-end">{{ \Auth::user()->priceFormat($totBud) }}</th>
                    <th class="text-end">{{ \Auth::user()->priceFormat($totAct) }}</th>
                    <th class="text-end {{ $totVar >= 0 ? 'text-success' : 'text-danger' }}">{{ \Auth::user()->priceFormat(abs($totVar)) }}</th>
                    <th></th>
                </tr></tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
