{{ Form::open(array('url' => 'vender', 'method' => 'post', 'class' => 'needs-validation', 'novalidate', 'enctype' => 'multipart/form-data')) }}
<div class="modal-body">

    <h6 class="sub-title">{{__('New Farmer Info')}}</h6>
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('name',__('Full Name'),array('class'=>'form-label')) }}<x-required></x-required>
                {{Form::text('name',null,array('class'=>'form-control','required'=>'required' , 'placeholder'=>__('Enter Name')))}}

            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{-- {{Form::label('contact',__('Phone No:'),['class'=>'form-label'])}}<x-required></x-required>
                {{Form::number('contact',null,array('class'=>'form-control','required'=>'required' , 'placeholder' => __('Enter Phone No:')))}} --}}
                <x-mobile label="{{__('Phone No:')}}" name="contact" value="{{old('contact')}}" required placeholder="Enter Phone"></x-mobile>

            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('email',__('Email'),['class'=>'form-label'])}}
                {{Form::email('email',null,array('class'=>'form-control','required'=>'required' , 'placeholder' => __('Enter email')))}}
            </div>
        </div>


        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('tax_number',__('NIN Number'),['class'=>'form-label'])}}
                {{Form::text('tax_number',null,array('class'=>'form-control' , 'placeholder'=>__('Enter NIN Number')))}}
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('gender',__('Gender'),['class'=>'form-label'])}}
                {{Form::select('gender',[''=>__('Select Gender'),'M'=>__('Male'),'F'=>__('Female'),'Other'=>__('Other')],null,['class'=>'form-control'])}}
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('dob',__('Date of Birth'),['class'=>'form-label'])}}
                {{Form::date('dob',null,['class'=>'form-control'])}}
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('gps_lat',__('GPS Latitude'),['class'=>'form-label'])}}
                {{Form::text('gps_lat',null,['class'=>'form-control','placeholder'=>__('e.g. 9.0579')])}}
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('gps_lng',__('GPS Longitude'),['class'=>'form-label'])}}
                {{Form::text('gps_lng',null,['class'=>'form-control','placeholder'=>__('e.g. 12.4898')])}}
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('photo',__('Profile Photo'),['class'=>'form-label'])}}
                {{Form::file('photo',['class'=>'form-control','accept'=>'image/*'])}}
                <small class="text-muted">{{__('Accepted: jpeg, png, jpg')}}</small>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group d-flex align-items-center mt-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="digital_payment_flag" name="digital_payment_flag" value="1">
                    <label class="form-check-label" for="digital_payment_flag">{{__('Enable Digital Payments')}}</label>
                </div>
            </div>
        </div>
       <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('bank_account',__('Bank Account'),['class'=>'form-label'])}}<x-required></x-required>
                {{Form::text('bank_account',null,array('class'=>'form-control', 'required'=>'required', 'placeholder'=>__('Enter Bank Account')))}}
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{ Form::label('bank', __('Select Bank'), ['class' => 'form-label']) }}<x-required></x-required>
                <select id="bank" name="bank_code" class="form-control select2" required>
                    <option value="">{{ __('Select a Bank') }}</option>
                    @foreach ($bankList as $bank)
                        <option value="{{ $bank['code'] }}" data-name="{{ $bank['name'] }}">{{ $bank['name'] }}</option>
                    @endforeach
                </select>
                <input type="hidden" id="bank_name" name="bank_name" value="">
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('account_name',__('Account Name'),['class'=>'form-label'])}}<x-required></x-required>
                {{Form::text('account_name',null,array('class'=>'form-control' , 'required'=>'required', 'readonly'=>'readonly', 'placeholder'=>__('Not set')))}}
            </div>
        </div>
        @if(!$customFields->isEmpty())
            {{-- <div class="col-lg-4 col-md-4 col-sm-6"> --}}
                {{-- <div class="tab-pane fade show" id="tab-2" role="tabpanel"> --}}
                    @include('customFields.formBuilder')
                {{-- </div> --}}
            {{-- </div> --}}
        @endif
    </div>
    <h6 class="sub-title">{{__('Billing Address')}}</h6>
    <div class="row">
        <!--<div class="col-lg-6 col-md-6 col-sm-6">-->
        <!--    <div class="form-group">-->
        <!--        {{Form::label('billing_name',__('Name'),array('class'=>'form-label')) }}-->
        <!--        {{Form::text('billing_name',null,array('class'=>'form-control' , 'placeholder'=>__('Enter Name')))}}-->

        <!--    </div>-->
        <!--</div>-->
        <!--<div class="col-lg-6 col-md-6 col-sm-6">-->
        <!--    <div class="form-group">-->
        <!--        {{Form::label('billing_phone',__('Phone'),array('class'=>'form-label')) }}-->
        <!--        {{Form::text('billing_phone',null,array('class'=>'form-control' , 'placeholder' => __('Enter Phone')))}}-->

        <!--    </div>-->
        <!--</div>-->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{Form::label('billing_country',__('Country'),array('class'=>'form-label')) }}
                {{Form::text('billing_country',null,array('class'=>'form-control' , 'placeholder' => __('Enter Country')))}}

            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{Form::label('billing_state',__('State'),array('class'=>'form-label')) }}
                {{Form::text('billing_state',null,array('class'=>'form-control' , 'placeholder'=>__('Enter State')))}}
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{Form::label('billing_city',__('City'),array('class'=>'form-label')) }}
                {{Form::text('billing_city',null,array('class'=>'form-control' , 'placeholder' => __('Enter City')))}}
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="form-group">
                    {{ Form::label('collection_centre', __('Collection Centre'), ['class' => 'form-label']) }}
                    {{ Form::select('collection_centre', $warehouses->pluck('name', 'id'), null, ['class' => 'form-control', 'placeholder' => __('Select Collection Centre')]) }}
                </div>
            </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('cooperative_id', __('Cooperative'), ['class' => 'form-label']) }}
                {{ Form::select('cooperative_id', $cooperatives->pluck('name', 'id'), null, ['class' => 'form-control select2', 'placeholder' => __('Select Cooperative (Optional)')]) }}
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('billing_address',__('Address'),array('class'=>'form-label')) }}
                {{Form::textarea('billing_address',null,array('class'=>'form-control','rows'=>3 , 'placeholder' => __('Enter Address')))}}
            </div>
        </div>

        
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="form-group">
                    {{Form::label('image',__('Upload Image'),['class'=>'form-label'])}}
                    {{Form::file('image', ['class' => 'form-control', 'accept' => 'image/*'])}}
                    <small class="text-muted">{{__('Accepted file types: jpeg, png, jpg')}}</small>
                </div>
            </div>
        


    </div>

   

</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-secondary" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{Form::close()}}


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
        alert()
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

