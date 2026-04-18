@extends('layouts.admin')

@section('page-title'){{ __('Add Milk Collection') }}@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('milk-collections.index') }}">{{ __('Milk Collection') }}</a></li>
    <li class="breadcrumb-item">{{ __('Add') }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-xl-8">
<div class="card">
<div class="card-header"><h5 class="mb-0">{{ __('Record Milk Collection') }}</h5></div>
<div class="card-body">
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <form action="{{ route('milk-collections.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label>
                <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('Time') }}</label>
                <input type="time" name="time" class="form-control" value="{{ old('time', date('H:i')) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('MCC') }} <span class="text-danger">*</span></label>
                <select name="mcc" class="form-select" required>
                    <option value="">{{ __('Select MCC') }}</option>
                    @foreach($mccs as $mcc)
                        <option value="{{ $mcc }}" {{ old('mcc') == $mcc ? 'selected' : '' }}>{{ $mcc }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('Farmer') }} <span class="text-danger">*</span></label>
                <select name="farmer_id" class="form-select" required>
                    <option value="">{{ __('Select Farmer') }}</option>
                    @foreach($farmers as $farmer)
                        <option value="{{ $farmer->id }}" {{ old('farmer_id') == $farmer->id ? 'selected' : '' }}>{{ $farmer->name }} ({{ $farmer->vender_id ?? $farmer->id }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Quantity (Litres)') }} <span class="text-danger">*</span></label>
                <input type="number" name="quantity_litres" class="form-control" step="0.01" min="0.01" value="{{ old('quantity_litres') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Quality Grade') }} <span class="text-danger">*</span></label>
                <div class="d-flex gap-3 mt-2">
                    @foreach($grades as $key => $label)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="quality_grade" id="grade_{{ $key }}" value="{{ $key }}" {{ old('quality_grade') == $key ? 'checked' : '' }} onchange="toggleRejection(this)">
                        <label class="form-check-label" for="grade_{{ $key }}">{{ $key }} <small class="text-muted">({{ $label }})</small></label>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Temperature (°C)') }}</label>
                <input type="number" name="temperature_celsius" class="form-control" step="0.1" value="{{ old('temperature_celsius') }}">
            </div>
            <div class="col-12" id="rejection_section" style="display:none;">
                <label class="form-label">{{ __('Rejection Reason') }} <span class="text-danger">*</span></label>
                <textarea name="rejection_reason" class="form-control" rows="2">{{ old('rejection_reason') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('Collection Batch ID') }}</label>
                <input type="text" name="collection_batch_id" class="form-control" value="{{ old('collection_batch_id') }}" placeholder="{{ __('Optional batch reference') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('Photo Evidence') }}</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
            </div>
            <div class="col-12">
                <label class="form-label">{{ __('Notes') }}</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">{{ __('Save Collection') }}</button>
            <a href="{{ route('milk-collections.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</div></div></div></div>

<script>
function toggleRejection(el) {
    document.getElementById('rejection_section').style.display = el.value === 'C' ? 'block' : 'none';
    document.querySelector('[name=rejection_reason]').required = el.value === 'C';
}
document.addEventListener('DOMContentLoaded', function() {
    var checked = document.querySelector('[name=quality_grade]:checked');
    if (checked) toggleRejection(checked);
});
</script>
@endsection
