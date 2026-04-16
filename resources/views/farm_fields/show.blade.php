@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 text-white">🌱 Farm Field Details</h4>
                    <a href="{{ route('farm-fields.index') }}" class="btn btn-light btn-sm">
                        ← Back to List
                    </a>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Field Name:</dt>
                        <dd class="col-sm-8">{{ $farmField->field_name }}</dd>

                        <dt class="col-sm-4">Size (hectares):</dt>
                        <dd class="col-sm-8">{{ $farmField->size }}</dd>

                        <dt class="col-sm-4">Crop / Seed Type:</dt>
                        <dd class="col-sm-8">{{ $farmField->crop_type ?? '—' }}</dd>

                        <dt class="col-sm-4">Description:</dt>
                        <dd class="col-sm-8">{{ $farmField->activities ?? '—' }}</dd>

                        <dt class="col-sm-4">Latitude:</dt>
                        <dd class="col-sm-8">{{ $farmField->latitude ?? '—' }}</dd>

                        <dt class="col-sm-4">Longitude:</dt>
                        <dd class="col-sm-8">{{ $farmField->longitude ?? '—' }}</dd>
                    </dl>

                    @if($farmField->latitude && $farmField->longitude)
                        <div class="mt-4">
                            <label class="form-label">📍 Field Location</label>
                            <div id="map" class="rounded border" style="height: 300px;"></div>
                        </div>
                    @endif
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('farm-fields.edit', Crypt::encrypt($farmField->id)) }}" class="btn btn-warning">
                        ✏️ Edit
                    </a>
                    <form action="{{ route('farm-fields.destroy', Crypt::encrypt($farmField->id)) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this field?')">
                            🗑️ Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@if($farmField->latitude && $farmField->longitude)
    <!-- Leaflet JS & CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        var map = L.map('map').setView([{{ $farmField->latitude }}, {{ $farmField->longitude }}], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
        }).addTo(map);

        L.marker([{{ $farmField->latitude }}, {{ $farmField->longitude }}]).addTo(map)
            .bindPopup("<b>{{ $farmField->field_name }}</b><br>{{ $farmField->size }} ha")
            .openPopup();
    </script>
@endif
@endsection
