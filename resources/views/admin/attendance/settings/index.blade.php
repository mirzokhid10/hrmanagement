@extends('admin.layouts.master')

@section('content')
    <div class="app-page-head mb-4">
        <h1 class="app-page-title">{{ __('Attendance Settings') }}</h1>
        <span class="text-muted small">{{ __('Configure where employees can check in via Telegram.') }}</span>
    </div>

    <div class="row g-4">
        <!-- LEFT COLUMN: OFFICE LOCATIONS -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="card-title fw-bold">
                        <i class="fa-solid fa-map-location-dot text-primary me-2"></i> {{ __('Office Locations') }}
                    </h5>
                    <p class="text-muted small">
                        {{ __('Employees must be within the radius of these locations to check in.') }}</p>
                </div>

                <div class="card-body">
                    <!-- Map Container -->
                    <div id="map" class="mb-3"></div>

                    <!-- Add Location Form -->
                    <form action="{{ route('admin.attendance.settings.location.store') }}" method="POST" class="row g-3">
                        @csrf
                        {{-- Company Selection for Admin --}}
                        @if (Auth::user()->isAdmin() && isset($companies))
                            <div class="col-12">
                                <label class="form-label text-xs fw-bold text-uppercase">{{ __('Company') }}</label>
                                <select name="company_id"
                                    class="form-select form-select-sm @error('company_id') is-invalid @enderror">
                                    <option value="">{{ __('Select Company') }}</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}"
                                            {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label text-xs fw-bold text-uppercase">{{ __('Office Name') }}</label>
                            <input type="text" name="name"
                                class="form-control form-control-sm @error('name') is-invalid @enderror"
                                placeholder="{{ __('e.g. Head Office') }}" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-xs fw-bold text-uppercase">{{ __('Radius (Meters)') }}</label>
                            <input type="number" name="radius_meters"
                                class="form-control form-control-sm @error('radius_meters') is-invalid @enderror"
                                value="{{ old('radius_meters', 100) }}" min="10" required>
                            @error('radius_meters')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100"
                                onclick="getCurrentLocation()">
                                <i class="fa-solid fa-location-crosshairs"></i> {{ __('Get My Loc') }}
                            </button>
                        </div>

                        <!-- Hidden Inputs populated by JS -->
                        <input type="hidden" name="latitude" id="lat" value="{{ old('latitude') }}">
                        <input type="hidden" name="longitude" id="lng" value="{{ old('longitude') }}">
                        <input type="hidden" name="address" id="addr" value="{{ old('address') }}">

                        @error('latitude')
                            <div class="col-12 text-danger small">{{ $message }}</div>
                        @enderror
                        @error('longitude')
                            <div class="col-12 text-danger small">{{ $message }}</div>
                        @enderror

                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-plus"></i> {{ __('Add Location') }}
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <!-- List of Locations -->
                    <h6 class="fw-bold mb-3 small text-uppercase">{{ __('Active Locations') }}</h6>
                    <div class="list-group list-group-flush">
                        @forelse($locations as $location)
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold text-dark">
                                        {{ $location->name }}
                                        @if (Auth::user()->isAdmin())
                                            <span
                                                class="badge bg-light text-muted ms-2">{{ $location->company->name ?? 'N/A' }}</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-muted">
                                        <i class="fa-solid fa-location-dot"></i>
                                        {{ number_format($location->latitude, 5) }},
                                        {{ number_format($location->longitude, 5) }}
                                        <span class="mx-1">•</span>
                                        Radius: {{ $location->radius_meters }}m
                                    </div>
                                </div>
                                <form action="{{ route('admin.attendance.settings.location.destroy', $location->id) }}"
                                    method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon btn-light text-danger"
                                        onclick="return confirm('{{ __('Remove this location?') }}')">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="text-center py-3 text-muted small">{{ __('No locations added yet.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: WIFI SETTINGS -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="card-title fw-bold"><i class="fa-solid fa-wifi text-info me-2"></i> {{ __('Office WiFi') }}
                    </h5>
                    <p class="text-muted small">{{ __('Allow check-in only when connected to these IPs.') }}</p>
                </div>

                <div class="card-body">
                    <div class="alert alert-light border border-info-subtle d-flex align-items-center gap-3 p-2 mb-4">
                        <div class="avatar bg-info-subtle text-info rounded-circle">
                            <i class="fa-solid fa-laptop"></i>
                        </div>
                        <div>
                            <div class="text-xs text-muted">{{ __('Your Current IP') }}</div>
                            <div class="fw-bold text-dark font-monospace" id="current-ip">{{ $currentIp }}</div>
                        </div>
                        <button type="button" class="btn btn-xs btn-outline-info ms-auto" onclick="useCurrentIp()">
                            {{ __('Use This') }}
                        </button>
                    </div>

                    <form action="{{ route('admin.attendance.settings.wifi.store') }}" method="POST" class="row g-3">
                        @csrf

                        {{-- Company Selection for Admin --}}
                        @if (Auth::user()->isAdmin() && isset($companies))
                            <div class="col-12">
                                <label class="form-label text-xs fw-bold text-uppercase">{{ __('Company') }}</label>
                                <select name="company_id"
                                    class="form-select form-select-sm @error('company_id') is-invalid @enderror">
                                    <option value="">{{ __('Select Company') }}</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}"
                                            {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="col-12">
                            <label
                                class="form-label text-xs fw-bold text-uppercase">{{ __('Office Location (Optional)') }}</label>
                            <select name="office_location_id"
                                class="form-select form-select-sm @error('office_location_id') is-invalid @enderror">
                                <option value="">{{ __('No specific location') }}</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}"
                                        {{ old('office_location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }} @if (Auth::user()->isAdmin())
                                            ({{ $location->company->name ?? 'N/A' }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('office_location_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label
                                class="form-label text-xs fw-bold text-uppercase">{{ __('WiFi Name / Identifier') }}</label>
                            <input type="text" name="network_name"
                                class="form-control form-control-sm @error('network_name') is-invalid @enderror"
                                placeholder="{{ __('e.g. Office Guest WiFi') }}" value="{{ old('network_name') }}"
                                required>
                            @error('network_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label
                                class="form-label text-xs fw-bold text-uppercase">{{ __('IP Address / Range') }}</label>
                            <input type="text" name="ip_range" id="ip_input"
                                class="form-control form-control-sm @error('ip_range') is-invalid @enderror"
                                placeholder="{{ __('e.g. 192.168.1.1') }}" value="{{ old('ip_range') }}" required>
                            <div class="form-text text-xs">{{ __('Enter the public IP address of your office router.') }}
                            </div>
                            @error('ip_range')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-info btn-sm text-white">
                                <i class="fa-solid fa-plus"></i> {{ __('Add WiFi') }}
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <h6 class="fw-bold mb-3 small text-uppercase">{{ __('Allowed Networks') }}</h6>
                    <div class="list-group list-group-flush">
                        @forelse($wifis as $wifi)
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold text-dark">
                                        {{ $wifi->network_name }}
                                        @if (Auth::user()->isAdmin())
                                            <span
                                                class="badge bg-light text-muted ms-2">{{ $wifi->company->name ?? 'N/A' }}</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-muted font-monospace">
                                        <i class="fa-solid fa-network-wired"></i> {{ $wifi->ip_range }}
                                        @if ($wifi->officeLocation)
                                            <span class="mx-1">•</span> {{ $wifi->officeLocation->name }}
                                        @endif
                                    </div>
                                </div>
                                <form action="{{ route('admin.attendance.settings.wifi.destroy', $wifi->id) }}"
                                    method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon btn-light text-danger"
                                        onclick="return confirm('{{ __('Remove this WiFi?') }}')">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="text-center py-3 text-muted small">{{ __('No WiFi networks added.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- DON'T load Yandex API here - it's already loaded in master.blade.php --}}
    <script>
        var myMap;
        var myPlacemark;

        // Initialize Map
        ymaps.ready(init);

        function init() {
            // Default center: Tashkent
            myMap = new ymaps.Map("map", {
                center: [41.2995, 69.2401],
                zoom: 13,
                controls: ["zoomControl", "searchControl", "geolocationControl"],
            });

            // Handle Map Click
            myMap.events.add("click", function(e) {
                var coords = e.get("coords");
                setMarker(coords);
            });
        }

        // Helper to place/move marker
        function setMarker(coords) {
            // If marker exists, move it
            if (myPlacemark) {
                myPlacemark.geometry.setCoordinates(coords);
            }
            // If not, create it
            else {
                myPlacemark = new ymaps.Placemark(
                    coords, {
                        hintContent: "{{ __('Office Location') }}",
                        balloonContent: "{{ __('Selected Point') }}",
                    }, {
                        preset: "islands#blueDotIcon",
                        draggable: true,
                    },
                );

                myMap.geoObjects.add(myPlacemark);

                // Update inputs when dragged
                myPlacemark.events.add("dragend", function() {
                    updateInputs(myPlacemark.geometry.getCoordinates());
                });
            }

            // Update Hidden Inputs
            updateInputs(coords);

            // Center map on marker
            myMap.panTo(coords);
        }

        function updateInputs(coords) {
            var lat = coords[0].toPrecision(8);
            var lng = coords[1].toPrecision(8);

            document.getElementById("lat").value = lat;
            document.getElementById("lng").value = lng;

            // Reverse Geocode to get address string
            ymaps.geocode(coords).then(function(res) {
                var firstGeoObject = res.geoObjects.get(0);
                var address = firstGeoObject.getAddressLine();
                document.getElementById("addr").value = address;
            });
        }

        // "Get My Location" Button Logic
        function getCurrentLocation() {
            const btn = document.querySelector('button[onclick="getCurrentLocation()"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> {{ __('Locating...') }}';
            btn.disabled = true;

            // Use Yandex Geolocation Provider
            ymaps.geolocation
                .get({
                    provider: "browser",
                    mapStateAutoApply: true,
                })
                .then(
                    function(result) {
                        // Success
                        var coords = result.geoObjects.get(0).geometry.getCoordinates();
                        setMarker(coords);

                        // Reset Button
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    },
                    function(err) {
                        // Error (Fallback to browser API if Yandex fails)
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                function(position) {
                                    var coords = [
                                        position.coords.latitude,
                                        position.coords.longitude,
                                    ];
                                    setMarker(coords);
                                    btn.innerHTML = originalText;
                                    btn.disabled = false;
                                },
                                function(error) {
                                    alert("{{ __('Could not get location. Please allow GPS access.') }}");
                                    btn.innerHTML = originalText;
                                    btn.disabled = false;
                                },
                            );
                        } else {
                            alert("{{ __('Geolocation not supported.') }}");
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                        }
                    },
                );
        }

        // WiFi Helper
        function useCurrentIp() {
            var ip = document.getElementById("current-ip").innerText;
            document.getElementById("ip_input").value = ip;
        }
    </script>
@endpush
