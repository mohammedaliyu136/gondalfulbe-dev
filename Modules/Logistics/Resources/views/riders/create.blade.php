@extends('layouts.admin')
@section('page-title'){{ isset($rider) ? __('Edit Rider') : __('Add Rider') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('riders.index') }}">{{ __('Riders') }}</a></li>
    <li class="breadcrumb-item">{{ isset($rider) ? __('Edit') : __('Add') }}</li>
@endsection
@section('content')
<div class="row justify-content-center"><div class="col-xl-7">
<div class="card"><div class="card-body">
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    @php $action = isset($rider) ? route('riders.update', $rider->id) : route('riders.store'); @endphp
    <form action="{{ $action }}" method="POST">
        @csrf @if(isset($rider)) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">{{ __('Name') }} *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $rider->name ?? '') }}" required></div>
            <div class="col-md-6"><label class="form-label">{{ __('Contact') }}</label>
                <input type="text" name="contact" class="form-control" value="{{ old('contact', $rider->contact ?? '') }}"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Email') }}</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $rider->email ?? '') }}"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Collection Centre') }}</label>
                <input type="text" name="collection_centre" class="form-control" value="{{ old('collection_centre', $rider->collection_centre ?? '') }}"></div>
            <div class="col-md-4"><label class="form-label">{{ __('Bank Name') }}</label>
                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $rider->bank_name ?? '') }}"></div>
            <div class="col-md-4"><label class="form-label">{{ __('Account Number') }}</label>
                <input type="text" name="bank_account" class="form-control" value="{{ old('bank_account', $rider->bank_account ?? '') }}"></div>
            <div class="col-md-4"><label class="form-label">{{ __('Account Name') }}</label>
                <input type="text" name="account_name" class="form-control" value="{{ old('account_name', $rider->account_name ?? '') }}"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Rate per Trip (₦)') }}</label>
                <input type="number" name="amount_per_trip" class="form-control" step="0.01" min="0" value="{{ old('amount_per_trip', $rider->amount_per_trip ?? 0) }}"></div>
            @if(isset($rider))
            <div class="col-md-6"><label class="form-label">{{ __('Status') }}</label>
                <select name="is_active" class="form-select">
                    <option value="1" {{ $rider->is_active ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="0" {{ !$rider->is_active ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                </select></div>
            @endif
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">{{ isset($rider) ? __('Update') : __('Add Rider') }}</button>
            <a href="{{ route('riders.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</div></div></div></div>
@endsection
