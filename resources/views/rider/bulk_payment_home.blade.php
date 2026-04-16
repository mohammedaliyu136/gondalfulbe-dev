@extends('layouts.admin')

@php
    $profile = asset(Storage::url('uploads/avatar/'));
@endphp

@push('script-page')
    <script>
        $(document).on('click', '#billing_data', function() {
            $("[name='shipping_name']").val($("[name='billing_name']").val());
            $("[name='shipping_country']").val($("[name='billing_country']").val());
            $("[name='shipping_state']").val($("[name='billing_state']").val());
            $("[name='shipping_city']").val($("[name='billing_city']").val());
            $("[name='shipping_phone']").val($("[name='billing_phone']").val());
            $("[name='shipping_zip']").val($("[name='billing_zip']").val());
            $("[name='shipping_address']").val($("[name='billing_address']").val());
        });
    </script>
@endpush

@section('page-title')
    {{ __('Manage Riders Payments') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rider.index') }}">{{ __('Transport Crew') }}</a></li>
    <li class="breadcrumb-item">{{ __('Riders Payments') }}</li>
@endsection



@section('content')
<div class="row">
    <!-- Nav Tabs -->
<ul class="nav nav-tabs" id="tabMenu" role="tablist">
    <li class="nav-item" role="presentation">
        <button 
           class="nav-link active" 
            id="riders-tab" 
            data-bs-toggle="tab" 
            data-bs-target="#riders" 
            type="button" 
            role="tab" 
            aria-controls="riders" 
            aria-selected="true">
            Payment Batches
        </button>
    </li>
     <li class="nav-item" role="presentation">
        <button 
             class="nav-link" 
            id="payments-tab" 
            data-bs-toggle="tab" 
            data-bs-target="#payments" 
            type="button" 
            role="tab" 
            aria-controls="payments" 
            aria-selected="false">
            Generate Payment
        </button>
        
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content mt-3" id="tabContent">
    <!-- Disbursements Tab Pane -->
    <div class="tab-pane fade show active" id="riders" role="tabpanel" aria-labelledby="riders-tab">
        <div class="row">
        <div class="col-md-12">
            <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-4">
                    <div class="d-flex align-items-center justify-content-start mt-2">
                        <h5>{{ __('Payment Batches') }}</h5>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="d-flex align-items-center justify-content-end gap-2">
                        <div class="col-xl-2 col-lg-3 col-md-4">
                            <div class="btn-box">
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-4">
                            <div class="btn-box">
                            </div>
                        </div>
                        <div class="col-auto float-end">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable" id="pc-dt-render-column-cells">
                    <thead>
                        <tr>
                            <th>{{ __('Batch Id') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payslip_batches as $payslip_batch)
                            <tr>
                                <td class="Id">
                                    <a href="{{ route('rider.payslips.show', \Crypt::encrypt($payslip_batch->id)) }}" class="btn btn-outline-primary">{{ $payslip_batch->batch_id }}</a>
                                </td>
                                <td>
                                    <p class="text-danger mt-3">{{ $payslip_batch->batch_reference }}</p>
                                </td>
                                <td>
                                    @if ($payslip_batch->status == 0)
                                        <span class="status_badge badge bg-secondary p-2 px-3 rounded">
                                            {{ __(\App\Models\PaySlipFarmerBatch::$statues[$payslip_batch->status]) }}
                                        </span>
                                    @elseif($payslip_batch->status == 1)
                                        <span class="status_badge badge bg-warning p-2 px-3 rounded">
                                            {{ __(\App\Models\PaySlipFarmerBatch::$statues[$payslip_batch->status]) }}
                                        </span>
                                    @elseif($payslip_batch->status == 2)
                                        <span class="status_badge badge bg-danger p-2 px-3 rounded">
                                            {{ __(\App\Models\PaySlipFarmerBatch::$statues[$payslip_batch->status]) }}
                                        </span>
                                    @elseif($payslip_batch->status == 3)
                                        <span class="status_badge badge bg-info p-2 px-3 rounded">
                                            {{ __(\App\Models\PaySlipFarmerBatch::$statues[$payslip_batch->status]) }}
                                        </span>
                                    @elseif($payslip_batch->status == 4)
                                        <span class="status_badge badge bg-primary p-2 px-3 rounded">
                                            {{ __(\App\Models\PaySlipFarmerBatch::$statues[$payslip_batch->status]) }}
                                        </span>
                                    @elseif($payslip_batch->status == 5)
                                        <span class="status_badge badge bg-danger p-2 px-3 rounded">
                                            {{ __(\App\Models\PaySlipFarmerBatch::$statues[$payslip_batch->status]) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="Action">
                                    <div class="action-btn me-2">
                                        <a href="{{ route('rider.payslips.show', \Crypt::encrypt($payslip_batch->id)) }}"
                                           class="mx-3 btn btn-sm align-items-center bg-warning" 
                                           data-bs-toggle="tooltip" title="Show" 
                                           data-original-title="{{ __('Detail') }}">
                                            <i class="ti ti-eye text-white"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
        </div>
    </div>
    </div>
    <div class="tab-pane fade" id="payments" role="tabpanel" aria-labelledby="payments-tab">
        <div class="col-sm-12 col-lg-12 col-xl-12 col-md-12 mt-4">
    <div class="card">
        <div class="card-body">
            {{ Form::open(['route' => ['payslip.store'], 'method' => 'POST', 'id' => 'payslip_form']) }}
            <div class="d-flex align-items-center justify-content-between gap-2">
                <!-- Float Left -->
                <div class="col-auto float-start mt-4">
                    <div class="p-4">
                        <p class="card-text mb-0">Amount Awaiting</p>
                        <h6 class="report-text mb-3">₦{{ number_format($total_awaiting, 2, '.', ',') }}</h6>
                        <p class="card-text mb-0">Riders Count</p>
                        <h6 class="report-text mb-0">{{$riders_with_positive_balance}}</h6>
                    </div>
                </div>
                <!-- Float Right -->
                <div class="col-auto float-end mt-4">
                        @if($total_awaiting >10000000000)
                        @can('create pay slip')
                            <a href={{route('payslip.farmer.bulk_pay_create')}} class="btn btn-primary"
                               data-bs-toggle="tooltip" title="{{ __('payslip') }}"
                               data-original-title="{{ __('payslip') }}">{{ __('Create Bulk Payment') }}
                            </a>
                        @endcan
                        @endif
                    
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
        <form id="collectionCenterForm" method="POST" action="{{ route('rider.payslips.multiplecenter.bulk_pay_store') }}">
    @csrf
    <div class="row">
        @foreach ($collectionCenterPayment as $result) 
            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Checkbox at Top Right -->
                            <div class="col-auto text-end">
                                <input type="checkbox" name="collection_center[]" value="{{ $result->lga_id }}">
                            </div>
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-primary badge">
                                        <i class="ti ti-map-pin"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="m-0">
                                            <a href="" class="dashboard-link">{{ $result->collection_centre }}</a>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0">{{ number_format($result->total_balance, 2) }} </h4>
                                <small class="text-muted">Total Balance</small>
                            </div>
                        </div>
                        <div class="row align-items-center justify-content-between mt-2">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-success badge">
                                        <i class="ti ti-users"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="m-0">
                                           <a href="#" 
                                           data-size="lg" 
                                           data-url="{{ route('rider.payslips.lga.list', ['lga' => $result->lga_id]) }}" 
                                           data-ajax-popup="true" 
                                           data-title="{{ $result->collection_centre }}" 
                                           data-bs-toggle="tooltip" 
                                           title="{{ __('View list') }}" 
                                           class="dashboard-link">
                                           Riders with Positive Balance
                                        </a>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0">{{ $result->positive_balance_count }}</h4>
                                <small class="text-muted">Riders Count</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <button type="submit" class="btn btn-primary mt-3">Submit Selected</button>
</form>
    </div>
</div>   
@endsection
