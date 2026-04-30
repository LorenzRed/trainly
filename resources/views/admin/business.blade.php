<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Trainly | Nearest Gym</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Inter, "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: #07130b;
            color: #f0f3f8;
            min-height: 100vh;
        }

        .page {
            max-width: 1100px;
            margin: 0 auto;
            padding: 18px;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #fde68a;
            text-decoration: none;
            font-weight: 600;
        }

        .status {
            color: #cfd9ed;
            font-size: 0.92rem;
            background: rgba(15, 27, 19, 0.9);
            border: 1px solid rgba(251, 191, 36, 0.2);
            border-radius: 999px;
            padding: 8px 12px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .status.status-loading {
            color: #fde68a;
            border-color: rgba(251, 191, 36, 0.75);
            box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.18);
        }

        .status.status-success {
            color: #dcfce7;
            border-color: rgba(34, 197, 94, 0.8);
            background: rgba(22, 101, 52, 0.35);
            box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.18);
        }

        .map-wrap {
            position: relative;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid rgba(251, 191, 36, 0.35);
            box-shadow: 0 18px 30px rgba(0, 0, 0, 0.3);
        }

        #map {
            width: 100%;
            height: 72vh;
            min-height: 360px;
        }

        .map-tag {
            position: absolute;
            top: 12px;
            left: 12px;
            z-index: 1000;
            background: rgba(7, 19, 11, 0.9);
            border: 1px solid rgba(251, 191, 36, 0.45);
            color: #fde68a;
            border-radius: 999px;
            padding: 8px 12px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .add-business-row {
            margin-top: 14px;
            display: flex;
            justify-content: center;
        }

        .add-business-note {
            margin-top: 12px;
            text-align: center;
            color: #cfd9ed;
            font-size: 0.92rem;
        }

        .add-business-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 0;
            border-radius: 999px;
            background: linear-gradient(95deg, #fbbf24 0%, #f59e0b 100%);
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            padding: 12px 22px;
            box-shadow: 0 8px 18px rgba(251, 191, 36, 0.3);
            transition: all 0.2s ease;
        }

        .add-business-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(251, 191, 36, 0.45);
        }

        @media (max-width: 768px) {
            .page {
                padding: 12px;
            }

            #map {
                height: 64vh;
                min-height: 320px;
            }

            .status {
                width: 100%;
            }
        }
    </style>
</head>

<body
    data-business-name="{{ $business?->business_name ?: (Auth::user()->business_name ?? 'Business Name') }}"
    data-initial-lat="{{ $business?->latitude }}"
    data-initial-lng="{{ $business?->longitude }}"
>
    <main class="page">
        <div class="top">
            <a href="{{ route('admin.main') }}" class="back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
            <p class="status status-loading" id="locationStatus" aria-live="polite">Requesting your location...</p>
        </div>

        <section class="map-wrap" aria-label="Map showing your current location">
            <div class="map-tag"><i class="fas fa-map-pin"></i> Mark the Gym Location</div>
            <div id="map"></div>
        </section>

        <p class="add-business-note">Tap on the map or drag the pin to change your gym location.</p>
        <div class="add-business-row">
            <form id="addBusinessForm" action="{{ route('admin.business.store') }}" method="post">
                @csrf
                <input type="hidden" name="business_name" value="{{ Auth::user()->business_name ?? 'Business Name' }}">
                <input type="hidden" name="latitude" id="businessLatitude">
                <input type="hidden" name="longitude" id="businessLongitude">
                <button type="submit" class="add-business-btn">
                    <i class="fas fa-map-pin"></i>
                    {{ $business ? 'Save Pin Location' : 'Add your business' }}
                </button>
            </form>
        </div>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const statusLabel = document.getElementById('locationStatus');
        const businessName = document.body.dataset.businessName;
        const initialLat = Number(document.body.dataset.initialLat);
        const initialLng = Number(document.body.dataset.initialLng);
        const addBusinessForm = document.getElementById('addBusinessForm');
        const businessLatitude = document.getElementById('businessLatitude');
        const businessLongitude = document.getElementById('businessLongitude');

        function setStatus(message, state) {
            statusLabel.textContent = message;
            statusLabel.classList.remove('status-loading', 'status-success');

            if (state === 'loading') {
                statusLabel.classList.add('status-loading');
            }

            if (state === 'success') {
                statusLabel.classList.add('status-success');
            }
        }

        const map = L.map('map', {
            zoomControl: false
        }).setView([14.5995, 120.9842], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let marker;

        function setMarker(latitude, longitude, shouldCenter) {
            const latLng = [latitude, longitude];

            if (!marker) {
                marker = L.marker(latLng, {
                    draggable: true
                }).addTo(map);

                marker.on('dragend', function () {
                    const draggedLatLng = marker.getLatLng();
                    updateSelectedCoords(draggedLatLng.lat, draggedLatLng.lng);
                    setStatus('Location updated.', 'success');
                });
            } else {
                marker.setLatLng(latLng);
            }

            marker.bindPopup(businessName).openPopup();

            businessLatitude.value = Number(latitude).toFixed(7);
            businessLongitude.value = Number(longitude).toFixed(7);

            if (shouldCenter) {
                map.setView(latLng, 16, {
                    animate: true
                });
            }
        }

        function renderLocation(latitude, longitude) {
            setMarker(latitude, longitude, true);
            setStatus('Location found.', 'success');
        }

        function handleLocationError(error) {
            if (error.code === 1) {
                setStatus('Location access was denied. Please enable location permission.');
                if (Number.isFinite(initialLat) && Number.isFinite(initialLng)) {
                    renderLocation(initialLat, initialLng);
                }
                return;
            }

            setStatus('Unable to get your location. Try again.');
            if (Number.isFinite(initialLat) && Number.isFinite(initialLng)) {
                renderLocation(initialLat, initialLng);
            }
        }

        function locateUser() {
            if (!navigator.geolocation) {
                setStatus('Geolocation is not supported on this device.');
                return;
            }

            setStatus('Requesting your location...', 'loading');
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    renderLocation(position.coords.latitude, position.coords.longitude);
                },
                handleLocationError,
                {
                    enableHighAccuracy: true,
                    timeout: 12000,
                    maximumAge: 0
                }
            );
        }

        map.on('click', function (event) {
            setMarker(event.latlng.lat, event.latlng.lng, false);
            setStatus('Location updated.', 'success');
        });

        addBusinessForm.addEventListener('submit', function (event) {
            if (!businessLatitude.value || !businessLongitude.value) {
                event.preventDefault();
                setStatus('Please wait for location or set the pin first.', 'loading');
            }
        });

        locateUser();
    </script>
</body>

</html>
