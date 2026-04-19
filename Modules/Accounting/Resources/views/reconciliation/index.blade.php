@extends('layouts.admin')
@section('page-title'){{ __('Bank Reconciliation') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accounting.dashboard') }}">{{ __('Finance') }}</a></li>
    <li class="breadcrumb-item">{{ __('Reconciliation') }}</li>
@endsection
@section('action-btn')
    @can('create reconciliation')
    <a href="{{ route('accounting.reconciliation.create') }}" class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>{{ __('New Reconciliation') }}</a>
    @endcan
@endsection
@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Bank Account') }}</th>
                    <th>{{ __('Statement Date') }}</th>
                    <th class="text-end">{{ __('Opening') }}</th>
                    <th class="text-end">{{ __('Closing') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Reconciled') }}</th>
                    <th class="text-center">{{ __('Actions') }}</th>
                </tr></thead>
                <tbody>
                @forelse($reconciliations as $rec)
                <tr>
                    <td><a href="{{ route('accounting.reconciliation.show', $rec->id) }}">{{ $rec->reconciliation_id }}</a></td>
                    <td>{{ $rec->bankAccount->bank_name ?? '—' }} – {{ $rec->bankAccount->account_number ?? '' }}</td>
                    <td>{{ $rec->statement_date->format('d M Y') }}</td>
                    <td class="text-end">{{ \Auth::user()->priceFormat($rec->opening_balance) }}</td>
                    <td class="text-end">{{ \Auth::user()->priceFormat($rec->closing_balance) }}</td>
                    <td><span class="badge {{ $rec->status==='reconciled'?'bg-success':'bg-warning text-dark' }}">{{ ucfirst($rec->status) }}</span></td>
                    <td><small>{{ $rec->reconciled_at ? $rec->reconciled_at->format('d M Y') : '—' }}</small></td>
                    <td class="text-center">
                        <a href="{{ route('accounting.reconciliation.show', $rec->id) }}" class="btn btn-xs btn-info"><i class="ti ti-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">{{ __('No reconciliations yet.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($reconciliations->hasPages())
    <div class="card-footer">{{ $reconciliations->links() }}</div>
    @endif
</div>
@endsection
