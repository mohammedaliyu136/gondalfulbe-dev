@extends('layouts.admin')
@section('page-title', __('OSS Inventory'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('OSS Inventory') }}</li>
@endsection

@section('content')
<!-- Stock Summary Cards -->
<div class="row mb-4">
    @foreach($stockLevels as $item)
    <div class="col-md-3 mb-3">
        <div class="card {{ $item['low'] ? 'border-danger' : '' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">{{ $item['product'] }}</p>
                        <h4 class="mb-0 {{ $item['low'] ? 'text-danger' : '' }}">
                            {{ number_format($item['stock'], 2) }} <small class="fs-6">{{ $item['unit'] }}</small>
                        </h4>
                    </div>
                    @if($item['low'])
                    <span class="badge bg-danger">{{ __('Low Stock') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Stock Transactions') }}</h5>
                <div class="d-flex gap-2">
                    @can('manage oss products')
                    <a href="{{ route('oss-inventory.stock-in') }}" class="btn btn-success btn-sm">
                        <i class="ti ti-arrow-bar-down"></i> {{ __('Stock In') }}
                    </a>
                    <a href="{{ route('oss-inventory.stock-out') }}" class="btn btn-warning btn-sm">
                        <i class="ti ti-arrow-bar-up"></i> {{ __('Stock Out') }}
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Txn ID') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Qty') }}</th>
                                <th>{{ __('Center') }}</th>
                                <th>{{ __('Reference') }}</th>
                                <th>{{ __('Notes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $txn)
                            <tr>
                                <td><code>{{ $txn->transaction_id }}</code></td>
                                <td>{{ \Carbon\Carbon::parse($txn->date)->format('d M Y') }}</td>
                                <td>
                                    @if($txn->type === 'Stock In')
                                    <span class="badge bg-success">{{ __('Stock In') }}</span>
                                    @else
                                    <span class="badge bg-warning text-dark">{{ __('Stock Out') }}</span>
                                    @endif
                                </td>
                                <td>{{ $txn->product->name ?? '—' }}</td>
                                <td>{{ number_format($txn->quantity, 2) }} {{ $txn->product->unit ?? '' }}</td>
                                <td>{{ $txn->center ?? '—' }}</td>
                                <td>{{ $txn->reference ?? '—' }}</td>
                                <td>{{ $txn->notes ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted">{{ __('No transactions yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($transactions->hasPages())
            <div class="card-footer">{{ $transactions->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
