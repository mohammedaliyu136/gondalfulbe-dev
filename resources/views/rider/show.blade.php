@extends('layouts.admin')
@push('script-page')
@endpush

@section('page-title')
    {{__(ucfirst($rider['type']).' Detail')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('rider.index')}}">{{__('Transport crew')}}</a></li>
    <li class="breadcrumb-item">{{$rider['name']}}</li>
@endsection

@section('action-btn')
    <div class="float-end d-flex">
        @can('edit rider')
            <a href="#" class="btn btn-sm btn-primary me-2" data-size="xl" data-url="{{ route('rider.edit',$rider['id']) }}" data-ajax-popup="true" title="{{__('Edit')}}" data-bs-toggle="tooltip" data-original-title="{{__('Edit')}}">
                <i class="ti ti-pencil"></i>
            </a>
        @endcan
        @can('delete rider')
            {!! Form::open(['method' => 'DELETE', 'route' => ['rider.destroy', $rider['id']],'class'=>'delete-form-btn','id'=>'delete-form-'.$rider['id']]) !!}
            <a href="#" class="btn btn-sm btn-danger bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{ $rider['id']}}').submit();">
                <i class="ti ti-trash text-white"></i>
            </a>
            {!! Form::close() !!}
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4 col-lg-4 col-xl-4">
            <div class="card pb-0 customer-detail-box rider_card">
                <div class="card-body">
                    <h5 class="card-title">{{__(ucfirst($rider['type']). ' Info')}}</h5>
                    <p class="card-text mb-0">Name: {{$rider->name}}</p>
                    <p class="card-text mb-0">Email: {{$rider->email}}</p>
                    <p class="card-text mb-0">Phone: {{$rider->contact}}</p>
                    <p class="card-text mb-0">Bank: {{$rider->bank_name}}</p>
                    <p class="card-text mb-0">Account No: {{$rider->bank_account}}</p>
                    <p class="card-text mb-0">Account Name: {{$rider->account_name}}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4 col-xl-4">
            <div class="card pb-0 customer-detail-box rider_card">
                <div class="card-body">
                    <h5 class="card-title">{{__('Address Info')}}</h5>
                    <p class="card-text mb-0">{{$rider->billing_address}}</p>
                    <p class="card-text mb-0">{{$rider->billing_city.', '. $rider->billing_state .', '.$rider->billing_zip}}</p>
                    <p class="card-text mb-0">{{$rider->billing_country}}</p>
                    <p class="card-text mb-0">{{$rider->billing_phone}}</p>
                    <p class="card-text mb-0">State: {{$rider->state->name}}</p>
                    <p class="card-text mb-0">L.G.A: {{$rider->lga->name}}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4 col-xl-4">
            <div class="image-container text-center" style="max-width: 200px; overflow: hidden;">
                @if ($rider->image)
                    <img src="{{ asset('public/uploads/riders/' . $rider->image) }}" alt="{{ $rider->name }}" class="img-fluid rounded" style="max-width: 100%; height: auto;">
                @else
                    <span>No Image Available</span>
                @endif
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card pb-0">
                <div class="card-body">
                    <h5 class="card-title">{{__(ucfirst($rider['type']).' Details')}}</h5>
                    <div class="row">
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0">{{__(ucfirst($rider['type']). 'Id')}}</p>
                                <h6 class="report-text mb-3">SEB-RD-00{{ $rider->rider_id }}</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0">{{__('Amount Per Trip')}}</p>
                                <h6 class="report-text mb-3 text-info">₦{{ $rider->amount_per_trip }}</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0">{{__('Account Balance')}}</p>
                                <h6 class="report-text mb-3 text-success">₦{{ $rider->balance }}</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0">{{__('Date of Creation')}}</p>
                                <h6 class="report-text mb-3">{{\Auth::user()->dateFormat($rider->created_at)}}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('Trips') }}</h5>
        <div class="card-tools">
            <div class="btn-group btn-group-sm" role="group">
                <a class="btn btn-outline-primary {{ request()->get('tab') != 'pending-trips' ? 'active' : '' }}" 
                   href="{{ route('rider.show', \Crypt::encrypt($rider->id)) }}">
                   <i class="ti ti-list me-1"></i>
                   {{ __('All Trips') }}
                   <span class="badge bg-primary ms-1">{{ $rider->trips->count() }}</span>
                </a>
                <a class="btn btn-outline-warning {{ request()->get('tab') == 'pending-trips' ? 'active' : '' }}" 
                   href="{{ route('rider.show', \Crypt::encrypt($rider->id)) }}?tab=pending-trips">
                   <i class="ti ti-edit me-1"></i>
                   {{ __('Draft Trips') }}
                   <span class="badge bg-warning ms-1">{{ $rider->pendingTrips->count() }}</span>
                </a>
            </div>
        </div>
    </div>
</div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Trip Date') }}</th>
                                    <th>{{ __('Trip Count') }}</th>
                                    <th>{{ __('Amount/Trip') }}</th>
                                    <th>{{ __('Total Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($trips as $trip)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $trip->trip_date }}</td>
                                        <td>{{ $trip->trip_count }}</td>
                                        <td>{{ \Auth::user()->priceFormat($trip->amount_per_trip) }}</td>
                                        <td>{{ \Auth::user()->priceFormat($trip->total_amount) }}</td>
                                        <td>
                                            @if ($trip->status == 0)
                                                <span class="status_badge badge bg-secondary p-2 px-3 rounded">
                                                    {{ __(\App\Models\RiderTrip::$statues[$trip->status]) }}
                                                </span>
                                            @elseif($trip->status == 1)
                                                <span class="status_badge badge bg-warning p-2 px-3 rounded">
                                                    {{ __(\App\Models\RiderTrip::$statues[$trip->status]) }}
                                                </span>
                                            @elseif($trip->status == 2)
                                                <span class="status_badge badge p-2 px-3 rounded" style="background-color: red !important; color: white !important;">
                                                    {{ __(\App\Models\RiderTrip::$statues[$trip->status]) }}
                                                </span>
                                            @elseif($trip->status == 3)
                                                <span class="status_badge badge bg-primary p-2 px-3 rounded">
                                                    {{ __(\App\Models\RiderTrip::$statues[$trip->status]) }}
                                                </span>
                                            @elseif($trip->status == 4)
                                                <span class="status_badge badge bg-danger p-2 px-3 rounded">
                                                    {{ __(\App\Models\RiderTrip::$statues[$trip->status]) }}
                                                </span>
                                            @elseif($trip->status == 5)
                                                <span class="status_badge badge bg-success p-2 px-3 rounded">
                                                    {{ __(\App\Models\RiderTrip::$statues[$trip->status]) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="Action">
                                            <span>
                                               
                                                @if($trip->status == 0)
                                                @can('validate trip')
                                                <a href="#" class="btn btn-sm bg-warning text-white" data-ajax-popup="true"
                                                       data-title="{{ __('Validate Trip') }}"
                                                       data-url="{{ route('rider-trips.validate-trip', \Crypt::encrypt($trip->id)) }}"
                                                       data-bs-toggle="tooltip" title="{{ __('Validate') }}">
                                                        <i class="ti ti-check"></i>
                                                </a>
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
@endsection

@push('css')
<style>
    .btn-group .btn {
        border-radius: 0.25rem;
        margin-right: 0.25rem;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }
    .btn-group .btn.active {
        color: #fff !important;
    }
    .btn-group .btn-outline-primary.active {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    .btn-group .btn-outline-warning.active {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #000 !important;
    }
    .btn-group .btn i {
        font-size: 0.875rem;
    }
</style>
@endpush