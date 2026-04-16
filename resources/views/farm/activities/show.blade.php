@extends('layouts.admin')

@section('page-title')
    {{ __('Activity Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">
        <a href="{{ route('farm-activities.index') }}">{{ __('Farm Activities') }}</a>
    </li>
    <li class="breadcrumb-item active">{{ __('View') }}</li>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-seedling me-2 text-success"></i>
            {{ ucwords(str_replace('_', ' ', $activity->activity_type)) }} - 
            {{ \Carbon\Carbon::parse($activity->activity_date)->format('d M, Y') }}
        </h5>
        <a href="{{ route('farm-activities.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>

    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <h6><strong>Field:</strong></h6>
                <p>{{ $activity->farmField->field_name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6">
                <h6><strong>Worker:</strong></h6>
                <p>{{ $activity->worker ?? '-' }}</p>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <h6><strong>Cost:</strong></h6>
                <p class="text-success">₦{{ number_format($activity->cost, 2) }}</p>
            </div>
            <div class="col-md-6">
                <h6><strong>Date:</strong></h6>
                <p>{{ \Carbon\Carbon::parse($activity->activity_date)->toFormattedDateString() }}</p>
            </div>
        </div>

        <div class="mb-4">
            <h6><strong>Notes Description:</strong></h6>
            <p class="text-muted">{{ $activity->description }}</p>
        </div>

        <hr class="my-4">

        {{-- Image section moved to bottom --}}
<div class="mt-4">
    <h6><strong>Image:</strong></h6>
    @if($activity->image)
        <div class="mb-3">
            <img src="{{ asset($activity->image) }}" 
                 alt="Activity Image" 
                 class="img-fluid rounded shadow-sm border"
                 style="max-width: 400px; cursor: pointer;"
                 data-bs-toggle="modal"
                 data-bs-target="#activityImageModal">
        </div>
        <div>
            <a href="{{ asset($activity->image) }}" 
               class="btn btn-sm btn-outline-primary" 
               download>
                <i class="fas fa-download me-2"></i> {{ __('Download Image') }}
            </a>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="activityImageModal" tabindex="-1" aria-labelledby="activityImageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="activityImageModalLabel">Activity Image</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="{{ asset($activity->image) }}" alt="Full Activity Image" class="img-fluid rounded">
                    </div>
                </div>
            </div>
        </div>
    @else
        <p class="text-muted"><em>{{ __('No image uploaded') }}</em></p>
    @endif
</div>

    </div>
</div>
@endsection