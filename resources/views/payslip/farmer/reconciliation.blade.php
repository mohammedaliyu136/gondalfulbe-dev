@extends('layouts.admin')

@section('page-title')
    {{ __('Payment Reconciliation') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Farmer Payments') }}</li>
    <li class="breadcrumb-item">{{ __('Reconciliation') }}</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold"><i class="ti ti-refresh me-2 text-primary"></i>{{ __('Payment Batch Reconciliation') }}</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Batch ID') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Expected Total (₦)') }}</th>
                    <th>{{ __('Actual Paid (₦)') }}</th>
                    <th>{{ __('Failed') }}</th>
                    <th>{{ __('Pending') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $row)
                <tr>
                    <td><code>{{ $row['batch']->batch_id }}</code></td>
                    <td>{{ $row['batch']->created_at->format('d M Y') }}</td>
                    <td>₦{{ number_format($row['expected_total'], 2) }}</td>
                    <td>₦{{ number_format($row['actual_paid'], 2) }}</td>
                    <td>
                        @if($row['failed_count'] > 0)
                            <span class="badge bg-danger">{{ $row['failed_count'] }}</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </td>
                    <td>
                        @if($row['pending_count'] > 0)
                            <span class="badge bg-warning text-dark">{{ $row['pending_count'] }}</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </td>
                    <td>
                        @if($row['matched'])
                            <span class="badge bg-success">{{ __('Matched') }}</span>
                        @else
                            <span class="badge bg-danger">{{ __('Unmatched') }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">{{ __('No payment batches found.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
