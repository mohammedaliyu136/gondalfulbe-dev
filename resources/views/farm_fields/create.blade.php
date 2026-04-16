@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">🌱 Create Farm Field</h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('farm-fields.store', 1) }}">
                        @csrf
                        
                        <div class="row">
                            <!-- Left column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Field Name</label>
                                    <input type="text" name="field_name" class="form-control" placeholder="Enter field name" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Size (hectares)</label>
                                    <input type="number" name="size" class="form-control" step="0.01" placeholder="e.g. 2.5" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Seed Type</label>
                                    <input type="text" name="crop_type" class="form-control" placeholder="e.g. Maize, Rice, Soybeans">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="activities" class="form-control" rows="3" placeholder="Short description or notes"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Latitude</label>
                                        <input type="text" name="latitude" id="latitude" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Longitude</label>
                                        <input type="text" name="longitude" id="longitude" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Right column -->
                            <div class="col-md-6">
                                <label class="form-label">Select Location on Map</label>
                                <div id="map" class="rounded border" style="height: 350px;"></div>
                                <small class="text-muted">📍 Click on the map to pick location</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-save"></i> Save Field
                            </button>
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
    var map = L.map('map').setView([9.0578, 7.4951], 6); // Default Nigeria center

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map);

    var marker;
    map.on('click', function(e) {
        var lat = e.latlng.lat.toFixed(7);
        var lng = e.latlng.lng.toFixed(7);

        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }
    });
</script>
@endsection
