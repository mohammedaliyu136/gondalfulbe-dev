@extends('layouts.admin')

@section('content')
<div style="background-color: #ffffff; padding: 20px; border-radius: 10px;">
<h3>Farm Fields</h3>
<a href="{{ route('farm-fields.create', 1) }}" class="btn btn-success mb-3">Add Farm Field</a>

<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Field Name</th>
                <th>Size (ha)</th>
                <th>Crop</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($farmFields as $field)
                <tr>
                    <td>{{ $field->field_name }}</td>
                    <td>{{ $field->size }}</td>
                    <td>{{ $field->crop_type }}</td>
                    
                    <td>
                        <div class="action-btn me-2">
                            <a href="{{ route('farm-fields.show', \Crypt::encrypt($field['id'])) }}"
                                 class="mx-3 btn btn-sm align-items-center bg-warning" data-bs-toggle="tooltip"
                                 title="{{ __('View') }}">
                                 <i class="ti ti-eye text-white text-white"></i>
                            </a>
                        </div>
                        
                        <div class="action-btn me-2">
                            <a href="{{ route('farm-fields.edit', \Crypt::encrypt($field['id'])) }}"
                                 class="mx-3 btn btn-sm align-items-center bg-info" data-bs-toggle="tooltip"
                                 title="{{ __('Edit') }}">
                                 <i class="ti ti-pencil text-white text-white"></i>
                            </a>
                        </div>
                        
                        <div class="action-btn ">
                            {!! Form::open(['method' => 'DELETE', 'route' => ['farm-fields.destroy', \Crypt::encrypt($field['id']) ], 'id' => 'delete-form-' . $field['id']]) !!}
                                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger" data-bs-toggle="tooltip"
                                    data-original-title="{{ __('Delete') }}" title="{{ __('Delete') }}"
                                    data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                    data-confirm-yes="document.getElementById('delete-form-{{ $field['id'] }}').submit();">
                                    <i class="ti ti-trash text-white text-white"></i>
                                </a>
                            {!! Form::close() !!}
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Map Modals -->
@foreach($farmFields as $field)
    @if($field->latitude && $field->longitude)
        <div class="modal fade" id="mapModal{{ $field->id }}" tabindex="-1" aria-labelledby="mapLabel{{ $field->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mapLabel{{ $field->id }}">{{ $field->field_name }} Map</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="map-{{ $field->id }}" style="height: 500px;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                var modal = document.getElementById('mapModal{{ $field->id }}');
                modal.addEventListener('shown.bs.modal', function () {
                    var mapId = 'map-{{ $field->id }}';
                    if (!window['mapInstance{{ $field->id }}']) {
                        var map = L.map(mapId).setView([{{ $field->latitude }}, {{ $field->longitude }}], 13);
                        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                            attribution: '&copy; <a href="https://www.esri.com/">Esri</a> | Tiles: Esri World Imagery'
                        }).addTo(map);
                        L.marker([{{ $field->latitude }}, {{ $field->longitude }}]).addTo(map);
                        window['mapInstance{{ $field->id }}'] = map;
                    }
                });
            });
        </script>
    @endif
@endforeach

<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
@endsection
