@extends('layouts.admin')
@push('script-page')
@endpush

@section('page-title')
    {{__('Manage Milk Collection Agent-Detail')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('mcagent.index')}}">{{__('Milk Collection Agent')}}</a></li>
    <li class="breadcrumb-item">{{$agent['name']}}</li>
@endsection

@section('action-btn')
    <div class="float-end d-flex">
        @can('edit rider')
            <a href="#" class="btn btn-sm btn-primary me-2" data-size="xl" data-url="{{ route('mcagent.edit',$agent['id']) }}" data-ajax-popup="true" title="{{__('Edit')}}" data-bs-toggle="tooltip" data-original-title="{{__('Edit')}}">
                <i class="ti ti-pencil"></i>
            </a>
        @endcan
        @can('delete rider')
            {!! Form::open(['method' => 'DELETE', 'route' => ['mcagent.destroy', $agent['id']],'class'=>'delete-form-btn','id'=>'delete-form-'.$agent['id']]) !!}
            <a href="#" class="btn btn-sm btn-danger bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{ $agent['id']}}').submit();">
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
                    <h5 class="card-title">{{__('Agent Info')}}</h5>
                    <p class="card-text mb-0">Name: {{$agent->name}}</p>
                    <p class="card-text mb-0">Email: {{$agent->email}}</p>
                    <p class="card-text mb-0">Phone: {{$agent->contact}}</p>
                    <p class="card-text mb-0">Bank: {{$agent->bank_name}}</p>
                    <p class="card-text mb-0">Account No: {{$agent->bank_account}}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4 col-xl-4">
            <div class="card pb-0 customer-detail-box rider_card">
                <div class="card-body">
                    <h5 class="card-title">{{__('Address Info')}}</h5>
                    <p class="card-text mb-0">{{$agent->billing_address}}</p>
                    <p class="card-text mb-0">{{$agent->billing_city.', '. $agent->billing_state .', '.$agent->billing_zip}}</p>
                    <p class="card-text mb-0">{{$agent->billing_country}}</p>
                    <p class="card-text mb-0">{{$agent->billing_phone}}</p>
                    <p class="card-text mb-0">Collection Centre: {{$agent->collection_centre}}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4 col-xl-4">
            <div class="image-container text-center" style="max-width: 200px; overflow: hidden;">
                @if ($agent->image)
                    <img src="{{ asset('public/uploads/mcagents/' . $agent->image) }}" alt="{{ $agent->name }}" class="img-fluid rounded" style="max-width: 100%; height: auto;">
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
                    <h5 class="card-title">{{__('Agents Details')}}</h5>
                    <div class="row">

                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0">{{__('Agent Id')}}</p>
                                <h6 class="report-text mb-3">SEB-RD-00{{ $agent->rider_id }}</h6>

                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0">{{__('Date of Creation')}}</p>
                                <h6 class="report-text mb-3">{{\Auth::user()->dateFormat($agent->created_at)}}</h6>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
