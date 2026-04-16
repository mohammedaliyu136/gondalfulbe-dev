@extends('layouts.admin')

@section('page-title')
    {{ __('Cooperatives') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Cooperatives') }}</li>
@endsection

@section('action-btn')
    @can('create cooperative')
        <a href="#" data-url="{{ route('cooperatives.create') }}" data-ajax-popup="true"
           data-bs-toggle="tooltip" title="{{ __('Add Cooperative') }}"
           class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
    @endcan

    @can('create cooperative')
        <a href="#" data-url="{{ route('cooperatives.import.form') }}" data-ajax-popup="true"
           data-bs-toggle="tooltip" title="{{ __('Import CSV') }}"
           class="btn btn-sm btn-info">
            <i class="ti ti-file-import"></i>
        </a>
    @endcan

    @can('manage cooperative')
        <a href="{{ route('cooperatives.export') }}"
           data-bs-toggle="tooltip" title="{{ __('Export Excel') }}"
           class="btn btn-sm btn-success">
            <i class="ti ti-file-export"></i>
        </a>
    @endcan
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Location (MCC)') }}</th>
                                    <th>{{ __('Leader') }}</th>
                                    <th>{{ __('Phone') }}</th>
                                    <th>{{ __('Formation Date') }}</th>
                                    <th>{{ __('Avg Supply (L)') }}</th>
                                    <th>{{ __('Members') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cooperatives as $cooperative)
                                    <tr>
                                        <td>{{ $cooperative->code ?? '—' }}</td>
                                        <td>{{ $cooperative->name }}</td>
                                        <td>{{ $cooperative->location ?? '—' }}</td>
                                        <td>{{ $cooperative->leader_name ?? '—' }}</td>
                                        <td>{{ $cooperative->leader_phone ?? '—' }}</td>
                                        <td>
                                            {{ $cooperative->formation_date
                                                ? $cooperative->formation_date->format('d M Y')
                                                : '—' }}
                                        </td>
                                        <td>{{ number_format((float) $cooperative->average_daily_supply, 2) }}</td>
                                        <td>{{ $cooperative->farmers_count }}</td>
                                        <td>
                                            @if ($cooperative->status === 'active')
                                                <span class="badge bg-success p-2 px-3 rounded">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-danger p-2 px-3 rounded">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @can('manage cooperative')
                                                <a href="{{ route('cooperatives.show', $cooperative->id) }}"
                                                   data-bs-toggle="tooltip" title="{{ __('View') }}"
                                                   class="btn btn-sm btn-info">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            @endcan

                                            @can('edit cooperative')
                                                <a href="#"
                                                   data-url="{{ route('cooperatives.edit', $cooperative->id) }}"
                                                   data-ajax-popup="true"
                                                   data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            @endcan

                                            @can('manage cooperative')
                                                <a href="{{ route('cooperatives.farmers.export', $cooperative->id) }}"
                                                   data-bs-toggle="tooltip" title="{{ __('Export Farmers') }}"
                                                   class="btn btn-sm btn-success">
                                                    <i class="ti ti-users text-white"></i>
                                                </a>
                                            @endcan

                                            @can('delete cooperative')
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['cooperatives.destroy', $cooperative->id], 'id' => 'delete-form-' . $cooperative->id]) !!}
                                                <a href="#" class="bs-pass-para"
                                                   data-confirm="{{ __('Are you sure?') }}"
                                                   data-text="{{ __('This will permanently delete the cooperative.') }}"
                                                   data-confirm-yes="document.getElementById('delete-form-{{ $cooperative->id }}').submit();"
                                                   data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                   class="btn btn-sm btn-danger">
                                                    <i class="ti ti-trash text-white"></i>
                                                </a>
                                                {!! Form::close() !!}
                                            @endcan
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
