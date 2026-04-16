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
    {{ __('Manage Milk Collectin Agents') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Milk Collection Agent') }}</li>
@endsection

@section('action-btn')
    <div class="float-end d-flex gap-2">
        @can('create rider')
            <a href="#" data-size="lg" data-url="{{ route('mcagent.create') }}" data-ajax-popup="true" data-title="{{ __('Create New Agent') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan

        @can('manage payment mc officer')
            <a href="#" 
                data-size="lg" 
                data-url="{{ route('mcagent.payslips.create') }}" 
                data-ajax-popup="true" 
                data-title="{{ __('Create Agent Payslip') }}" 
                data-bs-toggle="tooltip" 
                title="{{ __('View list') }}" 
                class="btn btn-sm btn-primary">
                Generate Payslip
            </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Nav Tabs -->
<ul class="nav nav-tabs" id="tabMenu" role="tablist">
    <li class="nav-item" role="presentation">
        <button 
            class="nav-link active" 
            id="mcagent-tab" 
            data-bs-toggle="tab" 
            data-bs-target="#mcagent" 
            type="button" 
            role="tab" 
            aria-controls="mcagent" 
            aria-selected="true">
            MC Agents
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
            Payment Batches
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content mt-3" id="tabContent">
    <!-- Disbursements Tab Pane -->
    <div class="tab-pane fade show active" id="mcagent" role="tabpanel" aria-labelledby="mcagent-tab">
        <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Contact') }}</th>
                                    <th>{{ __('Collection Centre') }}</th>
                                    <th>{{ __('Location') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($agents as $k => $agent)
                                    <tr class="cust_tr" id="rider_detail">
                                        <td class="Id">
                                            @can('show rider')
                                                <a href="{{ route('mcagent.show', \Crypt::encrypt($agent['id'])) }}" class="btn btn-outline-primary">
                                                    SEB-AG-00{{ $agent['agent_id'] }}
                                                </a>
                                            @else
                                                <a href="#" class="btn btn-outline-primary">SEB-RD-00{{ $rider['agent_id'] }}</a>
                                            @endcan
                                        </td>
                                        <td>{{ $agent['name'] }}</td>
                                        <td>{{ $agent['contact'] }}</td>
                                        <td>{{ $agent['collection_centre'] }}</td>
                                        <td>{{ $agent['billing_city'] }}</td>
                                        <td class="Action">
                                            <span>
                                                @if ($agent['is_active'] == 0)
                                                    <i class="fa fa-lock" title="Inactive"></i>
                                                @else
                                                    @can('show rider')
                                                        <div class="action-btn me-2">
                                                            <a href="{{ route('mcagent.show', \Crypt::encrypt($agent['id'])) }}" class="mx-3 btn btn-sm align-items-center bg-warning" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                                <i class="ti ti-eye text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('edit rider')
                                                        <div class="action-btn me-2">
                                                            <a href="#" class="mx-3 btn btn-sm align-items-center bg-info" data-size="lg"
                                                                data-title="{{ __('Edit Milk Collection Agent') }}"
                                                                data-url="{{ route('mcagent.edit', $agent['id']) }}"
                                                                data-ajax-popup="true" title="{{ __('Edit') }}"
                                                                data-bs-toggle="tooltip">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('delete rider')
                                                        <div class="action-btn">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['mcagent.destroy', $agent->id], 'id' => 'delete-form-' . $agent['id']]) !!}
                                                                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger" data-bs-toggle="tooltip"
                                                                    data-original-title="{{ __('Delete') }}" title="{{ __('Delete') }}"
                                                                    data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="document.getElementById('delete-form-{{ $agent['id'] }}').submit();">
                                                                    <i class="ti ti-trash text-white"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                @endif
                                            </span>
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
        <div class="col-12">
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
                            <th>{{ __('Batch Type') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payslip_batches as $payslip_batch)
                            <tr>
                                <td class="Id">
                                    <a href="{{ route('mcagent.payslips.show', \Crypt::encrypt($payslip_batch->id)) }}" class="btn btn-outline-primary">{{ $payslip_batch->batch_id }}</a>
                                </td> 
                                <td>
                                    @if($payslip_batch->batch_type == 'failed_reprocess')
                                        @php
                                            $parentBatch = \App\Models\PaySlipRiderBatch::find($payslip_batch->parent_batch_id);
                                        @endphp
                                        <i class="ti ti-alert-circle text-danger"></i> 
                                        <span class="text-danger">Failed Reprocess - {{ $parentBatch->batch_id ?? 'N/A' }}</span>
                                    @else
                                        <i class="ti ti-check-circle text-success"></i> 
                                        <span class="text-success">Regular</span>
                                    @endif
                                </td>
                                <td> 
                                    <p class="text-danger mt-3">{{ $payslip_batch->batch_reference }}</p>
                                </td>
                                <td>
                                    @if ($payslip_batch->status == 0)
                                        <span class="status_badge badge bg-secondary p-2 px-3 rounded">
                                            {{ __(\App\Models\PaySlipRiderBatch::$statues[$payslip_batch->status]) }}
                                        </span>
                                    @elseif($payslip_batch->status == 1)
                                        <span class="status_badge badge bg-warning p-2 px-3 rounded">
                                            {{ __(\App\Models\PaySlipRiderBatch::$statues[$payslip_batch->status]) }}
                                        </span>
                                    @elseif($payslip_batch->status == 2)
                                        <span class="status_badge badge bg-danger p-2 px-3 rounded">
                                            {{ __(\App\Models\PaySlipRiderBatch::$statues[$payslip_batch->status]) }}
                                        </span>
                                    @elseif($payslip_batch->status == 3)
                                        <span class="status_badge badge bg-info p-2 px-3 rounded">
                                            {{ __(\App\Models\PaySlipRiderBatch::$statues[$payslip_batch->status]) }}
                                        </span>
                                    @elseif($payslip_batch->status == 4)
                                        <span class="status_badge badge bg-primary p-2 px-3 rounded">
                                            {{ __(\App\Models\PaySlipRiderBatch::$statues[$payslip_batch->status]) }}
                                        </span>
                                    @elseif($payslip_batch->status == 5)
                                        <span class="status_badge badge bg-danger p-2 px-3 rounded">
                                            {{ __(\App\Models\PaySlipRiderBatch::$statues[$payslip_batch->status]) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="Action">
                                    <div class="action-btn me-2">
                                        <a href="{{ route('mcagent.payslips.show', \Crypt::encrypt($payslip_batch->id)) }}"
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
    
@endsection
