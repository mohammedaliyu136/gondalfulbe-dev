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
    {{ __('Manage Transport Crew') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Transport Crew') }}</li>
@endsection

@section('action-btn')
    <div class="float-end d-flex gap-2">
        @can('create rider')
            <a href="#" data-size="lg" data-url="{{ route('rider.create') }}" 
                data-ajax-popup="true" 
                data-title="{{ __('Create New') }}" 
                data-bs-toggle="tooltip" title="{{ __('Create') }}" 
                class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan

        
            <a href="{{ route('rider.payslips.home') }}" 
            title="{{ __('View list') }}" 
                class="btn btn-sm btn-primary">
                View Payments
            </a>
        
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{ __('Riders') }}</h5>
                <div class="card-tools">
                    <span class="badge bg-info">{{ $total_riders }} {{ __('Total Riders') }}</span>
                </div>
            </div>
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Balance') }}</th>
                                <th>{{ __('Draft Trips') }}</th>
                                <th>{{ __('Contact') }}</th>
                                <th>{{ __('State') }}</th>
                                <th>{{ __('LGA') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($riders as $k => $rider)
                                @php
                                    $pendingCount = $rider->pendingTrips()->count();
                                @endphp
                                <tr class="cust_tr" id="rider_detail">
                                    <td class="Id">
                                        @can('show rider')
                                            <a href="{{ route('rider.show', \Crypt::encrypt($rider['id'])) }}" class="btn btn-outline-primary">
                                                SEB-RD-00{{ $rider['rider_id'] }}
                                            </a>
                                        @else
                                            <a href="#" class="btn btn-outline-primary">SEB-RD-00{{ $rider['rider_id'] }}</a>
                                        @endcan
                                    </td>
                                    <td>{{ $rider['name'] }}</td>
                                    <td>
                                        <span class="fw-bold">₦{{ number_format($rider['balance'], 2) }}</span>
                                    </td>
                                    <td>
                                        @if($rider->pending_trips_count > 0)
                                            <span class="badge bg-secondary rounded-pill" data-bs-toggle="tooltip" title="{{ __('Pending Trips') }}">
                                                {{ $rider->pending_trips_count }}
                                            </span>
                                        @else
                                            <span class="badge bg-success rounded-pill">0</span>
                                        @endif
                                    </td>
                                    <td>{{ $rider['contact'] }}</td>
                                    <td>{{ $rider->state->name }}</td>
                                    <td>{{ $rider->lga->name }}</td>
                                    <td class="Action">
                                        <div class="d-flex">
                                            @if ($rider['is_active'] == 0)
                                                <i class="fas fa-lock text-danger me-2" title="Inactive" data-bs-toggle="tooltip"></i>
                                            @else
                                                @can('create trip')
                                                    <a href="#"
                                                        data-size="lg"
                                                        data-url="{{ route('rider-trips.create', \Crypt::encrypt($rider['id'])) }}"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Add Trip to Rider:') . ' ' . ucfirst($rider['name']) }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Add trip') }}"
                                                        class="btn btn-sm btn-icon bg-success me-2">
                                                        <i class="fas fa-road text-white"></i>
                                                    </a>
                                                @endcan
                                                
                                                @if($pendingCount > 0)
                                                    <a href="{{ route('rider.show', \Crypt::encrypt($rider['id'])) }}?tab=pending-trips"
                                                        class="btn btn-sm btn-icon bg-warning me-2"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Review Pending Trips') }}">
                                                        <i class="fas fa-clipboard-check text-white"></i>
                                                    </a>
                                                @endif
                                                
                                                @can('show rider')
                                                    <a href="{{ route('rider.show', \Crypt::encrypt($rider['id'])) }}" 
                                                        class="btn btn-sm btn-icon bg-info me-2" 
                                                        data-bs-toggle="tooltip" 
                                                        title="{{ __('View') }}">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                @endcan
                                                
                                                @can('edit rider')
                                                    <a href="#" 
                                                        class="btn btn-sm btn-icon bg-primary me-2" 
                                                        data-size="lg"
                                                        data-title="{{ __('Edit Rider') }}"
                                                        data-url="{{ route('rider.edit', $rider['id']) }}"
                                                        data-ajax-popup="true" 
                                                        title="{{ __('Edit') }}"
                                                        data-bs-toggle="tooltip">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                @endcan
                                                
                                                @can('delete rider')
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['rider.destroy', $rider['id']], 'id' => 'delete-form-' . $rider['id']]) !!}
                                                        <a href="#" 
                                                            class="btn btn-sm btn-icon bg-danger" 
                                                            data-bs-toggle="tooltip"
                                                            data-original-title="{{ __('Delete') }}" 
                                                            title="{{ __('Delete') }}"
                                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $rider['id'] }}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                @endcan
                                            @endif
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

@push('css')
<style>
    .badge {
        font-size: 0.85rem;
        padding: 0.35em 0.65em;
    }
    .btn-icon {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .cust_tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
</style>
@endpush
