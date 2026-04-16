@extends('layouts.admin')

@section('page-title')
    {{ __('Create Purchase Requisitions') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase-requisitions.index') }}">{{ __('Purchase Requisition') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create Purchase Requisitions') }}</li>
@endsection
@push('script-page')
<script src="{{asset('js/jquery-ui.min.js')}}"></script>
<script src="{{asset('js/jquery.repeater.min.js')}}"></script>
<script>
    $(document).ready(function () {
        var selector = "body";

        if ($(selector + " .repeater").length) {
            var $dragAndDrop = $("body .repeater tbody").sortable({
                handle: '.sort-handler'
            });

            var $repeater = $(selector + ' .repeater').repeater({
                initEmpty: false,
                defaultValues: {
                    'status': 1
                },
                show: function () {
                    $(this).slideDown();
                    $('.select2').select2();
                    updateTotalCost(); // Update total cost when adding new items
                },
                 hide: function (deleteElement) {
                    if (confirm('Are you sure you want to delete this element?')) {
                        $(this).slideUp(deleteElement);
                        $(this).remove();

                        var inputs = $(".amount");
                        var subTotal = 0;
                        for (var i = 0; i < inputs.length; i++) {
                            subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                        }
                        $('.subTotal').html(subTotal.toFixed(2));
                        //$('.totalAmount').html(subTotal.toFixed(2));
                    }
                },
                ready: function (setIndexes) {
                    $dragAndDrop.on('drop', setIndexes);
                },
                isFirstItemUndeletable: true
            });

            var value = $(selector + " .repeater").attr('data-value');
            if (typeof value !== 'undefined' && value.length !== 0) {
                value = JSON.parse(value);
                $repeater.setList(value);
            }


        }
        
        $(document).on('keyup', '.quantity', function () {
            var quntityTotalTaxPrice = 0;

            var el = $(this).parent().parent().parent().parent();
            var quantity = $(this).val();
            var price = $(el.find('.price')).val();

            var totalItemPrice = (quantity * price);
            var amount = (totalItemPrice);


            var amount = (totalItemPrice);


            $(el.find('.amount')).html(parseFloat(amount));


            var totalItemPrice = 0;
            var inputs_quantity = $(".quantity");

            var priceInput = $('.price');
            for (var j = 0; j < priceInput.length; j++) {
                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var inputs = $(".amount");

            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }

            $('.subTotal').html(totalItemPrice.toFixed(2));


            //$('.totalAmount').html((parseFloat(subTotal)).toFixed(2));

        })
        
        
        $(document).on('keyup change', '.price', function () {
            var el = $(this).parent().parent().parent().parent();
            var price = $(this).val();
            var quantity = $(el.find('.quantity')).val();


            var totalItemPrice = (quantity * price);

            var amount = (totalItemPrice);

            $(el.find('.amount')).html(parseFloat(amount));

            var totalItemPrice = 0;
            var inputs_quantity = $(".quantity");

            var priceInput = $('.price');
            for (var j = 0; j < priceInput.length; j++) {
                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var inputs = $(".amount");

            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }

            $('.subTotal').html(totalItemPrice.toFixed(2));

            //$('.totalAmount').html((parseFloat(subTotal)).toFixed(2));


        })

    });
</script>
@endpush


@section('content')
<div class="row">
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    {{ Form::open(['route' => ['purchase-requisitions.store'], 'method' => 'post', 'class'=>'needs-validation', 'novalidate']) }}
    <div class="">
        <div class="row">
            <div class="col-md-12">
                <div class="card em-card">
                    <div class="card-header">
                        <h5>{{ __('Requisition Details') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('title', __('Title/Description'), ['class' => 'form-label']) !!}<x-required></x-required>
                                {!! Form::text('title', old('title'), ['class' => 'form-control', 'required' => 'required' ,'placeholder'=>__('Title/Description')]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('requested_by', __('Requested By'), ['class' => 'form-label']) !!}<x-required></x-required>
                                {!! Form::text('requested_by', old('requested_by'), ['class' => 'form-control', 'required' => 'required' ,'placeholder'=>__('Requested by')]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('comment', __('Comment'), ['class' => 'form-label']) !!}
                                {!! Form::textarea('comment', old('comment'), ['class' => 'form-control', 'rows' => 3, 'placeholder'=>__('Enter comment')]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('priority', __('Priority'), ['class' => 'form-label']) !!}<x-required></x-required>
                                {!! Form::select('priority', ['Low' => __('Low'), 'Medium' => __('Medium'), 'High' => __('High')], old('priority'), ['class' => 'form-control', 'required' => 'required']) !!}
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <h5 class=" d-inline-block mb-4">{{__('Products')}}</h5>
            <div class="card repeater">
                <div class="item-section py-2">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                            <div class="all-button-box me-2">
                                <a href="#" data-repeater-create="" class="btn btn-primary">
                                    <i class="ti ti-plus"></i> {{__('Add item')}}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0" data-repeater-list="items" id="sortable-table">
                            <thead>
                                <tr>
                                    <th>{{__('Item Name')}}<x-required></x-required></th>
                                    <th>{{__('Quantity')}}<x-required></x-required></th>
                                    <th>{{__('Estimated Unit Price')}}<x-required></x-required></th>
                                    <th>{{__('Amount')}}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="ui-sortable" data-repeater-item>
                                <tr>
                                    <td width="30%" class="form-group">
                                        <div class="form-group price-input input-group search-form">
                                        {!! Form::text('name', '', ['class' => 'form-control', 'required' => 'required' ,'placeholder'=>__('Enter Item Name')]) !!}
                                        </div>
                                    </td>
                                    <td width="20%" class="form-group">
                                        <div class="form-group price-input input-group search-form">
                                        {!! Form::number('quantity', '', ['class' => 'form-control quantity', 'required' => 'required' ,'placeholder'=>__('Enter Quantity')]) !!}
                                        </div>
                                    </td>
                                    <td width="30%" class="form-group">

                                        <div class="form-group price-input input-group search-form">
                                        {{ Form::text('estimated_cost','', array('class' => 'form-control price','required'=>'required','placeholder'=>__('Price'),'required'=>'required')) }}
                                        <span class="input-group-text bg-transparent">{{\Auth::user()->currencySymbol()}}</span>
                                    </div>
                                    </td>
                                    <td width="10%">
                                        <div class="action-btn me-2">
                                            <a href="#" class="ti ti-trash text-white btn btn-sm repeater-action-btn bg-danger ms-2" data-repeater-delete></a>
                                        </div>
                                    </td>
                                    <td class="text-end amount">
                                        0.00
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td><strong>{{__('Total')}} ({{\Auth::user()->currencySymbol()}})</strong></td>
                                <td class="text-end subTotal">0.00</td>
                                <td></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="float-end">
            <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
        </div>
    </div>
    {!! Form::close() !!}
</div>
@endsection
