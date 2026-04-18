@extends('layouts.admin')
@section('page-title', __('Record Stock Out'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('oss-inventory.index') }}">{{ __('OSS Inventory') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Stock Out') }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">{{ __('Record Stock Out') }}</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('oss-inventory.store-out') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Product') }} <span class="text-danger">*</span></label>
                            <select name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                                <option value="">{{ __('Select product') }}</option>
                                @foreach($products as $p)
                                <option value="{{ $p->id }}" @selected(old('product_id') == $p->id)>{{ $p->name }} ({{ $p->unit }}) — {{ number_format($p->current_stock, 2) }} available</option>
                                @endforeach
                            </select>
                            @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Quantity') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="quantity"
                                   class="form-control @error('quantity') is-invalid @enderror"
                                   value="{{ old('quantity') }}" required>
                            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Center') }}</label>
                            <select name="center" class="form-select">
                                <option value="">{{ __('All Centers') }}</option>
                                @foreach($mccs as $mcc)
                                <option value="{{ $mcc }}" @selected(old('center') === $mcc)>{{ $mcc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Reference') }}</label>
                            <input type="text" name="reference" class="form-control" value="{{ old('reference') }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-warning">{{ __('Record Stock Out') }}</button>
                        <a href="{{ route('oss-inventory.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
