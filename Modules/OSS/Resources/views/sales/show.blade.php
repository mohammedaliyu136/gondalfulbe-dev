@extends('layouts.admin')
@section('page-title', __('Sale') . ' ' . $sale->sale_id)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('oss-sales.index') }}">{{ __('OSS Sales') }}</a></li>
    <li class="breadcrumb-item active">{{ $sale->sale_id }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">{{ __('Sale Details') }} — <code>{{ $sale->sale_id }}</code></h5>
                <span class="badge bg-secondary">{{ $sale->payment_method }}</span>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1 text-muted">{{ __('Farmer') }}</p>
                        <strong>{{ $sale->farmer->name ?? '—' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1 text-muted">{{ __('Date') }}</p>
                        <strong>{{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}</strong>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1 text-muted">{{ __('Center') }}</p>
                        <strong>{{ $sale->center ?? '—' }}</strong>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Qty') }}</th>
                                <th>{{ __('Unit Price') }}</th>
                                <th>{{ __('Subtotal') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $item)
                            <tr>
                                <td>{{ $item->product->name ?? '—' }}</td>
                                <td>{{ number_format($item->quantity, 2) }} {{ $item->product->unit ?? '' }}</td>
                                <td>₦{{ number_format($item->unit_price, 2) }}</td>
                                <td>₦{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">{{ __('Total') }}</th>
                                <th>₦{{ number_format($sale->total_amount, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @if($sale->is_credit)
                <div class="alert alert-{{ $sale->credit_settled ? 'success' : 'warning' }} mb-0">
                    {{ $sale->credit_settled ? __('Credit settled.') : __('Credit outstanding — not yet settled.') }}
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <a href="{{ route('oss-sales.index') }}" class="btn btn-secondary w-100">
            <i class="ti ti-arrow-left"></i> {{ __('Back to Sales') }}
        </a>
    </div>
</div>
@endsection
