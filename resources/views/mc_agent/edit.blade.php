{{ Form::model($mcAgent, ['route' => ['mcagent.update', $mcAgent->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate', 'enctype' => "multipart/form-data"]) }}
<div class="modal-body">

    <h6 class="sub-title">{{ __('Basic Info') }}</h6>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Name')]) }}
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                <x-mobile label="{{ __('Contact') }}" name="contact" value="{{ $mcAgent->contact }}" required placeholder="Enter Contact"></x-mobile>
            </div>
        </div>
         <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('name', __('Email'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('email', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Email')]) }}
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{ Form::label('bank_account', __('Bank Account'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('bank_account', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Bank Account')]) }}
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{ Form::label('bank', __('Select Bank'), ['class' => 'form-label']) }}<x-required></x-required>
                <select id="bank" name="bank_code" class="form-control select2" required>
                    <option value="">{{ __('Select a Bank') }}</option>
                    @foreach ($bankList as $bank)
                        <option value="{{ $bank['code'] }}" data-name="{{ $bank['name'] }}"
                            {{ isset($selectedBankCode) && $selectedBankCode == $bank['code'] ? 'selected' : '' }}>
                            {{ $bank['name'] }}
                        </option>
                    @endforeach
                </select>
                {{ Form::hidden('bank_name', null, ['id' => 'bank_name']) }}
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{ Form::label('account_name', __('Account Name'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('account_name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Not set')]) }}
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{ Form::label('tax_number', __('NIN Number'), ['class' => 'form-label']) }}
                {{ Form::text('tax_number', null, ['class' => 'form-control', 'placeholder' => __('Enter Tax Number')]) }}
            </div>
        </div>
        @if (!$customFields->isEmpty())
            @include('customFields.formBuilder')
        @endif
    </div>

    <h6 class="sub-title">{{ __('Billing Address') }}</h6>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('billing_country', __('Country'), ['class' => 'form-label']) }}
                {{ Form::text('billing_country', null, ['class' => 'form-control', 'placeholder' => __('Enter Country')]) }}
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('billing_state', __('State'), ['class' => 'form-label']) }}
                {{ Form::text('billing_state', null, ['class' => 'form-control', 'placeholder' => __('Enter State')]) }}
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('billing_city', __('City'), ['class' => 'form-label']) }}
                {{ Form::text('billing_city', null, ['class' => 'form-control', 'placeholder' => __('Enter City')]) }}
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('collection_centre', __('Collection Centre'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('collection_centre', $warehouses->pluck('name', 'id'), $selectedWarehouseId ?? null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Select Collection Centre')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('billing_address', __('Address'), ['class' => 'form-label']) }}
                {{ Form::textarea('billing_address', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter Address')]) }}
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('image', __('Upload Image'), ['class' => 'form-label']) }}
                {{ Form::file('image', ['class' => 'form-control', 'accept' => 'image/*']) }}
                <small class="text-muted">{{ __('Accepted file types: jpeg, png, jpg') }}</small>
            </div>
        </div>
    </div>

    @if (App\Models\Utility::getValByName('shipping_display') == 'on')
        <div class="col-md-12 text-end">
            <input type="button" id="billing_data" value="{{ __('Shipping Same As Billing') }}" class="btn btn-primary">
        </div>
        <h6 class="sub-title">{{ __('Shipping Address') }}</h6>
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="form-group">
                    {{ Form::label('shipping_name', __('Name'), ['class' => 'form-label']) }}
                    {{ Form::text('shipping_name', null, ['class' => 'form-control', 'placeholder' => __('Enter Name')]) }}
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="form-group">
                    {{ Form::label('shipping_phone', __('Phone'), ['class' => 'form-label']) }}
                    {{ Form::text('shipping_phone', null, ['class' => 'form-control', 'placeholder' => __('Enter Phone')]) }}
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('shipping_address', __('Address'), ['class' => 'form-label']) }}
                    {{ Form::textarea('shipping_address', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter Address')]) }}
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="form-group">
                    {{ Form::label('shipping_city', __('City'), ['class' => 'form-label']) }}
                    {{ Form::text('shipping_city', null, ['class' => 'form-control', 'placeholder' => __('Enter City')]) }}
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="form-group">
                    {{ Form::label('shipping_state', __('State'), ['class' => 'form-label']) }}
                    {{ Form::text('shipping_state', null, ['class' => 'form-control', 'placeholder' => __('Enter State')]) }}
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="form-group">
                    {{ Form::label('shipping_country', __('Country'), ['class' => 'form-label']) }}
                    {{ Form::text('shipping_country', null, ['class' => 'form-control', 'placeholder' => __('Enter Country')]) }}
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="form-group">
                    {{ Form::label('shipping_zip', __('Zip Code'), ['class' => 'form-label']) }}
                    {{ Form::text('shipping_zip', null, ['class' => 'form-control', 'placeholder' => __('Enter Zip')]) }}
                </div>
            </div>
        </div>
    @endif

</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-secondary" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>

{{ Form::close() }}



<script>
$(document).ready(function() {
    

    
    // Function to allow only numbers and limit length to 10 characters
    function allowOnlyNumbersAndLimitLength(event) {
        const inputField = event.target;
        $('#account_name').prop('value', '');
        // Allow only digits (0-9)
        const validChars = /^[0-9]*$/;
        
        // Remove non-numeric characters
        if (!validChars.test(inputField.value)) {
            inputField.value = inputField.value.replace(/[^0-9]/g, '');
        }
        
        // Limit the length to 10 characters
        if (inputField.value.length > 10) {
            inputField.value = inputField.value.substring(0, 10);
        }
    }
    
    // Attach event listener to the input field
    $('#bank_account').on('input', allowOnlyNumbersAndLimitLength);
    
    // Update hidden input on change
    // retrive account name
    $('#bank').on('change', function() {
        
        var selectedOption = $(this).find(':selected'); // Get the selected option
        var bankName = selectedOption.html(); // Extract the 'data-name' attribute
        var bankCode = this.value;
        var accountNumber = $('#bank_account').val();
        $('#bank_name').val(bankName); // Set the value of the hidden input
        
        $.ajax({
            url: '{{ route('payslipitem.validate.account') }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                bankCode: bankCode,
                accountNumber: accountNumber
            },
            success: function (response) {
                if (response.success) {
                    $('#account_name').prop('value', response.data.accountName);
                } else {
                    alert('Error: ' + response.message);
                }
                console.log(response);
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                const errorMessage = xhr.responseJSON?.message || 'An unexpected error occurred.';
                alert('Error: ' + errorMessage);
            }
        });
    });
});
</script>


