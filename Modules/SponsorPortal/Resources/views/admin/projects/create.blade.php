@extends('layouts.admin')
@section('page-title', __('Create Project for') . ' ' . $sponsor->organization_name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.sponsors.show', $sponsor->id) }}">{{ $sponsor->organization_name }}</a></li>
    <li class="breadcrumb-item active">{{ __('Create Project') }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Create Project for') }} <em>{{ $sponsor->organization_name }}</em></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.sponsors.store-project', $sponsor->id) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">{{ __('Project Title') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                @foreach($statuses as $s)
                                <option value="{{ $s }}" @selected(old('status', 'Draft') === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Budget (₦)') }}</label>
                            <input type="number" step="0.01" min="0" name="budget" class="form-control"
                                   value="{{ old('budget', 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Start Date') }}</label>
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('End Date') }}</label>
                            <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ __('Focus Areas') }}</label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($focusAreas as $area)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="focus_areas[]"
                                           value="{{ $area }}" id="area_{{ $area }}"
                                           @checked(in_array($area, old('focus_areas', [])))>
                                    <label class="form-check-label" for="area_{{ $area }}">{{ ucfirst($area) }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ __('Create Project') }}</button>
                        <a href="{{ route('admin.sponsors.show', $sponsor->id) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
