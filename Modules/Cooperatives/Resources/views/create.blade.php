{{ Form::open(['route' => 'cooperatives.store', 'method' => 'post', 'class' => 'needs-validation', 'novalidate' => true]) }}

<div class="modal-body">
    @include('cooperatives::_form')
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>

{{ Form::close() }}
