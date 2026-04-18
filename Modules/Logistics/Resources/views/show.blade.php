@extends('layouts.admin')
@section('page-title'){{ __('Trip Detail') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('logistics.index') }}">{{ __('Logistics') }}</a></li>
    <li class="breadcrumb-item">{{ $trip->trip_id }}</li>
@endsection
@section('content')
<div class="row"><div class="col-xl-8">
<div class="card"><div class="card-header d-flex align-items-center justify-content-between">
    <h5 class="mb-0">{{ $trip->trip_id }}</h5>
    <span class="badge {{ $trip->status_badge_class }} p-2 px-3">{{ $trip->status }}</span>
</div><div class="card-body">
    <div class="row g-3">
        <div class="col-md-6"><strong>{{ __('Date') }}:</strong> {{ $trip->trip_date->format('d M Y') }}</div>
        <div class="col-md-6"><strong>{{ __('Route') }}:</strong> {{ $trip->mcc_source }} → {{ $trip->destination }}</div>
        <div class="col-md-6"><strong>{{ __('Rider') }}:</strong> {{ $trip->rider?->name ?? '—' }}</div>
        <div class="col-md-6"><strong>{{ __('Vehicle') }}:</strong> {{ $trip->vehicle_registration ?? '—' }}</div>
        <div class="col-md-6"><strong>{{ __('Departure') }}:</strong> {{ $trip->departure_time ?? '—' }}</div>
        <div class="col-md-6"><strong>{{ __('Arrival') }}:</strong> {{ $trip->arrival_time ?? '—' }}</div>
        <div class="col-md-6"><strong>{{ __('Litres Transported') }}:</strong> {{ number_format($trip->litres_transported, 2) }} L</div>
        <div class="col-md-6"><strong>{{ __('Batch ID') }}:</strong> {{ $trip->collection_batch_id ?? '—' }}</div>
    </div>
    <hr>
    <h6>{{ __('Cost Breakdown') }}</h6>
    <div class="row g-3">
        <div class="col-md-4"><strong>{{ __('Fuel Cost') }}:</strong> ₦{{ number_format($trip->fuel_cost, 2) }}</div>
        <div class="col-md-4"><strong>{{ __('Other Expenses') }}:</strong> ₦{{ number_format($trip->other_expenses, 2) }}</div>
        <div class="col-md-4"><strong>{{ __('Total Cost') }}:</strong> ₦{{ number_format($trip->total_cost, 2) }}</div>
        <div class="col-md-4"><strong>{{ __('Cost/Litre') }}:</strong> {{ $trip->cost_per_litre ? '₦'.number_format($trip->cost_per_litre, 4) : '—' }}</div>
    </div>
    <div class="mt-4">
        <a href="{{ route('logistics.index') }}" class="btn btn-secondary">{{ __('Back') }}</a>
        @can('edit logistics trip')
        <a href="{{ route('logistics.edit', $trip->id) }}" class="btn btn-primary ms-2">{{ __('Edit') }}</a>
        @if($trip->status !== 'Completed')
        <form action="{{ route('logistics.complete', $trip->id) }}" method="POST" class="d-inline ms-2">@csrf
            <button type="submit" class="btn btn-success">{{ __('Mark Completed') }}</button>
        </form>
        @endif
        @endcan
    </div>
</div></div></div></div>
@endsection
