@extends('layouts.admin')

@section('page-title')
    {{ __('Extension Services Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}">{{ __('Reports') }}</a></li>
    <li class="breadcrumb-item">{{ __('Extension Services') }}</li>
@endsection

@section('content')

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold"><i class="ti ti-filter me-2 text-primary"></i>{{ __('Filters') }}</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.extension') }}">
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
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.82rem">{{ __('Center') }}</label>
                    <select name="center" class="form-control form-control-sm">
                        <option value="">{{ __('All') }}</option>
                        @foreach($centers as $c)
                            <option value="{{ $c }}" {{ $center === $c ? 'selected' : '' }}>{{ $c }}</option>
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
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4">{{ number_format($summary->total_visits ?? 0) }}</div>
            <div class="text-muted" style="font-size:.78rem">Total Visits</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4">{{ number_format($farmersReached) }}</div>
            <div class="text-muted" style="font-size:.78rem">Unique Farmers Reached</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4">{{ number_format($eventsHeld) }}</div>
            <div class="text-muted" style="font-size:.78rem">Events Held</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4 {{ $agentsBelow->isNotEmpty() ? 'text-danger' : 'text-success' }}">
                {{ $agentsBelow->count() }}
            </div>
            <div class="text-muted" style="font-size:.78rem">Agents Below Target</div>
        </div>
    </div>
</div>

{{-- Agents Below Target Warning --}}
@if($agentsBelow->isNotEmpty())
    <div class="card border-0 shadow-sm border-start border-danger border-3 mb-4">
        <div class="card-header bg-white border-bottom">
            <h6 class="mb-0 fw-semibold text-danger">
                <i class="ti ti-alert-triangle me-2"></i>
                Agents Below 2-Visit Target
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Agent</th>
                            <th>MCC</th>
                            <th class="text-end">Visits (Period)</th>
                            <th class="text-end">Days Active</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agentsBelow as $a)
                            <tr>
                                <td style="font-size:.85rem">{{ $a->name ?? 'Unknown' }}</td>
                                <td style="font-size:.85rem">{{ collect($a->assigned_centers ?? [])->implode(', ') ?: '—' }}</td>
                                <td class="text-end" style="font-size:.85rem">{{ $a->visits_this_week ?? 0 }}</td>
                                <td class="text-end" style="font-size:.85rem">
                                    {{ $a->visits()->whereBetween('visit_date', [now()->startOfWeek(), now()->endOfWeek()])->distinct()->count('visit_date') }}
                                </td>
                                <td>
                                    <span class="badge bg-danger" style="font-size:.72rem">Below Target</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

{{-- All Agents Stats Table --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold">
            <i class="ti ti-users me-2 text-success"></i>
            Agent Performance Summary
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Agent</th>
                        <th>MCC</th>
                        <th class="text-end">Visits</th>
                        <th class="text-end">Days Active</th>
                        <th>Performance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agentStats as $a)
                        <tr>
                            <td style="font-size:.85rem">{{ $a->agent_name ?? 'Unknown' }}</td>
                            <td style="font-size:.85rem">{{ $a->mcc_name ?? '—' }}</td>
                            <td class="text-end fw-semibold" style="font-size:.85rem">{{ $a->visit_count }}</td>
                            <td class="text-end" style="font-size:.85rem">{{ $a->days_active }}</td>
                            <td>
                                @if($a->visit_count >= 2)
                                    <span class="badge bg-success" style="font-size:.72rem">On Target</span>
                                @else
                                    <span class="badge bg-danger" style="font-size:.72rem">Below Target</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-3 text-muted">No visit records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Individual Visits Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold">
            <i class="ti ti-map me-2 text-info"></i>
            Field Visits
            <span class="badge bg-secondary ms-1">{{ $visits->total() }}</span>
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Agent</th>
                        <th>MCC</th>
                        <th>Topic</th>
                        <th>Type</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visits as $visit)
                        <tr>
                            <td style="font-size:.82rem">{{ \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') }}</td>
                            <td style="font-size:.85rem">{{ $visit->agent?->name ?? '—' }}</td>
                            <td style="font-size:.82rem; color:#6c757d">{{ $visit->center ?? '—' }}</td>
                            <td style="font-size:.82rem">
                                {{ optional($visit->topics->first())->topic ?? '—' }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border" style="font-size:.72rem">
                                    {{ ucfirst($visit->visit_type ?? 'visit') }}
                                </span>
                            </td>
                            <td style="font-size:.78rem; color:#6c757d; max-width:200px">
                                {{ \Illuminate\Support\Str::limit($visit->notes ?? '', 60) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No visits found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($visits->hasPages())
        <div class="card-footer bg-white">{{ $visits->links() }}</div>
    @endif
</div>

{{-- Below-Target Agents --}}
@if($belowTargetAgents->isNotEmpty())
<div class="alert alert-warning shadow-sm mb-4">
    <i class="ti ti-alert-triangle me-1"></i>
    <strong>{{ __('Agents Below Target This Week') }}:</strong>
    {{ $belowTargetAgents->pluck('name')->implode(', ') }} — {{ __('fewer than 2 visits this week.') }}
</div>
@endif

{{-- Visits per Agent KPI --}}
@if($visitsPerAgent->isNotEmpty())
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold"><i class="ti ti-users me-2 text-primary"></i>{{ __('Visits per Agent') }}</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Agent') }}</th>
                    <th>{{ __('This Week') }}</th>
                    <th>{{ __('This Month') }}</th>
                    <th>{{ __('All Time') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($visitsPerAgent as $row)
                <tr>
                    <td>{{ $row['agent']->name }}</td>
                    <td>{{ $row['this_week'] }}</td>
                    <td>{{ $row['this_month'] }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>
                        @if($row['this_week'] < 2)
                            <span class="badge bg-danger">{{ __('Below Target') }}</span>
                        @else
                            <span class="badge bg-success">{{ __('On Track') }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Topic Coverage --}}
@if($topicCoverage->isNotEmpty())
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold"><i class="ti ti-book me-2 text-primary"></i>{{ __('Topic Coverage') }}</h6>
    </div>
    <div class="card-body">
        <div class="row g-2">
            @foreach($topicCoverage as $tc)
            <div class="col-md-3">
                <div class="p-3 bg-light rounded text-center">
                    <div class="fw-bold fs-5">{{ $tc->count }}</div>
                    <div class="text-muted small">{{ $tc->topic }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- OSS Sales per Agent --}}
@if($ossSalesPerAgent->isNotEmpty())
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold"><i class="ti ti-shopping-cart me-2 text-primary"></i>{{ __('OSS Sales per Agent') }}</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Agent') }}</th>
                    <th>{{ __('Sales Count') }}</th>
                    <th>{{ __('Total Value (₦)') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ossSalesPerAgent as $row)
                <tr>
                    <td>{{ $row->agent?->name ?? '—' }}</td>
                    <td>{{ $row->sales_count }}</td>
                    <td>₦{{ number_format($row->total_value, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Outstanding Credit per Agent --}}
@if($outstandingCredit->isNotEmpty())
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold"><i class="ti ti-credit-card me-2 text-warning"></i>{{ __('Outstanding Credit Balances per Agent') }}</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Agent') }}</th>
                    <th>{{ __('Outstanding Credit (₦)') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($outstandingCredit as $row)
                <tr>
                    <td>{{ $row->agent?->name ?? '—' }}</td>
                    <td class="text-danger fw-semibold">₦{{ number_format($row->credit_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
