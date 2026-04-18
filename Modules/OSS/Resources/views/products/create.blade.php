@extends('layouts.admin')
@section('page-title', isset($product) ? __('Edit Product') : __('Add Product'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('oss-products.index') }}">{{ __('OSS Products') }}</a></li>
    <li class="breadcrumb-item active">{{ isset($product) ? __('Edit') : __('Add') }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ isset($product) ? __('Edit Product') : __('Add Product') }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($product) ? route('oss-products.update', $product->id) : route('oss-products.store') }}">
                    @csrf
                    @isset($product) @method('PUT') @endisset

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">{{ __('Product Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $product->name ?? '') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Category') }}</label>
                            <input type="text" name="category" class="form-control" placeholder="{{ __('e.g. Fertilizer') }}"
                                   value="{{ old('category', $product->category ?? '') }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description', $product->description ?? '') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Unit Price (₦)') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="unit_price"
                                   class="form-control @error('unit_price') is-invalid @enderror"
                                   value="{{ old('unit_price', $product->unit_price ?? '') }}" required>
                            @error('unit_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Unit') }} <span class="text-danger">*</span></label>
                            <input type="text" name="unit" class="form-control @error('unit') is-invalid @enderror"
                                   placeholder="{{ __('e.g. kg, litre, bag') }}"
                                   value="{{ old('unit', $product->unit ?? '') }}" required>
                            @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Reorder Level') }}</label>
                            <input type="number" step="0.01" min="0" name="reorder_level" class="form-control"
                                   value="{{ old('reorder_level', $product->reorder_level ?? 0) }}">
                        </div>
                        @isset($product)
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="is_active" class="form-select">
                                <option value="1" @selected(($product->is_active ?? true))>{{ __('Active') }}</option>
                                <option value="0" @selected(!($product->is_active ?? true))>{{ __('Inactive') }}</option>
                            </select>
                        </div>
                        @endisset
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ __('Save Product') }}</button>
                        <a href="{{ route('oss-products.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
