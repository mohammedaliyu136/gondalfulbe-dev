@extends('layouts.admin')
@section('page-title'){{ __('Budgets') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accounting.dashboard') }}">{{ __('Finance') }}</a></li>
    <li class="breadcrumb-item">{{ __('Budgets') }}</li>
@endsection
@section('action-btn')
    @can('create budget')
    <a href="{{ route('accounting.budget.create') }}" class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>{{ __('New Budget') }}</a>
    @endcan
@endsection
@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Fiscal Year') }}</th>
                    <th>{{ __('Period') }}</th>
                    <th class="text-end">{{ __('Total Budgeted') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-center">{{ __('Actions') }}</th>
                </tr></thead>
                <tbody>
                @forelse($budgets as $b)
                <tr>
                    <td><a href="{{ route('accounting.budget.show', $b->id) }}">{{ $b->budget_id }}</a></td>
                    <td>{{ $b->name }}</td>
                    <td>{{ $b->fiscal_year }}</td>
                    <td><small>{{ $b->start_date->format('d M Y') }} – {{ $b->end_date->format('d M Y') }}</small></td>
                    <td class="text-end">{{ \Auth::user()->priceFormat($b->totalBudgeted()) }}</td>
                    <td><span class="badge {{ $b->statusBadge() }}">{{ ucfirst($b->status) }}</span></td>
                    <td class="text-center">
                        @if($b->status === 'draft')
                        <form method="POST" action="{{ route('accounting.budget.activate', $b->id) }}" class="d-inline">
                            @csrf <button class="btn btn-xs btn-success" title="{{ __('Activate') }}"><i class="ti ti-player-play"></i></button>
                        </form>
                        @endif
                        @can('edit budget')
                        <a href="{{ route('accounting.budget.edit', $b->id) }}" class="btn btn-xs btn-info"><i class="ti ti-pencil"></i></a>
                        @endcan
                        @can('delete budget')
                        <form method="POST" action="{{ route('accounting.budget.destroy', $b->id) }}" class="d-inline" onsubmit="return confirm('{{ __('Delete this budget?') }}')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger"><i class="ti ti-trash"></i></button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">{{ __('No budgets yet.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
