@extends('layouts.admin')
@section('page-title', $sponsor->organization_name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.sponsors.index') }}">{{ __('Sponsors') }}</a></li>
    <li class="breadcrumb-item active">{{ $sponsor->organization_name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">{{ $sponsor->organization_name }}</h5>
                <div>
                    <span class="badge bg-{{ $sponsor->status === 'active' ? 'success' : 'secondary' }} me-2">{{ ucfirst($sponsor->status) }}</span>
                    <a href="{{ route('admin.sponsors.edit', $sponsor->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="ti ti-pencil"></i> {{ __('Edit') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">{{ __('Contact Person') }}</p>
                        <strong>{{ $sponsor->contact_person }}</strong>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">{{ __('Email') }}</p>
                        <strong>{{ $sponsor->email }}</strong>
                    </div>
                    <div class="col-md-6 mt-3">
                        <p class="text-muted mb-1">{{ __('Phone') }}</p>
                        <strong>{{ $sponsor->phone ?? '—' }}</strong>
                    </div>
                    <div class="col-md-6 mt-3">
                        <p class="text-muted mb-1">{{ __('Type') }}</p>
                        <span class="badge bg-secondary">{{ $sponsor->organization_type }}</span>
                    </div>
                    <div class="col-md-6 mt-3">
                        <p class="text-muted mb-1">{{ __('Country') }}</p>
                        <strong>{{ $sponsor->country ?? '—' }}</strong>
                    </div>
                    <div class="col-md-6 mt-3">
                        <p class="text-muted mb-1">{{ __('Sponsor Code') }}</p>
                        <code>{{ $sponsor->sponsor_code }}</code>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">{{ __('Projects') }} ({{ $sponsor->projects->count() }})</h6>
                <a href="{{ route('admin.sponsors.assign-project', $sponsor->id) }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-plus"></i> {{ __('Create Project') }}
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Budget') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Farmers') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sponsor->projects as $project)
                            <tr>
                                <td><code>{{ $project->project_code }}</code></td>
                                <td>{{ $project->title }}</td>
                                <td>₦{{ number_format($project->budget, 2) }}</td>
                                <td><span class="badge bg-{{ $project->status === 'Active' ? 'success' : 'secondary' }}">{{ $project->status }}</span></td>
                                <td>{{ $project->farmers->count() }}</td>
                                <td>
                                    <a href="{{ route('admin.sponsors.manage-farmers', [$sponsor->id, $project->id]) }}"
                                       class="btn btn-xs btn-outline-primary">{{ __('Manage Farmers') }}</a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-3 text-muted">{{ __('No projects yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
