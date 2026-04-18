@extends('layouts.admin')
@section('page-title'){{ isset($trip) ? __('Edit Trip') : __('Add Trip') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('logistics.index') }}">{{ __('Logistics') }}</a></li>
    <li class="breadcrumb-item">{{ isset($trip) ? __('Edit') : __('Add') }}</li>
@endsection
@section('content')
<div class="row justify-content-center"><div class="col-xl-9">
<div class="card"><div class="card-header"><h5 class="mb-0">{{ isset($trip) ? __('Edit Trip') : __('New Trip') }}</h5></div>
<div class="card-body">
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    @php $action = isset($trip) ? route('logistics.update', $trip->id) : route('logistics.store'); @endphp
    <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
        @csrf @if(isset($trip)) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">{{ __('Trip Date') }} *</label>
                <input type="date" name="trip_date" class="form-control" value="{{ old('trip_date', isset($trip) ? $trip->trip_date->format('Y-m-d') : date('Y-m-d')) }}" required></div>
            <div class="col-md-4"><label class="form-label">{{ __('MCC Source') }} *</label>
                <select name="mcc_source" class="form-select" required>
                    <option value="">{{ __('Select MCC') }}</option>
                    @foreach($mccs as $mcc)<option value="{{ $mcc }}" {{ old('mcc_source', $trip->mcc_source ?? '') == $mcc ? 'selected' : '' }}>{{ $mcc }}</option>@endforeach
                </select></div>
            <div class="col-md-4"><label class="form-label">{{ __('Destination') }}</label>
                <input type="text" name="destination" class="form-control" value="{{ old('destination', $trip->destination ?? 'Sebore Plant') }}"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Rider') }} *</label>
                <select name="rider_id" class="form-select" required>
                    <option value="">{{ __('Select Rider') }}</option>
                    @foreach($riders as $r)<option value="{{ $r->id }}" {{ old('rider_id', $trip->rider_id ?? '') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>@endforeach
                </select></div>
            <div class="col-md-6"><label class="form-label">{{ __('Vehicle Registration') }}</label>
                <input type="text" name="vehicle_registration" class="form-control" value="{{ old('vehicle_registration', $trip->vehicle_registration ?? '') }}"></div>
            <div class="col-md-3"><label class="form-label">{{ __('Departure Time') }}</label>
                <input type="time" name="departure_time" class="form-control" value="{{ old('departure_time', $trip->departure_time ?? '') }}"></div>
            <div class="col-md-3"><label class="form-label">{{ __('Arrival Time') }}</label>
                <input type="time" name="arrival_time" class="form-control" value="{{ old('arrival_time', $trip->arrival_time ?? '') }}"></div>
            <div class="col-md-3"><label class="form-label">{{ __('Litres Transported') }}</label>
                <input type="number" name="litres_transported" class="form-control" step="0.01" min="0" value="{{ old('litres_transported', $trip->litres_transported ?? 0) }}"></div>
            <div class="col-md-3"><label class="form-label">{{ __('Status') }} *</label>
                <select name="status" class="form-select" required>
                    @foreach($statuses as $s)<option value="{{ $s }}" {{ old('status', $trip->status ?? 'Scheduled') == $s ? 'selected' : '' }}>{{ $s }}</option>@endforeach
                </select></div>
            <div class="col-md-4"><label class="form-label">{{ __('Fuel Cost (₦)') }}</label>
                <input type="number" name="fuel_cost" class="form-control" step="0.01" min="0" value="{{ old('fuel_cost', $trip->fuel_cost ?? 0) }}"></div>
            <div class="col-md-4"><label class="form-label">{{ __('Other Expenses (₦)') }}</label>
                <input type="number" name="other_expenses" class="form-control" step="0.01" min="0" value="{{ old('other_expenses', $trip->other_expenses ?? 0) }}"></div>
            <div class="col-md-4"><label class="form-label">{{ __('Other Expenses Description') }}</label>
                <input type="text" name="other_expenses_description" class="form-control" value="{{ old('other_expenses_description', $trip->other_expenses_description ?? '') }}"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Collection Batch ID') }}</label>
                <input type="text" name="collection_batch_id" class="form-control" value="{{ old('collection_batch_id', $trip->collection_batch_id ?? '') }}"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Delivery Note (photo/file)') }}</label>
                <input type="file" name="delivery_note" class="form-control"></div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">{{ isset($trip) ? __('Update Trip') : __('Create Trip') }}</button>
            <a href="{{ route('logistics.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</div></div></div></div>
@endsection
