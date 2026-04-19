@extends('layouts.admin')
@section('page-title'){{ __('Reconciliation') }}: {{ $rec->reconciliation_id }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accounting.reconciliation.index') }}">{{ __('Reconciliation') }}</a></li>
    <li class="breadcrumb-item">{{ $rec->reconciliation_id }}</li>
@endsection
@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm text-center"><div class="card-body"><p class="text-muted mb-1 small">{{ __('Bank Account') }}</p><strong>{{ $rec->bankAccount->bank_name ?? '—' }}</strong><br><small>{{ $rec->bankAccount->account_number ?? '' }}</small></div></div></div>
    <div class="col-md-2"><div class="card border-0 shadow-sm text-center"><div class="card-body"><p class="text-muted mb-1 small">{{ __('Statement Date') }}</p><h6>{{ $rec->statement_date->format('d M Y') }}</h6></div></div></div>
    <div class="col-md-2"><div class="card border-0 shadow-sm text-center"><div class="card-body"><p class="text-muted mb-1 small">{{ __('Opening') }}</p><h6>{{ \Auth::user()->priceFormat($rec->opening_balance) }}</h6></div></div></div>
    <div class="col-md-2"><div class="card border-0 shadow-sm text-center"><div class="card-body"><p class="text-muted mb-1 small">{{ __('Closing') }}</p><h6>{{ \Auth::user()->priceFormat($rec->closing_balance) }}</h6></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm text-center"><div class="card-body"><p class="text-muted mb-1 small">{{ __('Status') }}</p><span class="badge {{ $rec->status==='reconciled'?'bg-success':'bg-warning text-dark' }} fs-6">{{ ucfirst($rec->status) }}</span></div></div></div>
</div>

<div class="row g-3">
    {{-- Statement Items --}}
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="mb-0">{{ __('Statement Items') }}</h6>
                @if($rec->isOpen())
                <div class="d-flex gap-2">
                    <span class="badge bg-success">{{ $rec->items->where('is_matched',true)->count() }} {{ __('matched') }}</span>
                    <span class="badge bg-danger">{{ $rec->items->where('is_matched',false)->count() }} {{ __('unmatched') }}</span>
                </div>
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th class="text-end">{{ __('Amount') }}</th>
                        <th>{{ __('Status') }}</th>
                        @if($rec->isOpen())<th></th>@endif
                    </tr></thead>
                    <tbody>
                    @foreach($rec->items as $item)
                    <tr class="{{ $item->is_matched?'table-success':'' }}">
                        <td><small>{{ $item->date->format('d M Y') }}</small></td>
                        <td>{{ $item->description }}<br><small class="text-muted">{{ $item->reference }}</small></td>
                        <td><span class="badge {{ $item->type==='credit'?'bg-success':'bg-danger' }}">{{ ucfirst($item->type) }}</span></td>
                        <td class="text-end">{{ \Auth::user()->priceFormat($item->amount) }}</td>
                        <td>
                            @if($item->is_matched)
                            <span class="badge bg-success">{{ __('Matched') }}</span>
                            @else
                            <span class="badge bg-secondary">{{ __('Unmatched') }}</span>
                            @endif
                        </td>
                        @if($rec->isOpen())
                        <td>
                            @if($item->is_matched)
                            <form method="POST" action="{{ route('accounting.reconciliation.unmatch', [$rec->id, $item->id]) }}" class="d-inline">
                                @csrf <button class="btn btn-xs btn-outline-warning">{{ __('Unmatch') }}</button>
                            </form>
                            @else
                            <button class="btn btn-xs btn-outline-success" data-bs-toggle="modal" data-bs-target="#matchModal" data-item="{{ $item->id }}">{{ __('Match') }}</button>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- GL Lines for matching --}}
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="mb-0">{{ __('GL Transactions (this period)') }}</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:400px;overflow-y:auto">
                    <table class="table table-sm mb-0">
                        <thead class="table-light sticky-top"><tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th class="text-end">{{ __('Dr') }}</th>
                            <th class="text-end">{{ __('Cr') }}</th>
                        </tr></thead>
                        <tbody>
                        @forelse($lines as $tl)
                        <tr>
                            <td><small>{{ $tl->date }}</small></td>
                            <td><small>{{ $tl->reference }}</small></td>
                            <td class="text-end"><small>{{ $tl->debit > 0 ? \Auth::user()->priceFormat($tl->debit) : '' }}</small></td>
                            <td class="text-end"><small>{{ $tl->credit > 0 ? \Auth::user()->priceFormat($tl->credit) : '' }}</small></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted small">{{ __('No GL lines found for this account.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($rec->isOpen())
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body text-center">
                <form method="POST" action="{{ route('accounting.reconciliation.finalize', $rec->id) }}">
                    @csrf
                    <button class="btn btn-success w-100">
                        <i class="ti ti-check me-2"></i>{{ __('Finalise Reconciliation') }}
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Match Modal --}}
@if($rec->isOpen())
<div class="modal fade" id="matchModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="match-form">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">{{ __('Match to GL Transaction') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="form-label">{{ __('Select GL Transaction') }}</label>
                    <select name="transaction_line_id" class="form-select" required>
                        <option value="">— {{ __('Select') }} —</option>
                        @foreach($lines as $tl)
                        <option value="{{ $tl->id }}">{{ $tl->date }} | {{ $tl->reference }} | Dr: {{ $tl->debit }} Cr: {{ $tl->credit }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('Match') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('matchModal').addEventListener('show.bs.modal', function(e) {
    const itemId = e.relatedTarget.dataset.item;
    document.getElementById('match-form').action = `/accounting/reconciliation/{{ $rec->id }}/items/${itemId}/match`;
});
</script>
@endif
@endsection
