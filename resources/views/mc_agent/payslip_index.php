@extends('layouts.admin')

@section('page-title')
    {{ __('Riders Payslip') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rider') }}">{{ __('Riders') }}</a></li> 
    <li class="breadcrumb-item">{{ __(' riders payslip') }}</li>
@endsection

@section('content')
<div class="row">
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
                                    <a href="{{ route('payslip.farmer.show', \Crypt::encrypt($payslip_batch->id)) }}" class="btn btn-outline-primary">{{ $payslip_batch->batch_id }}</a>
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
                                        <a href="{{ route('payslip.farmer.show', \Crypt::encrypt($payslip_batch->id)) }}"
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
</row>
    @endsection