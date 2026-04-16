@extends('layouts.admin')

@section('page-title', __('Activity Report'))

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('Activity Report') }}</h5>
        <a href="{{ route('farm-activities.report.form') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Report Form
        </a>
    </div>

    <div class="card-body">
        <p><strong>Report Range:</strong> {{ \Carbon\Carbon::parse($request->start_date)->toFormattedDateString() }} - {{ \Carbon\Carbon::parse($request->end_date)->toFormattedDateString() }}</p>
        @if($request->farm_field_id)
            <p><strong>Field:</strong> {{ \App\Models\FarmField::find($request->farm_field_id)->field_name }}</p>
        @else
            <p><strong>Field:</strong> All Fields</p>
        @endif

        @if($activities->count())
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Field</th>
                        <th>Activity Type</th>
                        <th>Worker</th>
                        <th>Cost</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($activity->activity_date)->format('d M Y') }}</td>
                            <td>{{ $activity->farmField->field_name ?? 'N/A' }}</td>
                            <td>{{ $activity->activity_type }}</td>
                            <td>{{ $activity->worker }}</td>
                            <td>₦{{ number_format($activity->cost, 2) }}</td>
                            <td>{{ $activity->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div class="alert alert-warning">{{ __('No activities found for the selected criteria.') }}</div>
        @endif
       <a href="{{ route('farm-activities.report.export') }}?field_id={{ request('field_id') }}&from_date={{ request('from_date') }}&to_date={{ request('to_date') }}" 
   class="btn btn-success mb-3">
    <i class="fas fa-file-excel"></i> Export to Excel
</a>

    </div>
</div>
@endsection
