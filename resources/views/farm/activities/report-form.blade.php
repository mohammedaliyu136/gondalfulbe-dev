@extends('layouts.admin')

@section('page-title', __('Farm Activity Report'))

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h5>{{ __('Generate Farm Activity Report') }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('farm-activities.report.generate') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="farm_field_id" class="form-label">Farm Field</label>
                    <select name="farm_field_id" id="farm_field_id" class="form-select">
                        <option value="">-- All Fields --</option>
                        @foreach($fields as $field)
                            <option value="{{ $field->id }}">{{ $field->field_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-alt me-1"></i> Generate Report
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
