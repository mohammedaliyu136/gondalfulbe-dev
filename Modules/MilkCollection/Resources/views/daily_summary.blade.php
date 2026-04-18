@extends('layouts.admin')

@section('page-title')
    {{ __('Daily Milk Collection Summary') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('milk-collections.index') }}">{{ __('Milk Collections') }}</a></li>
    <li class="breadcrumb-item">{{ __('Daily Summary') }}</li>
@endsection

@section('content')

{{-- Date header --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <h5 class="mb-0 fw-semibold">
        <i class="ti ti-calendar-stats me-2 text-primary"></i>
        {{ __('Today') }}: {{ now()->format('l, d F Y') }}
    </h5>
    <a href="{{ route('milk-collections.create') }}" class="btn btn-primary btn-sm">
        <i class="ti ti-plus me-1"></i>{{ __('Record Collection') }}
    </a>
</div>

{{-- Totals --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="fs-1 fw-bold text-primary">{{ number_format($totalLitres, 1) }} L</div>
                <div class="text-muted">{{ __('Total Litres Today') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="fs-1 fw-bold text-success">{{ $totalFarmers }}</div>
                <div class="text-muted">{{ __('Farmers Supplied Today') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Per-MCC breakdown --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold"><i class="ti ti-building-store me-2 text-primary"></i>{{ __('Per Collection Centre') }}</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Collection Centre') }}</th>
                    <th class="text-end">{{ __('Litres') }}</th>
                    <th class="text-end">{{ __('Farmers') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary as $mcc => $data)
                <tr>
                    <td class="fw-semibold">{{ $mcc }}</td>
                    <td class="text-end">{{ number_format($data['litres'], 1) }} L</td>
                    <td class="text-end">{{ $data['farmers'] }}</td>
                    <td>
                        @if($data['litres'] > 0)
                            <span class="badge bg-success">{{ __('Active') }}</span>
                        @else
                            <span class="badge bg-secondary">{{ __('No Supply') }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Recent collections today --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold"><i class="ti ti-list me-2 text-primary"></i>{{ __("Today's Records") }}</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Collection ID') }}</th>
                    <th>{{ __('Farmer') }}</th>
                    <th>{{ __('MCC') }}</th>
                    <th class="text-end">{{ __('Litres') }}</th>
                    <th>{{ __('Grade') }}</th>
                    <th>{{ __('Time') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentCollections as $c)
                <tr>
                    <td><code>{{ $c->collection_id }}</code></td>
                    <td>{{ $c->farmer?->name ?? '—' }}</td>
                    <td>{{ $c->mcc }}</td>
                    <td class="text-end">{{ number_format($c->quantity_litres, 1) }}</td>
                    <td>
                        @php $grade = strtoupper($c->grade ?? ''); @endphp
                        <span class="badge bg-{{ $grade === 'A' ? 'success' : ($grade === 'B' ? 'warning' : 'secondary') }}">
                            {{ $grade ?: '—' }}
                        </span>
                    </td>
                    <td>{{ $c->created_at->format('H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">{{ __('No collections recorded today yet.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
