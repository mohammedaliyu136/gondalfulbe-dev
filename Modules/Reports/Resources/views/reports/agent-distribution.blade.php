@extends('layouts.admin')

@section('page-title')
    {{ __('Agent Distribution Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}">{{ __('Reports') }}</a></li>
    <li class="breadcrumb-item">{{ __('Agent Distribution') }}</li>
@endsection

@section('content')

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold"><i class="ti ti-filter me-2 text-primary"></i>{{ __('Filters') }}</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.agent') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.82rem">{{ __('Date From') }}</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.82rem">{{ __('Date To') }}</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="font-size:.82rem">{{ __('Agent') }}</label>
                    <select name="agent_id" class="form-control form-control-sm">
                        <option value="">{{ __('All Agents') }}</option>
                        @foreach($agents as $a)
                            <option value="{{ $a->id }}" {{ (string)$agentId === (string)$a->id ? 'selected' : '' }}>
                                {{ $a->name }}
                            </option>
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

{{-- Distribution Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold">
            <i class="ti ti-box me-2 text-success"></i>
            Agent Allocation vs. Sold vs. Returned
            <span class="badge bg-secondary ms-1">{{ method_exists($agentSummary, 'total') ? $agentSummary->total() : $agentSummary->count() }}</span>
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Agent</th>
                        <th>Product</th>
                        <th class="text-end">Allocated</th>
                        <th class="text-end">Sold</th>
                        <th class="text-end">Returned</th>
                        <th class="text-end">Balance</th>
                        <th>Balance Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agentSummary as $row)
                        @php
                            $balancePct = $row->allocated > 0
                                ? round($row->balance / $row->allocated * 100)
                                : 0;
                        @endphp
                        <tr>
                            <td style="font-size:.85rem">{{ $row->agent_name ?? 'Unknown' }}</td>
                            <td style="font-size:.85rem">{{ $row->product_name ?? '—' }}</td>
                            <td class="text-end fw-semibold" style="font-size:.85rem">
                                {{ number_format($row->allocated ?? 0) }}
                            </td>
                            <td class="text-end text-success fw-semibold" style="font-size:.85rem">
                                {{ number_format($row->sold ?? 0) }}
                            </td>
                            <td class="text-end text-warning" style="font-size:.85rem">
                                {{ number_format($row->returned ?? 0) }}
                            </td>
                            <td class="text-end" style="font-size:.85rem">
                                <span class="{{ $row->balance > 0 ? 'text-primary fw-semibold' : 'text-muted' }}">
                                    {{ number_format($row->balance ?? 0) }}
                                </span>
                            </td>
                            <td>
                                @if(($row->balance ?? 0) <= 0)
                                    <span class="badge bg-success" style="font-size:.72rem">Cleared</span>
                                @elseif($balancePct > 50)
                                    <span class="badge bg-danger" style="font-size:.72rem">
                                        {{ $balancePct }}% Remaining
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark" style="font-size:.72rem">
                                        {{ $balancePct }}% Remaining
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="ti ti-inbox" style="font-size:2rem"></i>
                                <p class="mt-1 mb-0">No distribution records found for the selected period.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($agentSummary->isNotEmpty())
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="2">Totals</td>
                            <td class="text-end">{{ number_format($agentSummary->sum('allocated')) }}</td>
                            <td class="text-end text-success">{{ number_format($agentSummary->sum('sold')) }}</td>
                            <td class="text-end text-warning">{{ number_format($agentSummary->sum('returned')) }}</td>
                            <td class="text-end">{{ number_format($agentSummary->sum('balance')) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
    @if(method_exists($agentSummary, 'hasPages') && $agentSummary->hasPages())
        <div class="card-footer bg-white">{{ $agentSummary->links() }}</div>
    @endif
</div>

@endsection
