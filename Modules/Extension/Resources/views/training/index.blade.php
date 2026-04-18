@extends('layouts.admin')
@section('page-title', __('Training Events'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Training Events') }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('Training Events') }}</h5>
        @can('manage extension agents')
        <a href="{{ route('training-events.create') }}" class="btn btn-primary btn-sm">
            <i class="ti ti-plus"></i> {{ __('Record Event') }}
        </a>
        @endcan
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Event ID') }}</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Center') }}</th>
                        <th>{{ __('Location') }}</th>
                        <th>{{ __('Attendees') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                    <tr>
                        <td><code>{{ $event->event_id }}</code></td>
                        <td>{{ $event->title }}</td>
                        <td>{{ \Carbon\Carbon::parse($event->event_date)->format('d M Y') }}</td>
                        <td>{{ $event->center ?? '—' }}</td>
                        <td>{{ $event->location ?? '—' }}</td>
                        <td><span class="badge bg-primary">{{ $event->attendees_count }}</span></td>
                        <td>
                            <a href="{{ route('training-events.show', $event->id) }}" class="btn btn-xs btn-outline-info">
                                <i class="ti ti-eye"></i>
                            </a>
                            <a href="{{ route('training-events.edit', $event->id) }}" class="btn btn-xs btn-outline-primary">
                                <i class="ti ti-pencil"></i>
                            </a>
                            <form action="{{ route('training-events.destroy', $event->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('{{ __('Delete event?') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger"><i class="ti ti-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">{{ __('No training events found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($events->hasPages())
    <div class="card-footer">{{ $events->links() }}</div>
    @endif
</div>
@endsection
