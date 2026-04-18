@extends('layouts.admin')
@section('page-title', __('Training Event') . ' — ' . $event->event_id)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('training-events.index') }}">{{ __('Training Events') }}</a></li>
    <li class="breadcrumb-item active">{{ $event->event_id }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">{{ $event->title }}</h5>
                <a href="{{ route('training-events.edit', $event->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-pencil"></i> {{ __('Edit') }}
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <p class="text-muted mb-1">{{ __('Event ID') }}</p>
                        <code>{{ $event->event_id }}</code>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1">{{ __('Date') }}</p>
                        <strong>{{ \Carbon\Carbon::parse($event->event_date)->format('d M Y') }}</strong>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1">{{ __('Center') }}</p>
                        <strong>{{ $event->center ?? '—' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1">{{ __('Location') }}</p>
                        <strong>{{ $event->location ?? '—' }}</strong>
                    </div>
                </div>
                @if($event->facilitators)
                <div class="mt-3">
                    <p class="text-muted mb-1">{{ __('Facilitators') }}</p>
                    @foreach((array)$event->facilitators as $f)
                    <span class="badge bg-secondary me-1">{{ trim($f) }}</span>
                    @endforeach
                </div>
                @endif
                @if($event->topics_covered)
                <div class="mt-3">
                    <p class="text-muted mb-1">{{ __('Topics Covered') }}</p>
                    <p>{{ $event->topics_covered }}</p>
                </div>
                @endif
                @if($event->notes)
                <div class="mt-2">
                    <p class="text-muted mb-1">{{ __('Notes') }}</p>
                    <p class="mb-0">{{ $event->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        @if($event->attendees->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">{{ __('Attendees') }} ({{ $event->attendees->count() }})</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>{{ __('Name') }}</th></tr>
                        </thead>
                        <tbody>
                            @foreach($event->attendees as $i => $att)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $att->farmer_name ?? ($att->farmer->name ?? '—') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @if($event->materials->isNotEmpty())
        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ __('Materials Distributed') }}</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>{{ __('Material') }}</th><th>{{ __('Qty') }}</th></tr>
                        </thead>
                        <tbody>
                            @foreach($event->materials as $mat)
                            <tr>
                                <td>{{ $mat->material_name }}</td>
                                <td>{{ number_format($mat->quantity_distributed) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <a href="{{ route('training-events.index') }}" class="btn btn-secondary w-100">
            <i class="ti ti-arrow-left"></i> {{ __('Back to Events') }}
        </a>
    </div>
</div>
@endsection
