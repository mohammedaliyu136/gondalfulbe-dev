@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{__('Manage Service provider Detail')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('service-provider.index')}}">{{__('Service Provider')}}</a></li>
    <li class="breadcrumb-item">{{$vendor['name']}}</li>

@endsection

@section('action-btn')
    <div class="float-end d-flex">

        @can('edit service provider')
            <a href="#" class="btn btn-sm btn-primary me-2" data-size="xl" data-url="{{ route('service-provider.edit',$vendor['id']) }}" data-ajax-popup="true" title="{{__('Edit')}}" data-bs-toggle="tooltip" data-original-title="{{__('Edit')}}">
                <i class="ti ti-pencil"></i>
            </a>
        @endcan
        @can('delete service provider')
            {!! Form::open(['method' => 'DELETE', 'route' => ['service-provider.destroy', $vendor['id']],'class'=>'delete-form-btn','id'=>'delete-form-'.$vendor['id']]) !!}
            <a href="#" class="btn btn-sm btn-danger bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{ $vendor['id']}}').submit();">
                <i class="ti ti-trash text-white"></i>
            </a>
            {!! Form::close() !!}
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4 col-lg-4 col-xl-4">
            <div class="card pb-0 customer-detail-box vendor_card">
                <div class="card-body">
                    <h5 class="card-title">{{__('Service provider Info')}}</h5>
                    <p class="card-text mb-0">Name: {{$vendor->name}}</p>
                    <p class="card-text mb-0">Email: {{$vendor->email}}</p>
                    <p class="card-text mb-0">Phone: {{$vendor->contact}}</p>
                    <p class="card-text mb-0">Bank: {{$vendor->bank_name}}</p>
                    <p class="card-text mb-0">Account No: {{$vendor->bank_account}}</p>
                    <p class="card-text mb-0">Account Name: {{$vendor->account_name}}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4 col-xl-4">
            <div class="card pb-0 customer-detail-box vendor_card">
                <div class="card-body">
                    <h5 class="card-title">{{__('Address Info')}}</h5>
                    <p class="card-text mb-0">{{$vendor->billing_address}}</p>
                    <p class="card-text mb-0">{{$vendor->billing_city.', '. $vendor->billing_state .', '.$vendor->billing_zip}}</p>
                    <p class="card-text mb-0">{{$vendor->billing_country}}</p>
                    <p class="card-text mb-0">{{$vendor->billing_phone}}</p>
                    <p class="card-text mb-0">&nbsp;</p>


                </div>
            </div>
        </div>
       <div class="col-md-4 col-lg-4 col-xl-4">
            <div class="image-container text-center" style="max-width: 200px; overflow: hidden;">
                @if ($vendor->image)
                    <!-- Use asset() to generate the full URL -->
                    <img src="{{ asset('public/uploads/service_providers/' . $vendor->image) }}" alt="{{ $vendor->name }}" class="img-fluid rounded" style="max-width: 100%; height: auto;">
                @else
                    <span>No Image Available</span>
                @endif
            </div>
        </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card pb-0">
                <div class="card-body">
                    <h5 class="card-title">{{__('Other Details')}}</h5>
                    <div class="row">

                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0">{{__('Service Provider Id')}}</p>
                                <h6 class="report-text mb-3">#SP0{{$vendor->vender_id}}</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0">{{__('Date of Creation')}}</p>
                                <h6 class="report-text mb-3">{{\Auth::user()->dateFormat($vendor->created_at)}}</h6>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
