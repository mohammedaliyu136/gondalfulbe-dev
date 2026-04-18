@extends('layouts.admin')
@section('page-title', isset($visit) ? __('Edit Visit') : __('Log Field Visit'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('field-visits.index') }}">{{ __('Field Visits') }}</a></li>
    <li class="breadcrumb-item active">{{ isset($visit) ? __('Edit') : __('Log Visit') }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">{{ isset($visit) ? __('Edit Field Visit') : __('Log Field Visit') }}</h5></div>
            <div class="card-body">
                <form method="POST"
                      action="{{ isset($visit) ? route('field-visits.update', $visit->id) : route('field-visits.store') }}"
                      enctype="multipart/form-data">
                    @csrf
                    @isset($visit) @method('PUT') @endisset

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Agent') }} <span class="text-danger">*</span></label>
                            <select name="agent_id" class="form-select @error('agent_id') is-invalid @enderror" required>
                                <option value="">{{ __('Select agent') }}</option>
                                @foreach($agents as $a)
                                <option value="{{ $a->id }}" @selected(old('agent_id', $visit->agent_id ?? '') == $a->id)>{{ $a->name }}</option>
                                @endforeach
                            </select>
                            @error('agent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Visit Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="visit_date" class="form-control"
                                   value="{{ old('visit_date', isset($visit) ? $visit->visit_date?->format('Y-m-d') : date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Center') }}</label>
                            <select name="center" class="form-select">
                                <option value="">{{ __('Select') }}</option>
                                @foreach($mccs as $mcc)
                                <option value="{{ $mcc }}" @selected(old('center', $visit->center ?? '') === $mcc)>{{ $mcc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Community') }}</label>
                            <input type="text" name="community" class="form-control"
                                   value="{{ old('community', $visit->community ?? '') }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ __('Topics Discussed') }}</label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($topics as $topic)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="topics[]"
                                           value="{{ $topic }}" id="topic_{{ Str::slug($topic) }}"
                                           @checked(in_array($topic, old('topics', isset($visit) ? $visit->topics->pluck('topic')->toArray() : [])))>
                                    <label class="form-check-label" for="topic_{{ Str::slug($topic) }}">{{ $topic }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $visit->notes ?? '') }}</textarea>
                        </div>
                    </div>

                    <!-- Farmers Visited -->
                    <h6 class="mt-4 mb-2">{{ __('Farmers Visited') }}</h6>
                    <div id="farmersBody">
                        @php $existingFarmers = isset($visit) ? $visit->farmers->toArray() : [[]]; @endphp
                        @foreach($existingFarmers as $i => $fv)
                        <div class="row g-2 mb-2 farmer-row">
                            <div class="col-md-6">
                                <select name="farmers[{{ $i }}][farmer_id]" class="form-select form-select-sm">
                                    <option value="">{{ __('Select from register') }}</option>
                                    @foreach($farmers as $f)
                                    <option value="{{ $f->id }}" @selected(($fv['farmer_id'] ?? '') == $f->id)>{{ $f->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="farmers[{{ $i }}][farmer_name]" class="form-control form-control-sm"
                                       placeholder="{{ __('Or type name manually') }}"
                                       value="{{ $fv['farmer_name'] ?? '' }}">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-farmer">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mb-4" id="addFarmer">
                        <i class="ti ti-plus"></i> {{ __('Add Farmer') }}
                    </button>

                    @if(! isset($visit))
                    <!-- Photos (store only) -->
                    <div class="mb-4">
                        <label class="form-label">{{ __('Photos (max 3)') }}</label>
                        <input type="file" name="photos[]" class="form-control" accept="image/*" multiple>
                    </div>
                    @endif

                    <!-- Follow-up -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="follow_up_required"
                                       id="followUpCheck" value="1"
                                       @checked(old('follow_up_required', $visit->follow_up_required ?? false))>
                                <label class="form-check-label" for="followUpCheck">{{ __('Follow-up Required') }}</label>
                            </div>
                            <div class="row g-2" id="followUpFields" style="{{ old('follow_up_required', $visit->follow_up_required ?? false) ? '' : 'display:none' }}">
                                <div class="col-md-4">
                                    <input type="date" name="follow_up_date" class="form-control form-control-sm"
                                           value="{{ old('follow_up_date', isset($visit) ? $visit->follow_up_date?->format('Y-m-d') : '') }}">
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="follow_up_note" class="form-control form-control-sm"
                                           placeholder="{{ __('Follow-up note') }}"
                                           value="{{ old('follow_up_note', $visit->follow_up_note ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ isset($visit) ? __('Update Visit') : __('Log Visit') }}</button>
                        <a href="{{ route('field-visits.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('followUpCheck').addEventListener('change', function () {
    document.getElementById('followUpFields').style.display = this.checked ? '' : 'none';
});
let farmerCount = {{ count($existingFarmers ?? [[]]) }};
document.getElementById('addFarmer').addEventListener('click', function () {
    const body = document.getElementById('farmersBody');
    const html = `<div class="row g-2 mb-2 farmer-row">
        <div class="col-md-6">
            <select name="farmers[${farmerCount}][farmer_id]" class="form-select form-select-sm">
                <option value="">{{ __('Select from register') }}</option>
                @foreach($farmers as $f) <option value="{{ $f->id }}">{{ $f->name }}</option> @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <input type="text" name="farmers[${farmerCount}][farmer_name]" class="form-control form-control-sm" placeholder="{{ __('Or type name manually') }}">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-sm btn-outline-danger remove-farmer"><i class="ti ti-trash"></i></button>
        </div>
    </div>`;
    body.insertAdjacentHTML('beforeend', html);
    farmerCount++;
});
document.getElementById('farmersBody').addEventListener('click', e => {
    if (e.target.closest('.remove-farmer')) e.target.closest('.farmer-row').remove();
});
</script>
@endpush
