@extends('layouts.admin')
@section('page-title'){{ __('Cost Entry Detail') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('center-costs.index') }}">{{ __('Center Operations') }}</a></li>
    <li class="breadcrumb-item">{{ $cost->cost_entry_id }}</li>
@endsection
@section('content')
<div class="row"><div class="col-xl-8">
<div class="card"><div class="card-header d-flex align-items-center justify-content-between">
    <h5 class="mb-0">{{ $cost->cost_entry_id }}</h5>
    <span class="badge {{ $cost->status_badge_class }} p-2 px-3">{{ ucfirst($cost->status) }}</span>
</div><div class="card-body">
    <div class="row g-3">
        <div class="col-md-6"><strong>{{ __('MCC') }}:</strong> {{ $cost->mcc }}</div>
        <div class="col-md-6"><strong>{{ __('Category') }}:</strong> {{ $cost->category }}</div>
        <div class="col-md-6"><strong>{{ __('Amount') }}:</strong> ₦{{ number_format($cost->amount, 2) }}</div>
        <div class="col-md-6"><strong>{{ __('Period') }}:</strong> {{ optional($cost->period_start)->format('d M Y') }} — {{ optional($cost->period_end)->format('d M Y') }}</div>
        @if($cost->description)<div class="col-12"><strong>{{ __('Description') }}:</strong> {{ $cost->description }}</div>@endif
    </div>
    <hr>
    <h6>{{ __('Approval Workflow') }}</h6>
    <div class="timeline">
        <div class="d-flex align-items-center mb-2"><span class="badge {{ $cost->status==='draft'?'bg-secondary':'bg-success' }} me-2">1</span><strong>{{ __('Draft') }}</strong><span class="ms-2 text-muted small">{{ $cost->created_at->format('d M Y H:i') }}</span></div>
        <div class="d-flex align-items-center mb-2"><span class="badge {{ in_array($cost->status,['submitted','approved','paid'])?'bg-success':'bg-secondary' }} me-2">2</span><strong>{{ __('Submitted') }}</strong>@if($cost->submitted_at)<span class="ms-2 text-muted small">{{ $cost->submitter?->name }} — {{ $cost->submitted_at->format('d M Y') }}</span>@endif</div>
        <div class="d-flex align-items-center mb-2"><span class="badge {{ in_array($cost->status,['approved','paid'])?'bg-success':'bg-secondary' }} me-2">3</span><strong>{{ __('Approved') }}</strong>@if($cost->approved_at)<span class="ms-2 text-muted small">{{ $cost->approver?->name }} — {{ $cost->approved_at->format('d M Y') }}</span>@endif</div>
        <div class="d-flex align-items-center mb-2"><span class="badge {{ $cost->status==='paid'?'bg-success':'bg-secondary' }} me-2">4</span><strong>{{ __('Paid') }}</strong>@if($cost->paid_at)<span class="ms-2 text-muted small">{{ $cost->paidBy?->name }} — {{ $cost->paid_at->format('d M Y') }}</span>@endif</div>
        @if($cost->status==='rejected')<div class="alert alert-danger mt-2">{{ __('Rejected') }}: {{ $cost->rejection_reason }}</div>@endif
    </div>
    <div class="mt-4 d-flex gap-2 flex-wrap">
        <a href="{{ route('center-costs.index') }}" class="btn btn-secondary">{{ __('Back') }}</a>
        @if($cost->status==='draft')
        <form action="{{ route('center-costs.submit', $cost->id) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-warning">{{ __('Submit for Approval') }}</button></form>
        @endif
        @if($cost->status==='submitted')
        @can('approve center cost')
        <form action="{{ route('center-costs.approve', $cost->id) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-success">{{ __('Approve') }}</button></form>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">{{ __('Reject') }}</button>
        @endcan
        @endif
        @if($cost->status==='approved')
        @can('pay center cost')
        <form action="{{ route('center-costs.paid', $cost->id) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-primary">{{ __('Mark as Paid') }}</button></form>
        @endcan
        @endif
    </div>
</div></div></div></div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">{{ __('Reject Entry') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="{{ route('center-costs.reject', $cost->id) }}" method="POST">@csrf
        <div class="modal-body"><textarea name="rejection_reason" class="form-control" rows="3" placeholder="{{ __('Reason for rejection...') }}" required></textarea></div>
        <div class="modal-footer"><button type="submit" class="btn btn-danger">{{ __('Reject') }}</button></div>
        </form>
    </div></div>
</div>
@endsection
