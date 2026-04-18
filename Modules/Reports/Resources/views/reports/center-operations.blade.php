@extends('layouts.admin')

@section('page-title')
    {{ __('Center Operations Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}">{{ __('Reports') }}</a></li>
    <li class="breadcrumb-item">{{ __('Center Operations') }}</li>
@endsection

@section('content')

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold"><i class="ti ti-filter me-2 text-primary"></i>{{ __('Filters') }}</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.centers') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label" style="font-size:.82rem">{{ __('Center (MCC)') }}</label>
                    <select name="center" class="form-control form-control-sm">
                        <option value="">{{ __('All Centers') }}</option>
                        @foreach($centers as $c)
                            <option value="{{ $c }}" {{ $center === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.82rem">{{ __('Month') }}</label>
                    <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-size:.82rem">{{ __('Category') }}</label>
                    <select name="category" class="form-control form-control-sm">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="ti ti-search"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
@if($summary)
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4">₦{{ number_format($summary->total_spend ?? 0, 0) }}</div>
            <div class="text-muted" style="font-size:.78rem">Total Spend</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4 text-info">₦{{ number_format($summary->approved ?? 0, 0) }}</div>
            <div class="text-muted" style="font-size:.78rem">Approved</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4 text-warning">₦{{ number_format($summary->pending ?? 0, 0) }}</div>
            <div class="text-muted" style="font-size:.78rem">Pending</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4 text-success">₦{{ number_format($summary->paid ?? 0, 0) }}</div>
            <div class="text-muted" style="font-size:.78rem">Paid</div>
        </div>
    </div>
</div>
@endif

<div class="row g-4">
    {{-- Cost by Category --}}
    <div class="col-xl-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">
                    <i class="ti ti-tags me-2 text-info"></i>
                    Cost Breakdown by Category
                </h6>
            </div>
            <div class="card-body p-0">
                @if($byCategory->isEmpty())
                    <div class="text-center py-4 text-muted small">No data for selected period.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-end">Requisitions</th>
                                    <th class="text-end">Total (₦)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byCategory as $cat)
                                    <tr>
                                        <td style="font-size:.85rem">{{ $cat->category ?? 'Uncategorised' }}</td>
                                        <td class="text-end" style="font-size:.85rem">{{ $cat->req_count }}</td>
                                        <td class="text-end fw-semibold" style="font-size:.85rem">
                                            {{ number_format($cat->total, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td>Total</td>
                                    <td class="text-end">{{ $byCategory->sum('req_count') }}</td>
                                    <td class="text-end">₦{{ number_format($byCategory->sum('total'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Per-center cost --}}
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">
                    <i class="ti ti-building me-2 text-success"></i>
                    Per-Center Cost Summary — {{ $month }}
                </h6>
            </div>
            <div class="card-body p-0">
                @if($byCenter->isEmpty())
                    <div class="text-center py-4 text-muted small">No data for selected filters.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Center (MCC)</th>
                                    <th class="text-end">Requisitions</th>
                                    <th class="text-end">Pending</th>
                                    <th class="text-end">Total Spend (₦)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byCenter as $centerRow)
                                    <tr>
                                        <td style="font-size:.85rem">{{ $centerRow->mcc_name ?? '—' }}</td>
                                        <td class="text-end" style="font-size:.85rem">{{ $centerRow->req_count }}</td>
                                        <td class="text-end" style="font-size:.85rem">
                                            @if($centerRow->pending_count > 0)
                                                <span class="badge bg-warning text-dark">{{ $centerRow->pending_count }}</span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-semibold" style="font-size:.85rem">
                                            {{ number_format($centerRow->total, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td>Total</td>
                                    <td class="text-end">{{ $byCenter->sum('req_count') }}</td>
                                    <td class="text-end">{{ $byCenter->sum('pending_count') }}</td>
                                    <td class="text-end">₦{{ number_format($byCenter->sum('total'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
