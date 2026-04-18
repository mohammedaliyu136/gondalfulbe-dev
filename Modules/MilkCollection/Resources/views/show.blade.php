@extends('layouts.admin')
@section('page-title'){{ __('Collection Detail') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('milk-collections.index') }}">{{ __('Milk Collection') }}</a></li>
    <li class="breadcrumb-item">{{ $collection->collection_id }}</li>
@endsection
@section('content')
<div class="row">
<div class="col-xl-8">
<div class="card">
<div class="card-header d-flex align-items-center justify-content-between">
    <h5 class="mb-0">{{ $collection->collection_id }}</h5>
    <span class="badge {{ $collection->grade_badge_class }} p-2 px-3">{{ $collection->quality_grade }} — {{ $collection->grade_label }}</span>
</div>
<div class="card-body">
    <div class="row g-3">
        <div class="col-md-6"><strong>{{ __('Date') }}:</strong> {{ $collection->date->format('d M Y') }}</div>
        <div class="col-md-6"><strong>{{ __('Time') }}:</strong> {{ $collection->time ?? '—' }}</div>
        <div class="col-md-6"><strong>{{ __('MCC') }}:</strong> {{ $collection->mcc }}</div>
        <div class="col-md-6"><strong>{{ __('Farmer') }}:</strong> {{ $collection->farmer?->name ?? '—' }}</div>
        <div class="col-md-6"><strong>{{ __('Quantity') }}:</strong> {{ number_format($collection->quantity_litres, 2) }} L</div>
        <div class="col-md-6"><strong>{{ __('Temperature') }}:</strong> {{ $collection->temperature_celsius ? $collection->temperature_celsius . ' °C' : '—' }}</div>
        @if($collection->quality_grade === 'C')
        <div class="col-12"><strong>{{ __('Rejection Reason') }}:</strong> {{ $collection->rejection_reason }}</div>
        @endif
        <div class="col-md-6"><strong>{{ __('Batch ID') }}:</strong> {{ $collection->collection_batch_id ?? '—' }}</div>
        <div class="col-md-6"><strong>{{ __('Recorded By') }}:</strong> {{ $collection->recorder?->name ?? '—' }}</div>
        @if($collection->notes)
        <div class="col-12"><strong>{{ __('Notes') }}:</strong> {{ $collection->notes }}</div>
        @endif
        @if($collection->photo_path)
        <div class="col-12"><strong>{{ __('Photo') }}:</strong><br>
            <img src="{{ asset('storage/' . $collection->photo_path) }}" class="img-fluid rounded mt-2" style="max-height:200px">
        </div>
        @endif
    </div>
    <div class="mt-4">
        <a href="{{ route('milk-collections.index') }}" class="btn btn-secondary">{{ __('Back') }}</a>
        @can('edit milk collection')
        <a href="{{ route('milk-collections.edit', $collection->id) }}" class="btn btn-primary ms-2">{{ __('Edit') }}</a>
        @endcan
    </div>
</div></div></div></div>
@endsection
