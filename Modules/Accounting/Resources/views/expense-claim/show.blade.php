@extends('layouts.admin')
@section('page-title'){{ $claim->claim_id }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accounting.expense-claims.index') }}">{{ __('Expense Claims') }}</a></li>
    <li class="breadcrumb-item">{{ $claim->claim_id }}</li>
@endsection
@section('content')
@php $statusInfo = \Modules\Accounting\Models\ExpenseClaim::STATUSES[$claim->status]; @endphp

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm text-center"><div class="card-body"><p class="text-muted mb-1 small">{{ __('Employee') }}</p><strong>{{ $claim->employee->name ?? '—' }}</strong></div></div></div>
    <div class="col-md-2"><div class="card border-0 shadow-sm text-center"><div class="card-body"><p class="text-muted mb-1 small">{{ __('Date') }}</p><strong>{{ $claim->claim_date->format('d M Y') }}</strong></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm text-center"><div class="card-body"><p class="text-muted mb-1 small">{{ __('Total Amount') }}</p><h5>{{ \Auth::user()->priceFormat($claim->total_amount) }}</h5></div></div></div>
    <div class="col-md-2"><div class="card border-0 shadow-sm text-center"><div class="card-body"><p class="text-muted mb-1 small">{{ __('Status') }}</p><span class="badge {{ $statusInfo['class'] }} fs-6">{{ $statusInfo['label'] }}</span></div></div></div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body d-flex flex-column justify-content-center gap-2">
            @if($claim->status === 'draft')
                <form method="POST" action="{{ route('accounting.expense-claims.submit', $claim->id) }}">@csrf
                    <button class="btn btn-sm btn-warning w-100">{{ __('Submit') }}</button>
                </form>
            @endif
            @can('approve expense claim')
            @if($claim->status === 'submitted')
                <form method="POST" action="{{ route('accounting.expense-claims.approve', $claim->id) }}">@csrf
                    <button class="btn btn-sm btn-success w-100">{{ __('Approve') }}</button>
                </form>
                <button class="btn btn-sm btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">{{ __('Reject') }}</button>
            @endif
            @endcan
            @can('pay expense claim')
            @if($claim->status === 'approved')
                <form method="POST" action="{{ route('accounting.expense-claims.pay', $claim->id) }}">@csrf
                    <button class="btn btn-sm btn-primary w-100">{{ __('Mark Paid & Post GL') }}</button>
                </form>
            @endif
            @endcan
            </div>
        </div>
    </div>
</div>

@if($claim->rejection_reason)
<div class="alert alert-danger"><strong>{{ __('Rejection Reason') }}:</strong> {{ $claim->rejection_reason }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent"><h6 class="mb-0">{{ $claim->title }}</h6>@if($claim->description)<p class="text-muted mb-0 small mt-1">{{ $claim->description }}</p>@endif</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Account') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th>{{ __('Receipt') }}</th>
                </tr></thead>
                <tbody>
                @foreach($claim->items as $item)
                <tr>
                    <td>{{ $item->date->format('d M Y') }}</td>
                    <td>{{ $item->description }}</td>
                    <td><small>{{ $item->chartAccount ? $item->chartAccount->code.' – '.$item->chartAccount->name : '—' }}</small></td>
                    <td class="text-end fw-semibold">{{ \Auth::user()->priceFormat($item->amount) }}</td>
                    <td>
                        @if($item->receipt_path)
                        <a href="{{ asset('storage/'.$item->receipt_path) }}" target="_blank" class="btn btn-xs btn-outline-secondary"><i class="ti ti-paperclip"></i></a>
                        @else —
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr><th colspan="3" class="text-end">{{ __('Total') }}</th><th class="text-end">{{ \Auth::user()->priceFormat($claim->total_amount) }}</th><th></th></tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@if($claim->approver)
<div class="card border-0 shadow-sm mt-3">
    <div class="card-body">
        <small class="text-muted">{{ __('Approved by') }} <strong>{{ $claim->approver->name }}</strong> {{ __('on') }} {{ $claim->approved_at->format('d M Y H:i') }}</small>
        @if($claim->paid_at)
        · <small class="text-muted">{{ __('Paid on') }} {{ $claim->paid_at->format('d M Y H:i') }}</small>
        @endif
    </div>
</div>
@endif

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('accounting.expense-claims.reject', $claim->id) }}">@csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">{{ __('Reject Claim') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="form-label">{{ __('Reason') }} <span class="text-danger">*</span></label>
                    <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('Reject') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
