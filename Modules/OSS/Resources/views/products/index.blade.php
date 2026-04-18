@extends('layouts.admin')
@section('page-title', __('OSS Products'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('OSS Products') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('One Stop Shop Products') }}</h5>
                @can('manage oss products')
                <a href="{{ route('oss-products.create') }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-plus"></i> {{ __('Add Product') }}
                </a>
                @endcan
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Unit Price') }}</th>
                                <th>{{ __('Unit') }}</th>
                                <th>{{ __('Current Stock') }}</th>
                                <th>{{ __('Reorder Level') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr>
                                <td><code>{{ $product->product_code }}</code></td>
                                <td>
                                    <strong>{{ $product->name }}</strong>
                                    @if($product->description)
                                    <br><small class="text-muted">{{ Str::limit($product->description, 60) }}</small>
                                    @endif
                                </td>
                                <td>{{ $product->category ?? '—' }}</td>
                                <td>₦{{ number_format($product->unit_price, 2) }}</td>
                                <td>{{ $product->unit }}</td>
                                <td>
                                    <span class="{{ $product->is_low_stock ? 'text-danger fw-bold' : 'text-success' }}">
                                        {{ number_format($product->current_stock, 2) }} {{ $product->unit }}
                                    </span>
                                    @if($product->is_low_stock)
                                    <span class="badge bg-danger ms-1">{{ __('Low') }}</span>
                                    @endif
                                </td>
                                <td>{{ number_format($product->reorder_level, 2) }}</td>
                                <td>
                                    @if($product->is_active)
                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                    @else
                                    <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('oss-products.edit', $product->id) }}" class="btn btn-xs btn-outline-primary">
                                        <i class="ti ti-pencil"></i>
                                    </a>
                                    <form action="{{ route('oss-products.destroy', $product->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('{{ __('Delete this product?') }}')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-outline-danger"><i class="ti ti-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="text-center py-4 text-muted">{{ __('No products found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
