@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">✏️ Edit Farm Field</h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('farm-fields.update', Crypt::encrypt($farmField->id)) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Left column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Field Name</label>
                                    <input type="text" name="field_name" class="form-control" 
                                           value="{{ old('field_name', $farmField->field_name) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Size (hectares)</label>
                                    <input type="number" name="size" class="form-control" step="0.01"
                                           value="{{ old('size', $farmField->size) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Seed Type</label>
                                    <input type="text" name="crop_type" class="form-control" 
                                           value="{{ old('crop_type', $farmField->crop_type) }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="activities" class="form-control" rows="3">{{ old('activities', $farmField->activities) }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Latitude</label>
                                        <input type="text" name="latitude" id="latitude" class="form-control" 
                                               value="{{ old('latitude', $farmField->latitude) }}" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Longitude</label>
                                        <input type="text" name="longitude" id="longitude" class="form-control" 
                                               value="{{ old('longitude', $farmField->longitude) }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Right column -->
                            <div class="col-md-6">
                                <label class="form-label">Update Location on Map</label>
                                <div id="map" class="rounded border" style="height: 350px;"></div>
                                <small class="text-muted">📍 Click on the map to update location</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-warning px-4">
                                <i class="bi bi-pencil-square"></i> Update Field
                            </button>
                            <a href="{{ route('farm-fields.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS & CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    var lat = {{ $farmField->latitude ?? 9.0578 }};
    var lng = {{ $farmField->longitude ?? 7.4951 }};
    var map = L.map('map').setView([lat, lng], 8);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map);

    var marker = L.marker([lat, lng]).addTo(map);

    map.on('click', function(e) {
        var lat = e.latlng.lat.toFixed(7);
        var lng = e.latlng.lng.toFixed(7);

        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        marker.setLatLng(e.latlng);
    });
</script>
@endsection
