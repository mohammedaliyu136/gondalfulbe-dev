@extends('layouts.admin')
@section('page-title', isset($sponsor) ? __('Edit Sponsor') : __('Add Sponsor'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.sponsors.index') }}">{{ __('Sponsors') }}</a></li>
    <li class="breadcrumb-item active">{{ isset($sponsor) ? __('Edit') : __('Add') }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">{{ isset($sponsor) ? __('Edit Sponsor') : __('Add Sponsor') }}</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ isset($sponsor) ? route('admin.sponsors.update', $sponsor->id) : route('admin.sponsors.store') }}">
                    @csrf
                    @isset($sponsor) @method('PUT') @endisset

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Organization Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="organization_name" class="form-control @error('organization_name') is-invalid @enderror"
                                   value="{{ old('organization_name', $sponsor->organization_name ?? '') }}" required>
                            @error('organization_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Contact Person') }} <span class="text-danger">*</span></label>
                            <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror"
                                   value="{{ old('contact_person', $sponsor->contact_person ?? '') }}" required>
                            @error('contact_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $sponsor->email ?? '') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Phone') }}</label>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone', $sponsor->phone ?? '') }}">
                        </div>
                        @if(! isset($sponsor))
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Password') }} <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Organization Type') }} <span class="text-danger">*</span></label>
                            <select name="organization_type" class="form-select @error('organization_type') is-invalid @enderror" required>
                                <option value="">{{ __('Select type') }}</option>
                                @foreach($orgTypes as $type)
                                <option value="{{ $type }}" @selected(old('organization_type', $sponsor->organization_type ?? '') === $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('organization_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Country') }}</label>
                            <input type="text" name="country" class="form-control"
                                   value="{{ old('country', $sponsor->country ?? '') }}">
                        </div>
                        @isset($sponsor)
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" class="form-select">
                                <option value="active" @selected($sponsor->status === 'active')>{{ __('Active') }}</option>
                                <option value="inactive" @selected($sponsor->status === 'inactive')>{{ __('Inactive') }}</option>
                                <option value="suspended" @selected($sponsor->status === 'suspended')>{{ __('Suspended') }}</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">{{ __('New Password (leave blank to keep current)') }}</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        @endisset
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ __('Save Sponsor') }}</button>
                        <a href="{{ route('admin.sponsors.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
