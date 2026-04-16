@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Farm Activity') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('farm-activities.index') }}">{{ __('Farm Activities') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit') }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Update Activity') }}</h5>
            </div>

            <div class="card-body">
                <form action="{{ route('farm-activities.update', [$farmActivity->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Farm Field -->
                    <div class="mb-3">
                        <label for="farm_field_id" class="form-label">Farm Field</label>
                        <select name="farm_field_id" id="farm_field_id" class="form-select @error('farm_field_id') is-invalid @enderror" required>
                            <option value="">-- Select Field --</option>
                            @foreach($fields as $field)
                                <option value="{{ $field->id }}" {{ $farmActivity->farm_field_id == $field->id ? 'selected' : '' }}>
                                    {{ $field->field_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('farm_field_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Date -->
                    <div class="mb-3">
                        <label for="activity_date" class="form-label">Date</label>
                        <input type="date" name="activity_date" id="activity_date" class="form-control @error('activity_date') is-invalid @enderror" value="{{ $farmActivity->activity_date }}" required>
                        @error('activity_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Activity Type -->
                    <div class="mb-3">
                        <label for="activity_type" class="form-label">Activity Type</label>
                        <input type="text" name="activity_type" id="activity_type" class="form-control @error('activity_type') is-invalid @enderror" value="{{ $farmActivity->activity_type }}" required>
                        @error('activity_type')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ $farmActivity->description }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Worker -->
                    <div class="mb-3">
                        <label for="worker" class="form-label">Worker (optional)</label>
                        <input type="text" name="worker" id="worker" class="form-control @error('worker') is-invalid @enderror" value="{{ $farmActivity->worker }}">
                        @error('worker')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Cost -->
                    <div class="mb-3">
                        <label for="cost" class="form-label">Cost (₦)</label>
                        <input type="number" step="0.01" name="cost" id="cost" class="form-control @error('cost') is-invalid @enderror" value="{{ $farmActivity->cost }}">
                        @error('cost')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Image Upload -->
                    <div class="mb-3">
                        <label for="image" class="form-label">Update Image (optional)</label>
                        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror">
                        @error('image')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror

                        @if($farmActivity->image)
                            <div class="mt-3">
                                <p><strong>Current Image:</strong></p>
                                <img src="{{ asset($farmActivity->image) }}" alt="Current Activity Image" class="img-fluid rounded" style="max-width: 300px;">
                            </div>
                        @endif
                    </div>

                    <!-- Submit -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> {{ __('Update Activity') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
