@extends('layouts.admin')

@section('page-title')
    {{ $cooperative->name }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cooperatives.index') }}">{{ __('Cooperatives') }}</a></li>
    <li class="breadcrumb-item">{{ $cooperative->name }}</li>
@endsection

@section('action-btn')
    @can('manage cooperative')
        <a href="{{ route('cooperatives.farmers.export', $cooperative->id) }}"
           class="btn btn-sm btn-success"
           data-bs-toggle="tooltip" title="{{ __('Export Farmers') }}">
            <i class="ti ti-file-export"></i> {{ __('Export Farmers') }}
        </a>
    @endcan
@endsection

@section('content')
    {{-- Cooperative info card --}}
    <div class="row mb-4">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 text-center">
                            <span class="avatar avatar-xl bg-primary text-white rounded-circle fs-2">
                                {{ strtoupper(substr($cooperative->name, 0, 2)) }}
                            </span>
                        </div>
                        <div class="col-md-10">
                            <h4 class="mb-1">{{ $cooperative->name }}
                                <span class="badge {{ $cooperative->status === 'active' ? 'bg-success' : 'bg-danger' }} ms-2">
                                    {{ ucfirst($cooperative->status) }}
                                </span>
                            </h4>
                            <p class="text-muted mb-0">{{ $cooperative->code }}</p>
                            <div class="row mt-3">
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">{{ __('Location / MCC') }}</small>
                                    <strong>{{ $cooperative->location ?? '—' }}</strong>
                                </div>
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">{{ __('Site Location') }}</small>
                                    <strong>{{ $cooperative->site_location ?? '—' }}</strong>
                                </div>
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">{{ __('Formation Date') }}</small>
                                    <strong>{{ $cooperative->formation_date ? $cooperative->formation_date->format('d M Y') : '—' }}</strong>
                                </div>
                                <div class="col-sm-4 mt-2">
                                    <small class="text-muted d-block">{{ __('Leader') }}</small>
                                    <strong>{{ $cooperative->leader_name ?? '—' }}</strong>
                                </div>
                                <div class="col-sm-4 mt-2">
                                    <small class="text-muted d-block">{{ __('Leader Phone') }}</small>
                                    <strong>{{ $cooperative->leader_phone ?? '—' }}</strong>
                                </div>
                                <div class="col-sm-4 mt-2">
                                    <small class="text-muted d-block">{{ __('Avg Daily Supply') }}</small>
                                    <strong>{{ number_format((float) $cooperative->average_daily_supply, 2) }} L</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Farmers table --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Registered Farmers') }}
                        <span class="badge bg-primary ms-1">{{ $farmers->total() }}</span>
                    </h5>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Avatar') }}</th>
                                    <th>{{ __('Farmer ID') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Contact') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($farmers as $farmer)
                                    <tr>
                                        <td>
                                            @if ($farmer->avatar)
                                                <img src="{{ asset('storage/uploads/avatar/' . $farmer->avatar) }}"
                                                     class="rounded-circle" width="35" height="35"
                                                     alt="{{ $farmer->name }}">
                                            @else
                                                <span class="avatar avatar-sm bg-secondary text-white rounded-circle">
                                                    {{ strtoupper(substr($farmer->name, 0, 1)) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('vender.show', $farmer->id) }}">
                                                {{ $farmer->vender_id }}
                                            </a>
                                        </td>
                                        <td>{{ $farmer->name }}</td>
                                        <td>{{ $farmer->email }}</td>
                                        <td>{{ $farmer->contact }}</td>
                                        <td>
                                            @if ($farmer->is_active == 1)
                                                <span class="badge bg-success p-2 px-3 rounded">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-danger p-2 px-3 rounded">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">{{ __('No farmers registered to this cooperative yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-3">
                        {{ $farmers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
