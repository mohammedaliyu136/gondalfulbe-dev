@extends('layouts.admin')
@section('page-title', __('Field Visit') . ' — ' . $visit->visit_id)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('field-visits.index') }}">{{ __('Field Visits') }}</a></li>
    <li class="breadcrumb-item active">{{ $visit->visit_id }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0"><code>{{ $visit->visit_id }}</code></h5>
                <div>
                    <a href="{{ route('field-visits.edit', $visit->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="ti ti-pencil"></i> {{ __('Edit') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p class="text-muted mb-1">{{ __('Agent') }}</p>
                        <strong>{{ $visit->agent->name ?? '—' }}</strong>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1">{{ __('Visit Date') }}</p>
                        <strong>{{ \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') }}</strong>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1">{{ __('Center') }}</p>
                        <strong>{{ $visit->center ?? '—' }}</strong>
                    </div>
                    <div class="col-md-4 mt-3">
                        <p class="text-muted mb-1">{{ __('Community') }}</p>
                        <strong>{{ $visit->community ?? '—' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        @if($visit->topics->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">{{ __('Topics Discussed') }}</h6></div>
            <div class="card-body">
                @foreach($visit->topics as $t)
                <span class="badge bg-primary me-1 mb-1">{{ $t->topic }}</span>
                @endforeach
            </div>
        </div>
        @endif

        @if($visit->farmers->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">{{ __('Farmers Visited') }} ({{ $visit->farmers->count() }})</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>{{ __('Farmer') }}</th></tr>
                        </thead>
                        <tbody>
                            @foreach($visit->farmers as $i => $fv)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $fv->farmer_name ?? ($fv->farmer->name ?? '—') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @if($visit->notes)
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">{{ __('Notes') }}</h6></div>
            <div class="card-body">{{ $visit->notes }}</div>
        </div>
        @endif

        @if($visit->follow_up_required)
        <div class="card border-warning mb-4">
            <div class="card-header bg-warning text-dark"><h6 class="mb-0">{{ __('Follow-up Required') }}</h6></div>
            <div class="card-body">
                @if($visit->follow_up_date)
                <p class="mb-1"><strong>{{ __('Date:') }}</strong> {{ \Carbon\Carbon::parse($visit->follow_up_date)->format('d M Y') }}</p>
                @endif
                @if($visit->follow_up_note)
                <p class="mb-0">{{ $visit->follow_up_note }}</p>
                @endif
            </div>
        </div>
        @endif

        @if($visit->photos->isNotEmpty())
        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ __('Photos') }}</h6></div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach($visit->photos as $photo)
                    <div class="col-md-4">
                        <a href="{{ Storage::url($photo->photo_path) }}" target="_blank">
                            <img src="{{ Storage::url($photo->photo_path) }}" class="img-fluid rounded">
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <a href="{{ route('field-visits.index') }}" class="btn btn-secondary w-100">
            <i class="ti ti-arrow-left"></i> {{ __('Back to Visits') }}
        </a>
    </div>
</div>
@endsection
