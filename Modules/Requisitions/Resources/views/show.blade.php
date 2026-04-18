@extends('layouts.admin')
@section('page-title'){{ __('Requisition Detail') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('requisitions.index') }}">{{ __('Requisitions') }}</a></li>
    <li class="breadcrumb-item">{{ $req->requisition_ref }}</li>
@endsection
@section('content')
<div class="row">
<div class="col-xl-8">
<div class="card"><div class="card-header d-flex align-items-center justify-content-between">
    <h5 class="mb-0">{{ $req->requisition_ref }} — {{ $req->title }}</h5>
    <span class="badge {{ $req->status_badge_class }} p-2 px-3">{{ ucfirst(str_replace('_',' ',$req->status)) }}</span>
</div><div class="card-body">
    <div class="row g-3 mb-3">
        <div class="col-md-6"><strong>{{ __('Requested By') }}:</strong> {{ $req->requester?->name }}</div>
        <div class="col-md-6"><strong>{{ __('Date') }}:</strong> {{ $req->request_date->format('d M Y') }}</div>
        <div class="col-md-6"><strong>{{ __('Centre') }}:</strong> {{ $req->center ?? '—' }}</div>
        <div class="col-md-6"><strong>{{ __('Priority') }}:</strong> <span class="badge {{ $req->priority_badge_class }} p-1 px-2">{{ $req->priority }}</span></div>
        @if($req->description)<div class="col-12"><strong>{{ __('Description') }}:</strong> {{ $req->description }}</div>@endif
    </div>

    <h6>{{ __('Items') }}</h6>
    <div class="table-responsive mb-3">
    <table class="table table-bordered table-sm">
        <thead class="table-light"><tr>
            <th>{{ __('Item') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Unit') }}</th>
            <th>{{ __('Unit Cost (₦)') }}</th><th>{{ __('Subtotal (₦)') }}</th>
        </tr></thead>
        <tbody>
            @foreach($req->items as $item)
            <tr>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->unit ?? '—' }}</td>
                <td>₦{{ number_format($item->estimated_cost, 2) }}</td>
                <td>₦{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot><tr>
            <td colspan="4" class="text-end"><strong>{{ __('Total') }}:</strong></td>
            <td><strong>₦{{ number_format($req->total_estimated_cost, 2) }}</strong></td>
        </tr></tfoot>
    </table>
    </div>
    <p><small class="text-muted">{{ __('Approval Tier') }}: <strong>{{ ucfirst(str_replace('_',' ',$req->amount_tier)) }}</strong></small></p>

    <h6>{{ __('Approval Trail') }}</h6>
    @if($req->approvals->count())
    <div class="table-responsive mb-3">
    <table class="table table-sm table-bordered">
        <thead class="table-light"><tr><th>{{ __('Level') }}</th><th>{{ __('Actor') }}</th><th>{{ __('Action') }}</th><th>{{ __('Comments') }}</th><th>{{ __('Date') }}</th></tr></thead>
        <tbody>
            @foreach($req->approvals as $a)
            <tr>
                <td>{{ $a->level }}</td>
                <td>{{ $a->actor?->name ?? '—' }}</td>
                <td><span class="badge {{ $a->action==='approved'?'bg-success':'bg-danger' }} p-1 px-2">{{ ucfirst($a->action) }}</span></td>
                <td>{{ $a->comments ?? '—' }}</td>
                <td>{{ optional($a->acted_at)->format('d M Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @else<p class="text-muted">{{ __('No approvals yet.') }}</p>@endif

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('requisitions.index') }}" class="btn btn-secondary">{{ __('Back') }}</a>
        @if(in_array($req->status, ['pending','supervisor_approved','manager_approved']))
        @can('approve requisition')
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">{{ __('Approve') }}</button>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">{{ __('Reject') }}</button>
        @endcan
        @endif
        @if($req->status === 'approved')
        @can('pay requisition')
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#payModal">{{ __('Mark Paid') }}</button>
        @endcan
        @endif
        @if($req->status === 'paid')
        <form action="{{ route('requisitions.complete', $req->id) }}" method="POST" class="d-inline">@csrf
            <button type="submit" class="btn btn-outline-success">{{ __('Confirm Receipt') }}</button>
        </form>
        @endif
    </div>
</div></div>
</div></div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">{{ __('Approve Requisition') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="{{ route('requisitions.approve', $req->id) }}" method="POST">@csrf
        <div class="modal-body"><textarea name="comments" class="form-control" rows="3" placeholder="{{ __('Comments (optional)') }}"></textarea></div>
        <div class="modal-footer"><button type="submit" class="btn btn-success">{{ __('Approve') }}</button></div>
        </form>
    </div></div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">{{ __('Reject Requisition') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="{{ route('requisitions.reject', $req->id) }}" method="POST">@csrf
        <div class="modal-body"><textarea name="rejection_reason" class="form-control" rows="3" placeholder="{{ __('Reason...') }}" required></textarea></div>
        <div class="modal-footer"><button type="submit" class="btn btn-danger">{{ __('Reject') }}</button></div>
        </form>
    </div></div>
</div>

<!-- Pay Modal -->
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">{{ __('Mark as Paid') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="{{ route('requisitions.paid', $req->id) }}" method="POST">@csrf
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">{{ __('Approved Amount (₦)') }}</label>
                <input type="number" name="approved_amount" class="form-control" step="0.01" value="{{ $req->total_estimated_cost }}"></div>
            <div class="mb-3"><label class="form-label">{{ __('Payment Reference') }}</label>
                <input type="text" name="payment_reference" class="form-control"></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">{{ __('Mark Paid') }}</button></div>
        </form>
    </div></div>
</div>
@endsection
