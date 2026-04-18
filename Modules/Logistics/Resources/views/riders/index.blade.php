@extends('layouts.admin')
@section('page-title'){{ __('Riders') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('logistics.index') }}">{{ __('Logistics') }}</a></li>
    <li class="breadcrumb-item">{{ __('Riders') }}</li>
@endsection
@section('action-btn')
    <a href="#" data-url="{{ route('riders.create') }}" data-ajax-popup="true" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Add Rider') }}"><i class="ti ti-plus"></i></a>
@endsection
@section('content')
<div class="card"><div class="card-header card-body table-border-style"><div class="table-responsive">
    <table class="table datatable">
        <thead><tr>
            <th>{{ __('Name') }}</th><th>{{ __('Contact') }}</th><th>{{ __('Centre') }}</th>
            <th>{{ __('Rate/Trip') }}</th><th>{{ __('Status') }}</th><th>{{ __('Action') }}</th>
        </tr></thead>
        <tbody>
            @foreach($riders as $r)
            <tr>
                <td>{{ $r->name }}</td>
                <td>{{ $r->contact ?? '—' }}</td>
                <td>{{ $r->collection_centre ?? '—' }}</td>
                <td>₦{{ number_format($r->amount_per_trip, 2) }}</td>
                <td>@if($r->is_active)<span class="badge bg-success p-2 px-3">{{ __('Active') }}</span>@else<span class="badge bg-danger p-2 px-3">{{ __('Inactive') }}</span>@endif</td>
                <td>
                    <a href="#" data-url="{{ route('riders.edit', $r->id) }}" data-ajax-popup="true" class="btn btn-sm btn-primary"><i class="ti ti-pencil text-white"></i></a>
                    <form action="{{ route('riders.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"><i class="ti ti-trash text-white"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div><div class="mt-3">{{ $riders->links() }}</div></div></div>
@endsection
