@extends('layouts.admin')
@section('page-title'){{ isset($cost) ? __('Edit Cost Entry') : __('Add Cost Entry') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('center-costs.index') }}">{{ __('Center Operations') }}</a></li>
    <li class="breadcrumb-item">{{ isset($cost) ? __('Edit') : __('Add') }}</li>
@endsection
@section('content')
<div class="row justify-content-center"><div class="col-xl-8">
<div class="card"><div class="card-body">
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    @php $action = isset($cost) ? route('center-costs.update', $cost->id) : route('center-costs.store'); @endphp
    <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
        @csrf @if(isset($cost)) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">{{ __('MCC') }} *</label>
                <select name="mcc" class="form-select" required>
                    <option value="">{{ __('Select MCC') }}</option>
                    @foreach($mccs as $m)<option value="{{ $m }}" {{ old('mcc', $cost->mcc ?? '') == $m ? 'selected' : '' }}>{{ $m }}</option>@endforeach
                </select></div>
            <div class="col-md-6"><label class="form-label">{{ __('Category') }} *</label>
                <select name="category" class="form-select" required>
                    <option value="">{{ __('Select Category') }}</option>
                    @foreach($categories as $c)<option value="{{ $c }}" {{ old('category', $cost->category ?? '') == $c ? 'selected' : '' }}>{{ $c }}</option>@endforeach
                </select></div>
            <div class="col-md-6"><label class="form-label">{{ __('Amount (₦)') }} *</label>
                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" value="{{ old('amount', $cost->amount ?? '') }}" required></div>
            <div class="col-md-3"><label class="form-label">{{ __('Period Start') }}</label>
                <input type="date" name="period_start" class="form-control" value="{{ old('period_start', isset($cost) ? optional($cost->period_start)->format('Y-m-d') : '') }}"></div>
            <div class="col-md-3"><label class="form-label">{{ __('Period End') }}</label>
                <input type="date" name="period_end" class="form-control" value="{{ old('period_end', isset($cost) ? optional($cost->period_end)->format('Y-m-d') : '') }}"></div>
            <div class="col-12"><label class="form-label">{{ __('Description') }}</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $cost->description ?? '') }}</textarea></div>
            <div class="col-12"><label class="form-label">{{ __('Receipt Attachment') }}</label>
                <input type="file" name="receipt" class="form-control"></div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">{{ isset($cost) ? __('Update') : __('Save as Draft') }}</button>
            <a href="{{ route('center-costs.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</div></div></div></div>
@endsection
