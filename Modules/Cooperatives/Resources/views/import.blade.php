{{ Form::open(['route' => 'cooperatives.import', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate' => true]) }}

<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('file', __('CSV / Excel File'), ['class' => 'form-label']) }}
                <x-required></x-required>
                {{ Form::file('file', ['class' => 'form-control', 'accept' => '.csv,.xlsx,.xls', 'required' => 'required']) }}
                <small class="text-muted d-block mt-1">
                    {{ __('Accepted: .csv, .xlsx, .xls — max 2 MB') }}
                </small>
            </div>
        </div>

        <div class="col-12 mt-3">
            <div class="alert alert-info mb-0">
                <strong>{{ __('Expected column headers:') }}</strong><br>
                <code>name, location, leader_name, leader_phone, site_location, formation_date, average_daily_supply, status</code><br>
                <small>{{ __('Only "name" is required. Rows with a duplicate name will be skipped.') }}</small>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <input type="submit" value="{{ __('Import') }}" class="btn btn-primary">
</div>

{{ Form::close() }}
