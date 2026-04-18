@extends('layouts.admin')
@section('page-title', isset($event) ? __('Edit Training Event') : __('Record Training Event'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('training-events.index') }}">{{ __('Training Events') }}</a></li>
    <li class="breadcrumb-item active">{{ isset($event) ? __('Edit') : __('Record') }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">{{ isset($event) ? __('Edit Training Event') : __('Record Training Event') }}</h5></div>
            <div class="card-body">
                <form method="POST"
                      action="{{ isset($event) ? route('training-events.update', $event->id) : route('training-events.store') }}">
                    @csrf
                    @isset($event) @method('PUT') @endisset

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">{{ __('Event Title') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $event->title ?? '') }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Event Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="event_date" class="form-control @error('event_date') is-invalid @enderror"
                                   value="{{ old('event_date', isset($event) ? $event->event_date?->format('Y-m-d') : date('Y-m-d')) }}" required>
                            @error('event_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Center') }}</label>
                            <select name="center" class="form-select">
                                <option value="">{{ __('Select') }}</option>
                                @foreach($mccs as $mcc)
                                <option value="{{ $mcc }}" @selected(old('center', $event->center ?? '') === $mcc)>{{ $mcc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Location / Venue') }}</label>
                            <input type="text" name="location" class="form-control"
                                   value="{{ old('location', $event->location ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Facilitators') }}</label>
                            <input type="text" name="facilitators" class="form-control"
                                   placeholder="{{ __('Comma-separated names') }}"
                                   value="{{ old('facilitators', isset($event) ? implode(', ', (array)($event->facilitators ?? [])) : '') }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ __('Topics Covered') }}</label>
                            <textarea name="topics_covered" class="form-control" rows="2">{{ old('topics_covered', $event->topics_covered ?? '') }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $event->notes ?? '') }}</textarea>
                        </div>
                    </div>

                    @if(! isset($event))
                    <!-- Attendees -->
                    <h6 class="mt-4 mb-2">{{ __('Attendees') }}</h6>
                    <div id="attendeesBody">
                        <div class="row g-2 mb-2 attendee-row">
                            <div class="col-md-6">
                                <select name="attendees[0][farmer_id]" class="form-select form-select-sm">
                                    <option value="">{{ __('Select from register') }}</option>
                                    @foreach($farmers as $f)
                                    <option value="{{ $f->id }}">{{ $f->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="attendees[0][farmer_name]" class="form-control form-control-sm"
                                       placeholder="{{ __('Or type name manually') }}">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-attendee"><i class="ti ti-trash"></i></button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mb-4" id="addAttendee">
                        <i class="ti ti-plus"></i> {{ __('Add Attendee') }}
                    </button>

                    <!-- Materials -->
                    <h6 class="mb-2">{{ __('Materials Distributed') }}</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered" id="materialsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Material Name') }}</th>
                                    <th style="width:150px">{{ __('Qty Distributed') }}</th>
                                    <th style="width:50px"></th>
                                </tr>
                            </thead>
                            <tbody id="materialsBody">
                                <tr class="mat-row">
                                    <td><input type="text" name="materials[0][material_name]" class="form-control form-control-sm" placeholder="{{ __('e.g. Fertilizer bag') }}"></td>
                                    <td><input type="number" min="0" name="materials[0][quantity_distributed]" class="form-control form-control-sm" value="0"></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-mat"><i class="ti ti-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mb-4" id="addMaterial">
                        <i class="ti ti-plus"></i> {{ __('Add Material') }}
                    </button>
                    @endif

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ isset($event) ? __('Update Event') : __('Save Event') }}</button>
                        <a href="{{ route('training-events.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let ac = 1, mc = 1;
document.getElementById('addAttendee')?.addEventListener('click', function () {
    const body = document.getElementById('attendeesBody');
    const html = `<div class="row g-2 mb-2 attendee-row">
        <div class="col-md-6">
            <select name="attendees[${ac}][farmer_id]" class="form-select form-select-sm">
                <option value="">{{ __('Select from register') }}</option>
                @foreach($farmers as $f)<option value="{{ $f->id }}">{{ $f->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-5">
            <input type="text" name="attendees[${ac}][farmer_name]" class="form-control form-control-sm" placeholder="{{ __('Or type name manually') }}">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-sm btn-outline-danger remove-attendee"><i class="ti ti-trash"></i></button>
        </div>
    </div>`;
    body.insertAdjacentHTML('beforeend', html); ac++;
});
document.getElementById('attendeesBody')?.addEventListener('click', e => {
    if (e.target.closest('.remove-attendee')) e.target.closest('.attendee-row').remove();
});
document.getElementById('addMaterial')?.addEventListener('click', function () {
    const body = document.getElementById('materialsBody');
    const html = `<tr class="mat-row">
        <td><input type="text" name="materials[${mc}][material_name]" class="form-control form-control-sm"></td>
        <td><input type="number" min="0" name="materials[${mc}][quantity_distributed]" class="form-control form-control-sm" value="0"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-mat"><i class="ti ti-trash"></i></button></td>
    </tr>`;
    body.insertAdjacentHTML('beforeend', html); mc++;
});
document.getElementById('materialsBody')?.addEventListener('click', e => {
    if (e.target.closest('.remove-mat')) e.target.closest('.mat-row').remove();
});
</script>
@endpush
