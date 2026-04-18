@extends('layouts.admin')
@section('page-title', __('Sponsors'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Sponsors') }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('Sponsors') }}</h5>
        @can('manage sponsors')
        <a href="{{ route('admin.sponsors.create') }}" class="btn btn-primary btn-sm">
            <i class="ti ti-plus"></i> {{ __('Add Sponsor') }}
        </a>
        @endcan
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Organization') }}</th>
                        <th>{{ __('Contact Person') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Projects') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sponsors as $sponsor)
                    <tr>
                        <td><code>{{ $sponsor->sponsor_code }}</code></td>
                        <td>{{ $sponsor->organization_name }}</td>
                        <td>{{ $sponsor->contact_person }}</td>
                        <td>{{ $sponsor->email }}</td>
                        <td><span class="badge bg-secondary">{{ $sponsor->organization_type }}</span></td>
                        <td><span class="badge bg-primary">{{ $sponsor->projects_count }}</span></td>
                        <td>
                            @if($sponsor->status === 'active')
                            <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                            <span class="badge bg-secondary">{{ ucfirst($sponsor->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.sponsors.show', $sponsor->id) }}" class="btn btn-xs btn-outline-info">
                                <i class="ti ti-eye"></i>
                            </a>
                            <a href="{{ route('admin.sponsors.edit', $sponsor->id) }}" class="btn btn-xs btn-outline-primary">
                                <i class="ti ti-pencil"></i>
                            </a>
                            <form action="{{ route('admin.sponsors.destroy', $sponsor->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('{{ __('Delete sponsor?') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger"><i class="ti ti-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">{{ __('No sponsors found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sponsors->hasPages())
    <div class="card-footer">{{ $sponsors->links() }}</div>
    @endif
</div>
@endsection
