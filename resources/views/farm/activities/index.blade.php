@extends('layouts.admin')

@section('page-title')
    {{ __('Farm Activities') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Farm Activities') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12 mb-3">
            <a href="{{ route('farm-activities.create') }}" class="btn btn-success">
                <i class="fas fa-plus-circle"></i> Add Activity
            </a>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Activity Records') }}</h5>
                </div>

                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Field</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activities as $index => $activity)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ \Carbon\Carbon::parse($activity->activity_date)->format('d M Y') }}</td>
                                    <td>{{ $activity->farmField->field_name ?? 'N/A' }}</td>
                                    <td>{{ $activity->activity_type }}</td>
                                    <td>
                                        <a href="{{ route('farm-activities.show', [$activity->id]) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    
                                        <a href="{{ route('farm-activities.edit', [$activity->id]) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    
                                        <form action="{{ route('farm-activities.destroy', [$activity->id]) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No activity records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
