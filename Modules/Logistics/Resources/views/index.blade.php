@extends('layouts.admin')
@section('page-title'){{ __('Logistics Trips') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Logistics') }}</li>
@endsection
@section('action-btn')
    @can('create logistics trip')
    <a href="#" data-url="{{ route('logistics.create') }}" data-ajax-popup="true" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Add Trip') }}"><i class="ti ti-plus"></i></a>
    @endcan
    <a href="{{ route('riders.index') }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="{{ __('Manage Riders') }}"><i class="ti ti-user"></i></a>
    @can('manage logistics')
    <a href="{{ route('logistics.export') }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="{{ __('Export') }}"><i class="ti ti-file-export"></i></a>
    @endcan
@endsection
@section('content')
<div class="row">
    <div class="col-xl-3 col-sm-6"><div class="card"><div class="card-body"><div class="d-flex align-items-center">
        <div class="theme-avtar bg-primary"><i class="ti ti-truck text-white fs-5"></i></div>
        <div class="ms-3"><h6 class="mb-0">{{ __('Trips This Month') }}</h6><h4 class="mb-0">{{ $monthTrips }}</h4></div>
    </div></div></div></div>
    <div class="col-xl-3 col-sm-6"><div class="card"><div class="card-body"><div class="d-flex align-items-center">
        <div class="theme-avtar bg-success"><i class="ti ti-droplet text-white fs-5"></i></div>
        <div class="ms-3"><h6 class="mb-0">{{ __('Litres This Month') }}</h6><h4 class="mb-0">{{ number_format($monthLitres, 0) }} L</h4></div>
    </div></div></div></div>
    <div class="col-xl-3 col-sm-6"><div class="card"><div class="card-body"><div class="d-flex align-items-center">
        <div class="theme-avtar bg-info"><i class="ti ti-coin text-white fs-5"></i></div>
        <div class="ms-3"><h6 class="mb-0">{{ __('Avg Cost/Litre') }}</h6><h4 class="mb-0">₦{{ number_format($avgCostLitre, 2) }}</h4></div>
    </div></div></div></div>
    <div class="col-xl-3 col-sm-6"><div class="card"><div class="card-body"><div class="d-flex align-items-center">
        <div class="theme-avtar bg-warning"><i class="ti ti-users text-white fs-5"></i></div>
        <div class="ms-3"><h6 class="mb-0">{{ __('Active Riders') }}</h6><h4 class="mb-0">{{ $riders->count() }}</h4></div>
    </div></div></div></div>
</div>
<div class="card mb-3"><div class="card-body">
    <form method="GET"><div class="row g-2">
        <div class="col-md-2"><select name="mcc" class="form-select form-select-sm"><option value="">{{ __('All MCCs') }}</option>@foreach($mccs as $mcc)<option value="{{ $mcc }}" {{ request('mcc')==$mcc?'selected':'' }}>{{ $mcc }}</option>@endforeach</select></div>
        <div class="col-md-2"><select name="status" class="form-select form-select-sm"><option value="">{{ __('All Statuses') }}</option>@foreach($statuses as $s)<option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select></div>
        <div class="col-md-2"><select name="rider_id" class="form-select form-select-sm"><option value="">{{ __('All Riders') }}</option>@foreach($riders as $r)<option value="{{ $r->id }}" {{ request('rider_id')==$r->id?'selected':'' }}>{{ $r->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}"></div>
        <div class="col-md-2"><input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}"></div>
        <div class="col-md-1"><button type="submit" class="btn btn-sm btn-primary w-100">{{ __('Filter') }}</button></div>
        <div class="col-md-1"><a href="{{ route('logistics.index') }}" class="btn btn-sm btn-secondary w-100">{{ __('Reset') }}</a></div>
    </div></form>
</div></div>
<div class="card"><div class="card-header card-body table-border-style"><div class="table-responsive">
    <table class="table datatable">
        <thead><tr>
            <th>{{ __('Trip ID') }}</th><th>{{ __('Date') }}</th><th>{{ __('Route') }}</th>
            <th>{{ __('Rider') }}</th><th>{{ __('Litres') }}</th><th>{{ __('Total Cost') }}</th>
            <th>{{ __('Cost/L') }}</th><th>{{ __('Status') }}</th><th>{{ __('Action') }}</th>
        </tr></thead>
        <tbody>
            @foreach($trips as $t)
            <tr>
                <td><span class="badge bg-light text-dark">{{ $t->trip_id }}</span></td>
                <td>{{ $t->trip_date->format('d M Y') }}</td>
                <td>{{ $t->mcc_source }} → {{ $t->destination }}</td>
                <td>{{ $t->rider?->name ?? '—' }}</td>
                <td>{{ number_format($t->litres_transported, 2) }}</td>
                <td>₦{{ number_format($t->total_cost, 2) }}</td>
                <td>{{ $t->cost_per_litre ? '₦'.number_format($t->cost_per_litre, 2) : '—' }}</td>
                <td><span class="badge {{ $t->status_badge_class }} p-2 px-3">{{ $t->status }}</span></td>
                <td>
                    <a href="{{ route('logistics.show', $t->id) }}" class="btn btn-sm btn-info"><i class="ti ti-eye text-white"></i></a>
                    @can('edit logistics trip')
                    <a href="#" data-url="{{ route('logistics.edit', $t->id) }}" data-ajax-popup="true" class="btn btn-sm btn-primary"><i class="ti ti-pencil text-white"></i></a>
                    @if($t->status !== 'Completed')
                    <form action="{{ route('logistics.complete', $t->id) }}" method="POST" class="d-inline">@csrf
                        <button type="submit" class="btn btn-sm btn-success" title="{{ __('Mark Complete') }}"><i class="ti ti-check text-white"></i></button>
                    </form>
                    @endif
                    @endcan
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div><div class="mt-3">{{ $trips->links() }}</div></div></div>
@endsection
