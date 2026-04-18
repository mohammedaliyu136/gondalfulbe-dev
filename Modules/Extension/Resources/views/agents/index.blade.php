@extends('layouts.admin')
@section('page-title', __('Extension Agents'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Extension Agents') }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('Extension Agents') }}</h5>
        @can('manage extension agents')
        <a href="{{ route('extension-agents.create') }}" class="btn btn-primary btn-sm">
            <i class="ti ti-plus"></i> {{ __('Add Agent') }}
        </a>
        @endcan
    </div>
    @if($belowTarget->isNotEmpty())
    <div class="card-body border-bottom">
        <div class="alert alert-warning mb-0">
            <i class="ti ti-alert-triangle me-1"></i>
            <strong>{{ $belowTarget->count() }}</strong> {{ __('agent(s) below visit target (< 2 visits/week):') }}
            {{ $belowTarget->pluck('name')->implode(', ') }}
        </div>
    </div>
    @endif
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Centers') }}</th>
                        <th>{{ __('Visits This Week') }}</th>
                        <th>{{ __('Join Date') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agents as $agent)
                    <tr>
                        <td><code>{{ $agent->agent_code }}</code></td>
                        <td>{{ $agent->name }}</td>
                        <td>{{ $agent->phone ?? '—' }}</td>
                        <td>
                            @if($agent->assigned_centers)
                                @foreach((array)$agent->assigned_centers as $c)
                                <span class="badge bg-light text-dark">{{ $c }}</span>
                                @endforeach
                            @else —
                            @endif
                        </td>
                        <td>
                            <span class="{{ $agent->isBelowTarget() ? 'text-danger fw-bold' : 'text-success' }}">
                                {{ $agent->visitsThisWeek() }}
                            </span>
                            @if($agent->isBelowTarget())
                            <span class="badge bg-danger ms-1">{{ __('Below Target') }}</span>
                            @endif
                        </td>
                        <td>{{ $agent->join_date ? \Carbon\Carbon::parse($agent->join_date)->format('d M Y') : '—' }}</td>
                        <td>
                            @if($agent->status === 'active')
                            <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                            <span class="badge bg-secondary">{{ ucfirst($agent->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('extension-agents.edit', $agent->id) }}" class="btn btn-xs btn-outline-primary">
                                <i class="ti ti-pencil"></i>
                            </a>
                            <form action="{{ route('extension-agents.destroy', $agent->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('{{ __('Delete agent?') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger"><i class="ti ti-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">{{ __('No agents found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
