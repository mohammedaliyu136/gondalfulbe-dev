@extends('layouts.admin')

@section('page-title')
    {{ __('Cooperative Leader Dashboard') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Cooperative Leader Dashboard') }}</li>
@endsection

@section('content')

@if(!$cooperative)
<div class="alert alert-warning">{{ __('No cooperative is associated with your account.') }}</div>
@else

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold text-primary">{{ $memberCount }}</div>
                <div class="text-muted small">{{ __('Total Members') }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold text-success">{{ $activeCount }}</div>
                <div class="text-muted small">{{ __('Active Members') }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold text-info">{{ number_format($weekLitres, 1) }} L</div>
                <div class="text-muted small">{{ __('Milk This Week') }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold text-warning">{{ number_format($monthLitres, 1) }} L</div>
                <div class="text-muted small">{{ __('Milk This Month') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Member Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold">
            <i class="ti ti-users me-2 text-primary"></i>{{ $cooperative->name }} — {{ __('Members') }}
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Farmer ID') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th>{{ __('Gender') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                <tr>
                    <td><code>{{ $member->vender_id }}</code></td>
                    <td>{{ $member->name }}</td>
                    <td>{{ $member->contact }}</td>
                    <td>{{ $member->gender ?: '—' }}</td>
                    <td>
                        @if($member->is_active)
                            <span class="badge bg-success">{{ __('Active') }}</span>
                        @else
                            <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">{{ __('No members found.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endif
@endsection
