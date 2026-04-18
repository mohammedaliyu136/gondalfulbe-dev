@extends('layouts.admin')
@section('page-title'){{ __('Edit Collection') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('milk-collections.index') }}">{{ __('Milk Collection') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit') }}</li>
@endsection
@section('content')
<div class="row justify-content-center"><div class="col-xl-8">
<div class="card">
<div class="card-header"><h5 class="mb-0">{{ __('Edit Milk Collection') }} — {{ $collection->collection_id }}</h5></div>
<div class="card-body">
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <form action="{{ route('milk-collections.update', $collection->id) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">{{ __('Date') }} *</label>
                <input type="date" name="date" class="form-control" value="{{ old('date', $collection->date->format('Y-m-d')) }}" required></div>
            <div class="col-md-6"><label class="form-label">{{ __('Time') }}</label>
                <input type="time" name="time" class="form-control" value="{{ old('time', $collection->time) }}"></div>
            <div class="col-md-6"><label class="form-label">{{ __('MCC') }} *</label>
                <select name="mcc" class="form-select" required>
                    @foreach($mccs as $mcc)
                    <option value="{{ $mcc }}" {{ old('mcc', $collection->mcc) == $mcc ? 'selected' : '' }}>{{ $mcc }}</option>
                    @endforeach
                </select></div>
            <div class="col-md-6"><label class="form-label">{{ __('Farmer') }} *</label>
                <select name="farmer_id" class="form-select" required>
                    @foreach($farmers as $farmer)
                    <option value="{{ $farmer->id }}" {{ old('farmer_id', $collection->farmer_id) == $farmer->id ? 'selected' : '' }}>{{ $farmer->name }}</option>
                    @endforeach
                </select></div>
            <div class="col-md-4"><label class="form-label">{{ __('Quantity (L)') }} *</label>
                <input type="number" name="quantity_litres" class="form-control" step="0.01" min="0.01" value="{{ old('quantity_litres', $collection->quantity_litres) }}" required></div>
            <div class="col-md-4"><label class="form-label">{{ __('Quality Grade') }} *</label>
                <div class="d-flex gap-3 mt-2">
                    @foreach($grades as $key => $label)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="quality_grade" id="grade_{{ $key }}" value="{{ $key }}"
                            {{ old('quality_grade', $collection->quality_grade) == $key ? 'checked' : '' }} onchange="toggleRejection(this)">
                        <label class="form-check-label" for="grade_{{ $key }}">{{ $key }}</label>
                    </div>
                    @endforeach
                </div></div>
            <div class="col-md-4"><label class="form-label">{{ __('Temperature (°C)') }}</label>
                <input type="number" name="temperature_celsius" class="form-control" step="0.1" value="{{ old('temperature_celsius', $collection->temperature_celsius) }}"></div>
            <div class="col-12" id="rejection_section" style="{{ old('quality_grade', $collection->quality_grade) === 'C' ? '' : 'display:none' }}">
                <label class="form-label">{{ __('Rejection Reason') }} *</label>
                <textarea name="rejection_reason" class="form-control" rows="2">{{ old('rejection_reason', $collection->rejection_reason) }}</textarea>
            </div>
            <div class="col-12"><label class="form-label">{{ __('Notes') }}</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $collection->notes) }}</textarea></div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
            <a href="{{ route('milk-collections.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</div></div></div></div>
<script>
function toggleRejection(el) {
    document.getElementById('rejection_section').style.display = el.value === 'C' ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', function() {
    var checked = document.querySelector('[name=quality_grade]:checked');
    if (checked) toggleRejection(checked);
});
</script>
@endsection
