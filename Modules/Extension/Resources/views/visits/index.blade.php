@extends('layouts.admin')
@section('page-title', __('Field Visits'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Field Visits') }}</li>
@endsection

@section('content')
@if($belowTarget->isNotEmpty())
<div class="alert alert-warning">
    <i class="ti ti-alert-triangle me-1"></i>
    <strong>{{ __('Below Target:') }}</strong>
    {{ $belowTarget->pluck('name')->implode(', ') }} — {{ __('fewer than 2 visits this week.') }}
</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('Field Visits') }}</h5>
        @can('manage extension agents')
        <a href="{{ route('field-visits.create') }}" class="btn btn-primary btn-sm">
            <i class="ti ti-plus"></i> {{ __('Log Visit') }}
        </a>
        @endcan
    </div>
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">{{ __('Agent') }}</label>
                <select name="agent_id" class="form-select form-select-sm">
                    <option value="">{{ __('All Agents') }}</option>
                    @foreach($agents as $a)
                    <option value="{{ $a->id }}" @selected(request('agent_id') == $a->id)>{{ $a->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('Center') }}</label>
                <select name="center" class="form-select form-select-sm">
                    <option value="">{{ __('All') }}</option>
                    @foreach($mccs as $mcc)
                    <option value="{{ $mcc }}" @selected(request('center') === $mcc)>{{ $mcc }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('From') }}</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('To') }}</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-secondary btn-sm w-100">{{ __('Filter') }}</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Visit ID') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Agent') }}</th>
                        <th>{{ __('Center') }}</th>
                        <th>{{ __('Community') }}</th>
                        <th>{{ __('Farmers') }}</th>
                        <th>{{ __('Follow-up') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visits as $visit)
                    <tr>
                        <td><code>{{ $visit->visit_id }}</code></td>
                        <td>{{ \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') }}</td>
                        <td>{{ $visit->agent->name ?? '—' }}</td>
                        <td>{{ $visit->center ?? '—' }}</td>
                        <td>{{ $visit->community ?? '—' }}</td>
                        <td>{{ $visit->farmers_count ?? '—' }}</td>
                        <td>
                            @if($visit->follow_up_required)
                            <span class="badge bg-warning text-dark">{{ __('Required') }}</span>
                            @else
                            <span class="badge bg-light text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('field-visits.show', $visit->id) }}" class="btn btn-xs btn-outline-info">
                                <i class="ti ti-eye"></i>
                            </a>
                            <a href="{{ route('field-visits.edit', $visit->id) }}" class="btn btn-xs btn-outline-primary">
                                <i class="ti ti-pencil"></i>
                            </a>
                            <form action="{{ route('field-visits.destroy', $visit->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('{{ __('Delete visit?') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger"><i class="ti ti-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">{{ __('No visits found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($visits->hasPages())
    <div class="card-footer">{{ $visits->links() }}</div>
    @endif
</div>
@endsection
