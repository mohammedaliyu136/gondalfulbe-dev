{{-- Ajax popup edit form --}}
<div class="modal-header">
    <h5 class="modal-title">{{ __('Edit Sponsor') }}: {{ $sponsor->organization_name }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

{!! Form::open(['route' => ['sponsors.update', $sponsor->id], 'method' => 'PUT']) !!}

<div class="modal-body">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">{{ __('Organisation Name') }} <span class="text-danger">*</span></label>
            {!! Form::text('organization_name', old('organization_name', $sponsor->organization_name), [
                'class' => 'form-control',
                'required' => true
            ]) !!}
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">{{ __('Contact Person') }} <span class="text-danger">*</span></label>
            {!! Form::text('contact_person', old('contact_person', $sponsor->contact_person), [
                'class' => 'form-control',
                'required' => true
            ]) !!}
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">{{ __('Email Address') }} <span class="text-danger">*</span></label>
            {!! Form::email('email', old('email', $sponsor->email), [
                'class' => 'form-control',
                'required' => true
            ]) !!}
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">{{ __('New Password') }}</label>
            {!! Form::password('password', [
                'class' => 'form-control',
                'placeholder' => __('Leave blank to keep current'),
                'minlength' => 8
            ]) !!}
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">{{ __('Phone') }}</label>
            {!! Form::text('phone', old('phone', $sponsor->phone), [
                'class' => 'form-control'
            ]) !!}
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">{{ __('Organisation Type') }} <span class="text-danger">*</span></label>
            {!! Form::select('organization_type', [
                'NGO'        => 'NGO',
                'Government' => 'Government',
                'Corporate'  => 'Corporate',
                'Individual' => 'Individual',
            ], old('organization_type', $sponsor->organization_type), [
                'class' => 'form-control',
                'required' => true
            ]) !!}
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">{{ __('Country') }}</label>
            {!! Form::text('country', old('country', $sponsor->country), [
                'class' => 'form-control'
            ]) !!}
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">{{ __('Status') }} <span class="text-danger">*</span></label>
            {!! Form::select('status', [
                'active'   => 'Active',
                'inactive' => 'Inactive',
            ], old('status', $sponsor->status), [
                'class' => 'form-control',
                'required' => true
            ]) !!}
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {!! Form::submit(__('Save Changes'), ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}
