@extends('layouts.admin')
@section('page-title', __('OSS Sales'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('OSS Sales') }}</li>
@endsection

@section('content')
<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <p class="mb-1">{{ __('Total Revenue') }}</p>
                <h3 class="mb-0">₦{{ number_format($totalRevenue, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <p class="mb-1">{{ __('Outstanding Credit') }}</p>
                <h3 class="mb-0">₦{{ number_format($creditTotal, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('Sales Transactions') }}</h5>
        @can('manage oss products')
        <a href="{{ route('oss-sales.create') }}" class="btn btn-primary btn-sm">
            <i class="ti ti-plus"></i> {{ __('New Sale') }}
        </a>
        @endcan
    </div>
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">{{ __('Farmer') }}</label>
                <select name="farmer_id" class="form-select form-select-sm">
                    <option value="">{{ __('All Farmers') }}</option>
                    @foreach($farmers as $f)
                    <option value="{{ $f->id }}" @selected(request('farmer_id') == $f->id)>{{ $f->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('Center') }}</label>
                <select name="center" class="form-select form-select-sm">
                    <option value="">{{ __('All') }}</option>
                    @foreach($mccs as $mcc)
                    <option value="{{ $mcc }}" @selected(request('center') === $mcc)>{{ $mcc }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('From') }}</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('To') }}</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-secondary btn-sm w-100">{{ __('Filter') }}</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Sale ID') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Farmer') }}</th>
                        <th>{{ __('Center') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Payment') }}</th>
                        <th>{{ __('Credit Status') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td><code>{{ $sale->sale_id }}</code></td>
                        <td>{{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}</td>
                        <td>{{ $sale->farmer->name ?? '—' }}</td>
                        <td>{{ $sale->center ?? '—' }}</td>
                        <td>₦{{ number_format($sale->total_amount, 2) }}</td>
                        <td><span class="badge bg-secondary">{{ $sale->payment_method }}</span></td>
                        <td>
                            @if($sale->is_credit)
                                @if($sale->credit_settled)
                                <span class="badge bg-success">{{ __('Settled') }}</span>
                                @else
                                <span class="badge bg-danger">{{ __('Unsettled') }}</span>
                                @endif
                            @else
                            <span class="badge bg-light text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('oss-sales.show', $sale->id) }}" class="btn btn-xs btn-outline-info">
                                <i class="ti ti-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">{{ __('No sales found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sales->hasPages())
    <div class="card-footer">{{ $sales->links() }}</div>
    @endif
</div>
@endsection
