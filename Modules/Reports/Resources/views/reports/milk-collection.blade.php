@extends('layouts.admin')

@section('page-title')
    {{ __('Milk Collection Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}">{{ __('Reports') }}</a></li>
    <li class="breadcrumb-item">{{ __('Milk Collection') }}</li>
@endsection

@section('content')

{{-- Filter Form --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold"><i class="ti ti-filter me-2 text-primary"></i>{{ __('Filters') }}</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.milk') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.82rem">{{ __('Date From') }}</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.82rem">{{ __('Date To') }}</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.82rem">{{ __('MCC') }}</label>
                    <select name="mcc" class="form-control form-control-sm">
                        <option value="">{{ __('All Centers') }}</option>
                        @foreach($mccList as $m)
                            <option value="{{ $m }}" {{ $mcc === $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.82rem">{{ __('Grade') }}</label>
                    <select name="grade" class="form-control form-control-sm">
                        <option value="">{{ __('All Grades') }}</option>
                        <option value="A" {{ $grade === 'A' ? 'selected' : '' }}>Grade A</option>
                        <option value="B" {{ $grade === 'B' ? 'selected' : '' }}>Grade B</option>
                        <option value="C" {{ $grade === 'C' ? 'selected' : '' }}>Grade C</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-size:.82rem">{{ __('Farmer Name / ID') }}</label>
                    <input type="text" name="farmer" class="form-control form-control-sm"
                           placeholder="{{ __('Search farmer...') }}" value="{{ $farmer }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="ti ti-search"></i>
                    </button>
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
            <div class="fw-bold fs-4">{{ number_format($summary->total_litres ?? 0, 1) }}L</div>
            <div class="text-muted" style="font-size:.78rem">Total Litres</div>
        </div>
    </div>
    <div class="col-md col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4 text-success">
                {{ ($summary->total_litres ?? 0) > 0 ? round($summary->grade_a / $summary->total_litres * 100, 1) : 0 }}%
            </div>
            <div class="text-muted" style="font-size:.78rem">Grade A</div>
        </div>
    </div>
    <div class="col-md col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4 text-warning">
                {{ ($summary->total_litres ?? 0) > 0 ? round($summary->grade_b / $summary->total_litres * 100, 1) : 0 }}%
            </div>
            <div class="text-muted" style="font-size:.78rem">Grade B</div>
        </div>
    </div>
    <div class="col-md col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4 text-danger">
                {{ ($summary->total_litres ?? 0) > 0 ? round($summary->grade_c / $summary->total_litres * 100, 1) : 0 }}%
            </div>
            <div class="text-muted" style="font-size:.78rem">Grade C</div>
        </div>
    </div>
    <div class="col-md col-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4">{{ number_format($summary->total_farmers ?? 0) }}</div>
            <div class="text-muted" style="font-size:.78rem">Total Farmers</div>
        </div>
    </div>
</div>
@endif

{{-- Records Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold">
            <i class="ti ti-table me-2 text-warning"></i>
            Collection Records
            <span class="badge bg-secondary ms-1">{{ method_exists($records, 'total') ? $records->total() : $records->count() }}</span>
        </h6>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.milk', array_merge(request()->query(), ['export' => 'pdf'])) }}"
               class="btn btn-sm btn-outline-danger">
                <i class="ti ti-file-type-pdf me-1"></i> PDF
            </a>
            <a href="{{ route('reports.milk', array_merge(request()->query(), ['export' => 'excel'])) }}"
               class="btn btn-sm btn-outline-success">
                <i class="ti ti-file-type-xls me-1"></i> Excel
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('MCC') }}</th>
                        <th>{{ __('Farmer') }}</th>
                        <th>{{ __('Farmer ID') }}</th>
                        <th class="text-end">{{ __('Qty (L)') }}</th>
                        <th>{{ __('Grade') }}</th>
                        <th>{{ __('Recorded By') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $rec)
                        <tr>
                            <td style="font-size:.8rem; color:#9ca3af">{{ $rec->id }}</td>
                            <td style="font-size:.85rem">{{ \Carbon\Carbon::parse($rec->collection_date)->format('d M Y') }}</td>
                            <td style="font-size:.85rem">{{ $rec->mcc_name ?? '—' }}</td>
                            <td style="font-size:.85rem">{{ $rec->farmer_name }}</td>
                            <td style="font-size:.82rem; color:#6c757d">{{ $rec->farmer_code ?? '—' }}</td>
                            <td class="text-end fw-semibold" style="font-size:.85rem">
                                {{ number_format($rec->quantity_litres, 2) }}
                            </td>
                            <td>
                                <span class="badge
                                    @if($rec->grade === 'A') bg-success
                                    @elseif($rec->grade === 'B') bg-warning text-dark
                                    @else bg-danger
                                    @endif">
                                    {{ $rec->grade ?? '—' }}
                                </span>
                            </td>
                            <td style="font-size:.82rem; color:#6c757d">{{ $rec->recorded_by ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="ti ti-inbox" style="font-size:2rem"></i>
                                <p class="mt-1 mb-0">No records found for the selected filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(method_exists($records, 'hasPages') && $records->hasPages())
        <div class="card-footer bg-white">
            {{ $records->links() }}
        </div>
    @endif
</div>

@endsection
