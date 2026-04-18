@extends('layouts.admin')

@section('page-title')
    {{ __('Requisitions Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}">{{ __('Reports') }}</a></li>
    <li class="breadcrumb-item">{{ __('Requisitions') }}</li>
@endsection

@section('content')

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold"><i class="ti ti-filter me-2 text-primary"></i>{{ __('Filters') }}</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.requisitions') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.82rem">{{ __('Date From') }}</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.82rem">{{ __('Date To') }}</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-size:.82rem">{{ __('Center (MCC)') }}</label>
                    <select name="center" class="form-control form-control-sm">
                        <option value="">{{ __('All Centers') }}</option>
                        @foreach($centers as $c)
                            <option value="{{ $c }}" {{ $center === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.82rem">{{ __('Status') }}</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">{{ __('All') }}</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="ti ti-search"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
@if($summary)
<div class="row g-3 mb-4">
    <div class="col-md col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4">{{ $summary->total ?? 0 }}</div>
            <div class="text-muted" style="font-size:.78rem">Total</div>
        </div>
    </div>
    <div class="col-md col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4 text-warning">{{ $summary->pending_count ?? 0 }}</div>
            <div class="text-muted" style="font-size:.78rem">Pending</div>
        </div>
    </div>
    <div class="col-md col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4 text-info">{{ $summary->approved_count ?? 0 }}</div>
            <div class="text-muted" style="font-size:.78rem">Approved</div>
        </div>
    </div>
    <div class="col-md col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4 text-success">{{ $summary->paid_count ?? 0 }}</div>
            <div class="text-muted" style="font-size:.78rem">Paid</div>
        </div>
    </div>
    <div class="col-md col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4 text-danger">{{ $summary->rejected_count ?? 0 }}</div>
            <div class="text-muted" style="font-size:.78rem">Rejected</div>
        </div>
    </div>
    <div class="col-md col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4">
                {{ number_format($summary->avg_approval_hours ?? 0, 1) }}<small class="text-muted fs-6 ms-1">hrs</small>
            </div>
            <div class="text-muted" style="font-size:.78rem">Avg Approval Time</div>
        </div>
    </div>
</div>
@endif

{{-- Requisitions Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold">
            <i class="ti ti-file-invoice me-2 text-primary"></i>
            Requisitions
            <span class="badge bg-secondary ms-1">{{ $requisitions->total() }}</span>
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Req. No</th>
                        <th>Center</th>
                        <th>Category</th>
                        <th>Requester</th>
                        <th>Description</th>
                        <th class="text-end">Amount (₦)</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requisitions as $req)
                        <tr>
                            <td style="font-size:.82rem">{{ $req->requisition_no ?? '#' . $req->id }}</td>
                            <td style="font-size:.82rem">{{ $req->mcc_name ?? '—' }}</td>
                            <td style="font-size:.82rem">{{ $req->category ?? '—' }}</td>
                            <td style="font-size:.82rem">{{ $req->requester_name ?? '—' }}</td>
                            <td style="font-size:.78rem; color:#6c757d; max-width:180px">
                                {{ \Illuminate\Support\Str::limit($req->description ?? '', 50) }}
                            </td>
                            <td class="text-end fw-semibold" style="font-size:.85rem">
                                {{ number_format($req->total_amount ?? 0, 2) }}
                            </td>
                            <td>
                                @php $st = $req->status ?? 'pending'; @endphp
                                <span class="badge
                                    @if($st === 'paid') bg-success
                                    @elseif($st === 'approved') bg-info
                                    @elseif($st === 'rejected') bg-danger
                                    @else bg-warning text-dark
                                    @endif" style="font-size:.72rem">
                                    {{ ucfirst($st) }}
                                </span>
                            </td>
                            <td style="font-size:.78rem; color:#6c757d">
                                {{ \Carbon\Carbon::parse($req->created_at)->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No requisitions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($requisitions->hasPages())
        <div class="card-footer bg-white">{{ $requisitions->links() }}</div>
    @endif
</div>

@endsection
