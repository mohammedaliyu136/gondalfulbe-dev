@extends('layouts.admin')

@section('page-title')
    {{ __('Milk Collections') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Milk Collection') }}</li>
@endsection

@section('action-btn')
    @can('create milk collection')
        <a href="#" data-url="{{ route('milk-collections.create') }}" data-ajax-popup="true"
           data-bs-toggle="tooltip" title="{{ __('Add Collection') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
    @can('manage milk collection')
        <a href="{{ route('milk-collections.export') }}" class="btn btn-sm btn-success"
           data-bs-toggle="tooltip" title="{{ __('Export CSV') }}">
            <i class="ti ti-file-export"></i>
        </a>
    @endcan
@endsection

@section('content')
<div class="row">
    <!-- Summary Cards -->
    <div class="col-xl-3 col-sm-6"><div class="card">
        <div class="card-body"><div class="d-flex align-items-center">
            <div class="theme-avtar bg-primary"><i class="ti ti-droplet text-white fs-5"></i></div>
            <div class="ms-3"><h6 class="mb-0">{{ __("Today's Litres") }}</h6>
                <h4 class="mb-0">{{ number_format($todayLitres, 2) }} L</h4></div>
        </div></div>
    </div></div>
    <div class="col-xl-3 col-sm-6"><div class="card">
        <div class="card-body"><div class="d-flex align-items-center">
            <div class="theme-avtar bg-success"><i class="ti ti-users text-white fs-5"></i></div>
            <div class="ms-3"><h6 class="mb-0">{{ __("Today's Farmers") }}</h6>
                <h4 class="mb-0">{{ $todayFarmers }}</h4></div>
        </div></div>
    </div></div>
    <div class="col-xl-3 col-sm-6"><div class="card">
        <div class="card-body"><div class="d-flex align-items-center">
            <div class="theme-avtar bg-info"><i class="ti ti-star text-white fs-5"></i></div>
            <div class="ms-3"><h6 class="mb-0">{{ __('Grade A %') }}</h6>
                <h4 class="mb-0">{{ $gradeAPct }}%</h4></div>
        </div></div>
    </div></div>
    <div class="col-xl-3 col-sm-6"><div class="card">
        <div class="card-body"><div class="d-flex align-items-center">
            <div class="theme-avtar bg-warning"><i class="ti ti-building text-white fs-5"></i></div>
            <div class="ms-3"><h6 class="mb-0">{{ __('Active MCCs') }}</h6>
                <h4 class="mb-0">{{ count($mccs) }}</h4></div>
        </div></div>
    </div></div>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET">
            <div class="row g-2">
                <div class="col-md-2">
                    <select name="mcc" class="form-select form-select-sm">
                        <option value="">{{ __('All MCCs') }}</option>
                        @foreach($mccs as $mcc)
                            <option value="{{ $mcc }}" {{ request('mcc') == $mcc ? 'selected' : '' }}>{{ $mcc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="grade" class="form-select form-select-sm">
                        <option value="">{{ __('All Grades') }}</option>
                        @foreach($grades as $key => $label)
                            <option value="{{ $key }}" {{ request('grade') == $key ? 'selected' : '' }}>{{ $key }} - {{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="{{ __('From Date') }}"></div>
                <div class="col-md-2"><input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="{{ __('To Date') }}"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-sm btn-primary w-100">{{ __('Filter') }}</button></div>
                <div class="col-md-2"><a href="{{ route('milk-collections.index') }}" class="btn btn-sm btn-secondary w-100">{{ __('Reset') }}</a></div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header card-body table-border-style">
        <div class="table-responsive">
            <table class="table datatable">
                <thead>
                    <tr>
                        <th>{{ __('Collection ID') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('MCC') }}</th>
                        <th>{{ __('Farmer') }}</th>
                        <th>{{ __('Quantity (L)') }}</th>
                        <th>{{ __('Grade') }}</th>
                        <th>{{ __('Recorded By') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($collections as $c)
                    <tr>
                        <td><span class="badge bg-light text-dark">{{ $c->collection_id }}</span></td>
                        <td>{{ $c->date->format('d M Y') }}</td>
                        <td>{{ $c->mcc }}</td>
                        <td>{{ $c->farmer?->name ?? '—' }}</td>
                        <td>{{ number_format($c->quantity_litres, 2) }}</td>
                        <td><span class="badge {{ $c->grade_badge_class }} p-2 px-3 rounded">{{ $c->quality_grade }} — {{ $c->grade_label }}</span></td>
                        <td>{{ $c->recorder?->name ?? '—' }}</td>
                        <td>
                            @can('manage milk collection')
                            <a href="{{ route('milk-collections.show', $c->id) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="{{ __('View') }}"><i class="ti ti-eye text-white"></i></a>
                            @endcan
                            @can('edit milk collection')
                            <a href="#" data-url="{{ route('milk-collections.edit', $c->id) }}" data-ajax-popup="true" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Edit') }}"><i class="ti ti-pencil text-white"></i></a>
                            @endcan
                            @can('delete milk collection')
                            <form action="{{ route('milk-collections.destroy', $c->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this record?') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="{{ __('Delete') }}"><i class="ti ti-trash text-white"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $collections->links() }}</div>
    </div>
</div>
@endsection
