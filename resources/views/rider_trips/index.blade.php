@extends('layouts.admin')

@push('script-page')
    <script>
        // Add any JS specific to rider trip here
    </script>
@endpush

@section('page-title')
    {{ __('Riders Trip') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Rider Trips') }}</li>
@endsection



@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Rider') }}</th>
                                    <th>{{ __('Trip Date') }}</th>
                                    <th>{{ __('Trip Count') }}</th>
                                    <th>{{ __('Amount/Trip') }}</th>
                                    <th>{{ __('Total Amount') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($trips as $trip)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $trip->rider->name ?? '-' }}</td>
                                        <td>{{ $trip->trip_date }}</td>
                                        <td>{{ $trip->trip_count }}</td>
                                        <td>{{ \Auth::user()->priceFormat($trip->amount_per_trip) }}</td>
                                        <td>{{ \Auth::user()->priceFormat($trip->total_amount) }}</td>
                                        <td class="Action">
                                            <span>
                                                @can('edit trip')
                                                    <a href="#" class="btn btn-sm bg-info text-white" data-ajax-popup="true"
                                                       data-title="{{ __('Edit Trip') }}"
                                                       data-url="{{ route('rider-trips.edit', $trip->id) }}"
                                                       data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil"></i>
                                                    </a>
                                                @endcan
                                                @can('delete trip')
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['rider-trips.destroy', $trip->id], 'id' => 'delete-form-' . $trip->id]) !!}
                                                        <a href="#" class="btn btn-sm bg-danger text-white bs-pass-para"
                                                           data-confirm="{{ __('Are You Sure?') . '|' . __('This action cannot be undone. Do you want to continue?') }}"
                                                           data-confirm-yes="document.getElementById('delete-form-{{ $trip->id }}').submit();"
                                                           data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                            <i class="ti ti-trash"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                @endcan
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
