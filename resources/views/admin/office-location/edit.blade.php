@extends('admin.layouts.master')

@push('styles')
    {{-- Make sure Yandex Maps API script is included here or in your master layout --}}
    {{-- <script src="https://api-maps.yandex.ru/2.1/?apikey=YOUR_API_KEY&lang=en_US" type="text/javascript"></script> --}}
@endpush

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">
                Edit Office Location: {{ $officeLocation->name }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.office-location.index') }}">Office Locations</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>
        </div>
        <a class="btn btn-primary waves-effect waves-light" href="{{ route('admin.office-location.index') }}">
            <i class="fa-solid fa-map-location-dot me-2"></i> Back to Office Locations
        </a>
    </div>

    <div class="card p-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="card-header py-3">
            <h5 class="card-title">Office Location Details</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.office-location.update', $officeLocation) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    {{-- Company Display - Only for Super Admins --}}
                    @if (auth()->user()->isAdmin())
                        <div class="mb-3 col-12 col-md-6">
                            <label for="company_name" class="form-label">Company</label>
                            <input type="text" id="company_name" class="form-control"
                                value="{{ $officeLocation->company->name ?? 'N/A' }}" disabled>
                            {{-- Hidden input to ensure company_id is passed if needed, though controller uses $officeLocation->company_id --}}
                            <input type="hidden" name="company_id" value="{{ $officeLocation->company_id }}">
                        </div>
                    @endif

                    <div class="mb-3 {{ auth()->user()->isAdmin() ? 'col-12 col-md-6' : 'col-12' }}">
                        <label for="name" class="form-label">Office Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name"
                            class="form-control @error('name') is-invalid @enderror" required
                            value="{{ old('name', $officeLocation->name) }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3 col-12 col-md-6">
                        <label for="radius_meters" class="form-label">Geofence Radius (meters) <span
                                class="text-danger">*</span></label>
                        <input type="number" name="radius_meters" id="radius_meters"
                            class="form-control @error('radius_meters') is-invalid @enderror" required
                            value="{{ old('radius_meters', $officeLocation->radius_meters) }}" min="10"
                            max="1000">
                        @error('radius_meters')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="3"
                        placeholder="Office address">{{ old('address', $officeLocation->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="mb-3 col-12 col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                {{ old('is_active', $officeLocation->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active Location
                            </label>
                            <small class="form-text text-muted d-block">If unchecked, this location will not be used for
                                attendance tracking.</small>
                        </div>
                    </div>
                    <div class="mb-3 col-12 col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_primary" id="is_primary" value="1"
                                {{ old('is_primary', $officeLocation->is_primary) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_primary">
                                Primary Office Location
                            </label>
                            <small class="form-text text-muted d-block">Only one location can be primary per company.
                                Setting this will unset others.</small>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="latitude" id="latitude"
                    value="{{ old('latitude', $officeLocation->latitude) }}">
                <input type="hidden" name="longitude" id="longitude"
                    value="{{ old('longitude', $officeLocation->longitude) }}">

                <div class="mb-3">
                    <label class="form-label">Select Office Location on Map <span class="text-danger">*</span></label>
                    <div id="map" style="height: 400px; width: 100%;"></div>
                    @error('latitude')
                        <div class="text-danger mt-1">Please select a location on the map.</div>
                    @enderror
                </div>

                <div class="text-end">
                    <a href="{{ route('admin.office-location.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Office Location</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        ymaps.ready(init);

        function init() {
            const initialLatitude = parseFloat(document.getElementById('latitude').value);
            const initialLongitude = parseFloat(document.getElementById('longitude').value);

            const defaultCenter = [41.2995, 69.2401]; // Tashkent
            const defaultZoom = 12;

            const map = new ymaps.Map("map", {
                center: (initialLatitude && initialLongitude) ? [initialLatitude, initialLongitude] : defaultCenter,
                zoom: defaultZoom
            });

            let placemark = null;

            if (initialLatitude && initialLongitude) {
                placemark = new ymaps.Placemark([initialLatitude, initialLongitude], {}, {
                    preset: 'islands#blueDotIcon'
                });
                map.geoObjects.add(placemark);
            }

            map.events.add('click', function(e) {
                const coords = e.get('coords');

                if (placemark) {
                    placemark.geometry.setCoordinates(coords);
                } else {
                    placemark = new ymaps.Placemark(coords, {}, {
                        preset: 'islands#blueDotIcon'
                    });
                    map.geoObjects.add(placemark);
                }

                document.getElementById('latitude').value = coords[0];
                document.getElementById('longitude').value = coords[1];
            });
        }
    </script>
@endpush
