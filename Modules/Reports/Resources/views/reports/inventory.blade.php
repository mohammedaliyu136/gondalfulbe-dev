@extends('layouts.admin')

@section('page-title')
    {{ __('Inventory Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}">{{ __('Reports') }}</a></li>
    <li class="breadcrumb-item">{{ __('Inventory') }}</li>
@endsection

@section('content')

{{-- Stock Summary Cards --}}
@php
    $outOfStock = $products->where('stock_status', 'Out of Stock')->count();
    $lowStock   = $products->where('stock_status', 'Low Stock')->count();
    $ok         = $products->where('stock_status', 'OK')->count();
@endphp

<div class="row g-3 mb-4">
    <div class="col-md-4 col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4 text-success">{{ $ok }}</div>
            <div class="text-muted" style="font-size:.78rem">Products OK</div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4 text-warning">{{ $lowStock }}</div>
            <div class="text-muted" style="font-size:.78rem">Low Stock</div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4 text-danger">{{ $outOfStock }}</div>
            <div class="text-muted" style="font-size:.78rem">Out of Stock</div>
        </div>
    </div>
</div>

{{-- Products Stock Level Table --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold">
            <i class="ti ti-package me-2 text-info"></i>
            Product Stock Levels
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Unit</th>
                        <th class="text-end">Current Stock</th>
                        <th class="text-end">Reorder Level</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td style="font-size:.88rem">{{ $product->name }}</td>
                            <td style="font-size:.82rem; color:#6c757d">{{ $product->unit ?? '—' }}</td>
                            <td class="text-end fw-semibold" style="font-size:.88rem">
                                {{ number_format($product->current_stock, 0) }}
                            </td>
                            <td class="text-end" style="font-size:.82rem; color:#6c757d">
                                {{ number_format($product->reorder_quantity ?? 0, 0) }}
                            </td>
                            <td>
                                @if($product->stock_status === 'OK')
                                    <span class="badge bg-success" style="font-size:.72rem">OK</span>
                                @elseif($product->stock_status === 'Low Stock')
                                    <span class="badge bg-warning text-dark" style="font-size:.72rem">
                                        <i class="ti ti-alert-triangle me-1"></i>Low Stock
                                    </span>
                                @else
                                    <span class="badge bg-danger" style="font-size:.72rem">
                                        <i class="ti ti-x me-1"></i>Out of Stock
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Recent Transactions --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold">
            <i class="ti ti-history me-2 text-primary"></i>
            Recent Stock Transactions
            <span class="badge bg-secondary ms-1">{{ $transactions->count() }}</span>
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th>Type</th>
                        <th class="text-end">Quantity</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td style="font-size:.82rem">
                                {{ \Carbon\Carbon::parse($tx->created_at)->format('d M Y') }}
                            </td>
                            <td style="font-size:.85rem">{{ $tx->product_name ?? '—' }}</td>
                            <td style="font-size:.82rem; color:#6c757d">{{ $tx->warehouse_name ?? '—' }}</td>
                            <td>
                                @if(in_array($tx->type, ['in', 'purchase', 'receive']))
                                    <span class="badge bg-success" style="font-size:.72rem">
                                        <i class="ti ti-arrow-down me-1"></i>In
                                    </span>
                                @elseif(in_array($tx->type, ['out', 'sale', 'issue']))
                                    <span class="badge bg-danger" style="font-size:.72rem">
                                        <i class="ti ti-arrow-up me-1"></i>Out
                                    </span>
                                @else
                                    <span class="badge bg-secondary" style="font-size:.72rem">
                                        {{ ucfirst($tx->type ?? 'adj') }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end fw-semibold" style="font-size:.85rem">
                                {{ number_format($tx->quantity, 0) }}
                            </td>
                            <td style="font-size:.78rem; color:#6c757d; max-width:200px">
                                {{ \Illuminate\Support\Str::limit($tx->description ?? '', 60) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No recent transactions.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
