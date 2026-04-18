@extends('layouts.admin')
@section('page-title'){{ __('Center Operations') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Center Operations') }}</li>
@endsection
@section('action-btn')
    @can('create center cost')
    <a href="#" data-url="{{ route('center-costs.create') }}" data-ajax-popup="true" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Add Cost Entry') }}"><i class="ti ti-plus"></i></a>
    @endcan
    @can('manage center operations')
    <a href="{{ route('center-costs.export') }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="{{ __('Export') }}"><i class="ti ti-file-export"></i></a>
    @endcan
@endsection
@section('content')
<div class="row">
    @foreach(['draft'=>['bg-secondary','Draft'],'submitted'=>['bg-warning text-dark','Submitted'],'approved'=>['bg-info','Approved'],'paid'=>['bg-success','Paid']] as $s=>[$cls,$lbl])
    <div class="col-xl-3 col-sm-6"><div class="card"><div class="card-body text-center">
        <span class="badge {{ $cls }} p-2 px-3 mb-2">{{ __($lbl) }}</span>
        <h3 class="mb-0">{{ $countByStatus[$s] ?? 0 }}</h3>
    </div></div></div>
    @endforeach
</div>
<div class="card mb-3"><div class="card-body">
    <form method="GET"><div class="row g-2">
        <div class="col-md-2"><select name="mcc" class="form-select form-select-sm"><option value="">{{ __('All MCCs') }}</option>@foreach($mccs as $m)<option value="{{ $m }}" {{ request('mcc')==$m?'selected':'' }}>{{ $m }}</option>@endforeach</select></div>
        <div class="col-md-2"><select name="status" class="form-select form-select-sm"><option value="">{{ __('All Statuses') }}</option>@foreach($statuses as $s)<option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>@endforeach</select></div>
        <div class="col-md-2"><select name="category" class="form-select form-select-sm"><option value="">{{ __('All Categories') }}</option>@foreach($categories as $c)<option value="{{ $c }}" {{ request('category')==$c?'selected':'' }}>{{ $c }}</option>@endforeach</select></div>
        <div class="col-md-2"><input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}"></div>
        <div class="col-md-2"><button type="submit" class="btn btn-sm btn-primary w-100">{{ __('Filter') }}</button></div>
        <div class="col-md-2"><a href="{{ route('center-costs.index') }}" class="btn btn-sm btn-secondary w-100">{{ __('Reset') }}</a></div>
    </div></form>
</div></div>
<div class="card"><div class="card-header card-body table-border-style"><div class="table-responsive">
    <table class="table datatable">
        <thead><tr>
            <th>{{ __('Entry ID') }}</th><th>{{ __('MCC') }}</th><th>{{ __('Category') }}</th>
            <th>{{ __('Amount (₦)') }}</th><th>{{ __('Status') }}</th><th>{{ __('Submitted By') }}</th><th>{{ __('Action') }}</th>
        </tr></thead>
        <tbody>
            @foreach($costs as $c)
            <tr>
                <td><span class="badge bg-light text-dark">{{ $c->cost_entry_id }}</span></td>
                <td>{{ $c->mcc }}</td>
                <td>{{ $c->category }}</td>
                <td>₦{{ number_format($c->amount, 2) }}</td>
                <td><span class="badge {{ $c->status_badge_class }} p-2 px-3">{{ ucfirst($c->status) }}</span></td>
                <td>{{ $c->submitter?->name ?? '—' }}</td>
                <td>
                    <a href="{{ route('center-costs.show', $c->id) }}" class="btn btn-sm btn-info"><i class="ti ti-eye text-white"></i></a>
                    @if($c->isEditable())
                    @can('edit center cost')
                    <a href="#" data-url="{{ route('center-costs.edit', $c->id) }}" data-ajax-popup="true" class="btn btn-sm btn-primary"><i class="ti ti-pencil text-white"></i></a>
                    @endcan
                    @endif
                    @if($c->status === 'draft')
                    <form action="{{ route('center-costs.submit', $c->id) }}" method="POST" class="d-inline">@csrf
                        <button type="submit" class="btn btn-sm btn-warning" title="{{ __('Submit') }}"><i class="ti ti-send text-white"></i></button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div><div class="mt-3">{{ $costs->links() }}</div></div></div>
@endsection
