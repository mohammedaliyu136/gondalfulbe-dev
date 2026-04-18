@extends('layouts.admin')
@section('page-title', isset($agent) ? __('Edit Agent') : __('Add Agent'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('extension-agents.index') }}">{{ __('Extension Agents') }}</a></li>
    <li class="breadcrumb-item active">{{ isset($agent) ? __('Edit') : __('Add') }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">{{ isset($agent) ? __('Edit Agent') : __('Add Extension Agent') }}</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ isset($agent) ? route('extension-agents.update', $agent->id) : route('extension-agents.store') }}">
                    @csrf
                    @isset($agent) @method('PUT') @endisset

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">{{ __('Full Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $agent->name ?? '') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Phone') }}</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $agent->phone ?? '') }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ __('Assigned Centers') }}</label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($mccs as $mcc)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="assigned_centers[]"
                                           value="{{ $mcc }}" id="center_{{ Str::slug($mcc) }}"
                                           @checked(in_array($mcc, (array)($agent->assigned_centers ?? [])))>
                                    <label class="form-check-label" for="center_{{ Str::slug($mcc) }}">{{ $mcc }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Join Date') }}</label>
                            <input type="date" name="join_date" class="form-control"
                                   value="{{ old('join_date', isset($agent) ? $agent->join_date?->format('Y-m-d') : '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Supervisor') }}</label>
                            <select name="supervisor_id" class="form-select">
                                <option value="">{{ __('None') }}</option>
                                @foreach($supervisors as $s)
                                <option value="{{ $s->id }}" @selected(old('supervisor_id', $agent->supervisor_id ?? '') == $s->id)>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @isset($agent)
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" class="form-select">
                                <option value="active" @selected($agent->status === 'active')>{{ __('Active') }}</option>
                                <option value="inactive" @selected($agent->status === 'inactive')>{{ __('Inactive') }}</option>
                                <option value="suspended" @selected($agent->status === 'suspended')>{{ __('Suspended') }}</option>
                            </select>
                        </div>
                        @endisset
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ __('Save Agent') }}</button>
                        <a href="{{ route('extension-agents.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
