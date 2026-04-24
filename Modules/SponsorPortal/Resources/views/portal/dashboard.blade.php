@extends('sponsorportal::layouts.sponsor')
@section('page-title', __('My Projects'))

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h4>{{ __('Welcome,') }} {{ auth('sponsor')->user()->contact_person ?? auth('sponsor')->user()->organization_name }}</h4>
        <p class="text-muted">{{ __('Your investment portfolio and impact metrics.') }}</p>
    </div>
</div>

<div class="row g-4">
    @forelse($projects as $project)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge bg-{{ $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'secondary' : 'warning text-dark') }}">
                        {{ ucfirst($project->status) }}
                    </span>
                    <small class="text-muted">{{ $project->project_code }}</small>
                </div>
                <h5 class="card-title mb-1">{{ $project->title }}</h5>
                @if($project->description)
                <p class="text-muted small mb-3">{{ Str::limit($project->description, 100) }}</p>
                @endif
                <div class="row g-2 text-center mb-3">
                    <div class="col-4">
                        <div class="p-2 bg-light rounded">
                            <div class="fw-bold">₦{{ number_format(((float) $project->budget) / 1000, 0) }}K</div>
                            <div class="text-muted" style="font-size:.7rem">{{ __('Budget') }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-light rounded">
                            <div class="fw-bold">{{ $project->farmers_count ?? $project->farmers->count() }}</div>
                            <div class="text-muted" style="font-size:.7rem">{{ __('Farmers') }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-light rounded">
                            <div class="fw-bold">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M Y') : '—' }}</div>
                            <div class="text-muted" style="font-size:.7rem">{{ __('Start') }}</div>
                        </div>
                    </div>
                </div>
                <a href="{{ route('sponsor.project', $project->id) }}" class="btn btn-primary btn-sm w-100">
                    {{ __('View Impact Dashboard') }} <i class="ti ti-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5 text-muted">
                <i class="ti ti-folder-off fs-1 d-block mb-2"></i>
                {{ __('No projects assigned to your account yet.') }}
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection
