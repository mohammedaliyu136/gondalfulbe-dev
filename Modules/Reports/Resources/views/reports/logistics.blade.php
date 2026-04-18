@extends('layouts.admin')

@section('page-title')
    {{ __('Logistics Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}">{{ __('Reports') }}</a></li>
    <li class="breadcrumb-item">{{ __('Logistics') }}</li>
@endsection

@section('content')

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold"><i class="ti ti-filter me-2 text-primary"></i>{{ __('Filters') }}</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.logistics') }}">
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
                    <label class="form-label" style="font-size:.82rem">{{ __('Rider') }}</label>
                    <select name="rider" class="form-control form-control-sm">
                        <option value="">{{ __('All Riders') }}</option>
                        @foreach($riders as $r)
                            <option value="{{ $r->id }}" {{ (string)$rider === (string)$r->id ? 'selected' : '' }}>
                                {{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.82rem">{{ __('MCC') }}</label>
                    <select name="mcc" class="form-control form-control-sm">
                        <option value="">{{ __('All') }}</option>
                        @foreach($mccList as $m)
                            <option value="{{ $m }}" {{ $mcc === $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.82rem">{{ __('Status') }}</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">{{ __('All') }}</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4">{{ number_format($summary->total_trips ?? 0) }}</div>
            <div class="text-muted" style="font-size:.78rem">Total Trips</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4">{{ number_format($summary->total_litres ?? 0, 0) }}L</div>
            <div class="text-muted" style="font-size:.78rem">Litres Transported</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4">₦{{ number_format($summary->total_cost ?? 0, 0) }}</div>
            <div class="text-muted" style="font-size:.78rem">Total Cost</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4">₦{{ number_format($summary->avg_cost_per_litre ?? 0, 2) }}</div>
            <div class="text-muted" style="font-size:.78rem">Avg Cost / Litre</div>
        </div>
    </div>
</div>
@endif

<div class="row g-4">
    {{-- Trips Table --}}
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">
                    <i class="ti ti-truck me-2 text-primary"></i>
                    Trip Records
                    <span class="badge bg-secondary ms-1">{{ $trips->total() }}</span>
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Route</th>
                                <th>Rider</th>
                                <th class="text-end">Litres</th>
                                <th class="text-end">Cost (₦)</th>
                                <th class="text-end">₦/L</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trips as $trip)
                                <tr>
                                    <td style="font-size:.8rem; color:#9ca3af">{{ $trip->id }}</td>
                                    <td style="font-size:.85rem">{{ \Carbon\Carbon::parse($trip->trip_date)->format('d M Y') }}</td>
                                    <td style="font-size:.82rem">{{ $trip->route ?? '—' }}</td>
                                    <td style="font-size:.82rem">{{ $trip->rider_name ?? '—' }}</td>
                                    <td class="text-end" style="font-size:.85rem">{{ number_format($trip->litres_collected ?? 0, 1) }}</td>
                                    <td class="text-end" style="font-size:.85rem">{{ number_format($trip->total_cost ?? 0, 2) }}</td>
                                    <td class="text-end" style="font-size:.85rem">{{ number_format($trip->cost_per_litre ?? 0, 2) }}</td>
                                    <td>
                                        @php $st = $trip->status ?? 'pending'; @endphp
                                        <span class="badge
                                            @if($st === 'completed') bg-success
                                            @elseif($st === 'cancelled') bg-danger
                                            @else bg-secondary
                                            @endif" style="font-size:.72rem">
                                            {{ ucfirst($st) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        No trips found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($trips->hasPages())
                <div class="card-footer bg-white">{{ $trips->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Rider Ranking --}}
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">
                    <i class="ti ti-award me-2 text-warning"></i>
                    Rider Performance Ranking
                </h6>
            </div>
            <div class="card-body p-0">
                @if($riderRanking->isEmpty())
                    <div class="text-center py-4 text-muted small">No data.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Rider</th>
                                    <th class="text-end">Trips</th>
                                    <th class="text-end">Litres</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($riderRanking as $i => $r)
                                    <tr>
                                        <td>
                                            @if($i === 0)
                                                <i class="ti ti-medal text-warning"></i>
                                            @else
                                                <span class="text-muted" style="font-size:.8rem">{{ $i + 1 }}</span>
                                            @endif
                                        </td>
                                        <td style="font-size:.85rem">{{ $r->rider_name ?? 'Unknown' }}</td>
                                        <td class="text-end" style="font-size:.85rem">{{ $r->trips }}</td>
                                        <td class="text-end fw-semibold" style="font-size:.85rem">
                                            {{ number_format($r->litres ?? 0, 0) }}L
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
