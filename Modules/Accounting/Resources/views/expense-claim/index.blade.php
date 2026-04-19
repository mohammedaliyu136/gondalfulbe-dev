@extends('layouts.admin')
@section('page-title'){{ __('Expense Claims') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accounting.dashboard') }}">{{ __('Finance') }}</a></li>
    <li class="breadcrumb-item">{{ __('Expense Claims') }}</li>
@endsection
@section('action-btn')
    @can('create expense claim')
    <a href="{{ route('accounting.expense-claims.create') }}" class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>{{ __('New Claim') }}</a>
    @endcan
@endsection
@section('content')
<div class="row g-3 mb-3">
    @foreach(['draft'=>['bg-secondary','Draft'],'submitted'=>['bg-warning text-dark','Pending'],'approved'=>['bg-info','Approved'],'paid'=>['bg-success','Paid']] as $s=>[$cls,$lbl])
    <div class="col-xl-3 col-sm-6"><div class="card border-0 shadow-sm text-center"><div class="card-body">
        <span class="badge {{ $cls }} p-2 px-3 mb-2">{{ __($lbl) }}</span>
        <h3 class="mb-0">{{ $summary[$s] ?? 0 }}</h3>
    </div></div></div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr>
                    <th>{{ __('Claim ID') }}</th>
                    <th>{{ __('Employee') }}</th>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-center">{{ __('Actions') }}</th>
                </tr></thead>
                <tbody>
                @forelse($claims as $c)
                @php $statusInfo = \Modules\Accounting\Models\ExpenseClaim::STATUSES[$c->status]; @endphp
                <tr>
                    <td><a href="{{ route('accounting.expense-claims.show', $c->id) }}">{{ $c->claim_id }}</a></td>
                    <td>{{ $c->employee->name ?? '—' }}</td>
                    <td>{{ $c->title }}</td>
                    <td>{{ $c->claim_date->format('d M Y') }}</td>
                    <td class="text-end fw-semibold">{{ \Auth::user()->priceFormat($c->total_amount) }}</td>
                    <td><span class="badge {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span></td>
                    <td class="text-center">
                        <a href="{{ route('accounting.expense-claims.show', $c->id) }}" class="btn btn-xs btn-info"><i class="ti ti-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">{{ __('No expense claims yet.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($claims->hasPages())
    <div class="card-footer">{{ $claims->links() }}</div>
    @endif
</div>
@endsection
