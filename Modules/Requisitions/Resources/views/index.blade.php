@extends('layouts.admin')
@section('page-title'){{ __('Requisitions') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Requisitions') }}</li>
@endsection
@section('action-btn')
    @can('create requisition')
    <a href="{{ route('requisitions.create') }}" class="btn btn-sm btn-primary"><i class="ti ti-plus"></i> {{ __('New Requisition') }}</a>
    @endcan
    @can('manage requisitions')
    <a href="{{ route('requisitions.export') }}" class="btn btn-sm btn-success"><i class="ti ti-file-export"></i></a>
    @endcan
@endsection
@section('content')
<ul class="nav nav-tabs mb-3" id="reqTabs">
    <li class="nav-item"><a class="nav-link active" href="#myReqs" data-bs-toggle="tab">{{ __('My Requisitions') }} <span class="badge bg-secondary">{{ $myRequisitions->total() }}</span></a></li>
    <li class="nav-item"><a class="nav-link" href="#pending" data-bs-toggle="tab">{{ __('Pending Approvals') }} <span class="badge bg-warning text-dark">{{ $pendingApprovals->total() }}</span></a></li>
</ul>

<div class="tab-content">
<div class="tab-pane fade show active" id="myReqs">
<div class="card"><div class="card-header card-body table-border-style"><div class="table-responsive">
    <table class="table datatable">
        <thead><tr>
            <th>{{ __('Ref') }}</th><th>{{ __('Title') }}</th><th>{{ __('Centre') }}</th>
            <th>{{ __('Total Cost') }}</th><th>{{ __('Priority') }}</th><th>{{ __('Status') }}</th><th>{{ __('Date') }}</th><th>{{ __('Action') }}</th>
        </tr></thead>
        <tbody>
            @foreach($myRequisitions as $r)
            <tr>
                <td><span class="badge bg-light text-dark">{{ $r->requisition_ref }}</span></td>
                <td>{{ $r->title }}</td>
                <td>{{ $r->center ?? '—' }}</td>
                <td>₦{{ number_format($r->total_estimated_cost, 2) }}</td>
                <td><span class="badge {{ $r->priority_badge_class }} p-2 px-2">{{ $r->priority }}</span></td>
                <td><span class="badge {{ $r->status_badge_class }} p-2 px-2">{{ ucfirst(str_replace('_',' ',$r->status)) }}</span></td>
                <td>{{ $r->request_date->format('d M Y') }}</td>
                <td>
                    <a href="{{ route('requisitions.show', $r->id) }}" class="btn btn-sm btn-info"><i class="ti ti-eye text-white"></i></a>
                    @if($r->status === 'pending')
                    @can('edit requisition')
                    <a href="{{ route('requisitions.edit', $r->id) }}" class="btn btn-sm btn-primary"><i class="ti ti-pencil text-white"></i></a>
                    @endcan
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div><div class="mt-3">{{ $myRequisitions->links() }}</div></div></div>
</div>

<div class="tab-pane fade" id="pending">
<div class="card"><div class="card-header card-body table-border-style"><div class="table-responsive">
    <table class="table datatable">
        <thead><tr>
            <th>{{ __('Ref') }}</th><th>{{ __('Title') }}</th><th>{{ __('Requested By') }}</th>
            <th>{{ __('Total Cost') }}</th><th>{{ __('Tier') }}</th><th>{{ __('Priority') }}</th><th>{{ __('Status') }}</th><th>{{ __('Action') }}</th>
        </tr></thead>
        <tbody>
            @foreach($pendingApprovals as $r)
            <tr>
                <td><span class="badge bg-light text-dark">{{ $r->requisition_ref }}</span></td>
                <td>{{ $r->title }}</td>
                <td>{{ $r->requester?->name ?? '—' }}</td>
                <td>₦{{ number_format($r->total_estimated_cost, 2) }}</td>
                <td><small class="text-muted">{{ ucfirst(str_replace('_',' ',$r->amount_tier)) }}</small></td>
                <td><span class="badge {{ $r->priority_badge_class }} p-1 px-2">{{ $r->priority }}</span></td>
                <td><span class="badge {{ $r->status_badge_class }} p-1 px-2">{{ ucfirst(str_replace('_',' ',$r->status)) }}</span></td>
                <td><a href="{{ route('requisitions.show', $r->id) }}" class="btn btn-sm btn-info"><i class="ti ti-eye text-white"></i></a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div><div class="mt-3">{{ $pendingApprovals->links() }}</div></div></div>
</div>
</div>
@endsection
