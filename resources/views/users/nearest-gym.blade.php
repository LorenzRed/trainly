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

        .search-row {
            display: flex;
            gap: 8px;
            width: 100%;
            margin-bottom: 12px;
        }

        .search-label {
            display: block;
            color: #fde68a;
            font-size: 0.9rem;
            font-weight: 600;
            margin: 12px 0 8px;
        }

        .search-input {
            flex: 1;
            border: 1px solid rgba(251, 191, 36, 0.38);
            background: rgba(15, 27, 19, 0.9);
            color: #f0f3f8;
            border-radius: 12px;
            padding: 12px 14px;
            outline: none;
            font-size: 0.95rem;
        }

        .search-input:focus {
            border-color: #fbbf24;
            box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.2);
        }

        .search-btn {
            border: 0;
            border-radius: 12px;
            background: linear-gradient(95deg, #fbbf24 0%, #f59e0b 100%);
            color: #fff;
            font-weight: 700;
            padding: 0 16px;
            cursor: pointer;
            min-height: 44px;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(251, 191, 36, 0.35);
        }

        .locate-nearby-btn {
            position: fixed;
            right: 24px;
            bottom: 24px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: 0;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 20px rgba(251, 191, 36, 0.4);
            transition: all 0.3s ease;
            z-index: 900;
            font-size: 24px;
        }

        .locate-nearby-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 28px rgba(251, 191, 36, 0.5);
        }

        .locate-nearby-btn:active {
            transform: scale(0.95);
        }

        .locate-nearby-btn[data-loading="true"] {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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

        .status.status-route {
            color: #dbeafe;
            border-color: rgba(59, 130, 246, 0.85);
            background: rgba(30, 64, 175, 0.35);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
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
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
            transition: opacity 0.25s ease, transform 0.25s ease;
        }

        .map-tag.hide {
            opacity: 0;
            transform: translateY(-6px);
            pointer-events: none;
        }

        .gym-name-label {
            background: rgba(7, 19, 11, 0.92);
            border: 1px solid rgba(251, 191, 36, 0.55);
            color: #fde68a;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 1;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.28);
        }

        .route-line-animated {
            stroke-dasharray: 10 8;
            animation: routeDashMove 1.1s linear infinite;
        }

        @keyframes routeDashMove {
            to {
                stroke-dashoffset: -36;
            }
        }

        @media (max-width: 768px) {
            .page {
                padding: 12px;
            }

            .search-row {
                flex-direction: column;
            }

            .search-btn {
                width: 100%;
            }

            #map {
                height: 64vh;
                min-height: 320px;
            }

            .status {
                width: 100%;
            }

            .locate-nearby-btn {
                right: 12px;
                bottom: 12px;
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <main class="page">
        <div class="top">
            <a href="{{ route('users.main') }}" class="back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
            <p class="status status-loading" id="locationStatus" aria-live="polite">Requesting your location...</p>
        </div>

        <section class="map-wrap" aria-label="Map showing your current location">
            <div class="map-tag"><i class="fas fa-expand"></i> Scroll Around and find other Gym</div>
            <div id="map"></div>
        </section>

        <label for="gymSearchInput" class="search-label">Search business name</label>
        <div class="search-row">
            <input id="gymSearchInput" class="search-input" type="text" placeholder="Search business name..." aria-label="Search business name">
            <button type="button" id="gymSearchBtn" class="search-btn">Search</button>
        </div>

        <button type="button" id="locateNearbyBtn" class="locate-nearby-btn" title="Find the nearest gym to your location" aria-label="Locate nearby gym">
            <i class="fas fa-compass"></i>
        </button>
    </main>

    <script type="application/json" id="adminGymLocations">@json($gymLocations)</script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const statusLabel = document.getElementById('locationStatus');
        const gymSearchInput = document.getElementById('gymSearchInput');
        const gymSearchBtn = document.getElementById('gymSearchBtn');
        const locateNearbyBtn = document.getElementById('locateNearbyBtn');
        const mapTag = document.querySelector('.map-tag');
        const adminGymLocations = JSON.parse(document.getElementById('adminGymLocations').textContent || '[]');
        const gymViewRouteTemplate = '{{ route('users.gym.view', ['business' => '__BUSINESS_ID__']) }}';

        function runMapTagTimer() {
            if (!mapTag) {
                return;
            }

            setTimeout(function () {
                mapTag.classList.add('hide');
            }, 5000);
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function gymPopupContent(gym, distanceText) {
            const businessName = gym.business_name || 'Gym';
            const businessId = Number(gym.id);

            if (!Number.isFinite(businessId)) {
                return '<strong>' + escapeHtml(businessName) + '</strong>';
            }

            const gymUrl = gymViewRouteTemplate.replace('__BUSINESS_ID__', String(businessId));
            const distanceRow = distanceText
                ? '<div style="margin-top:6px;color:#60a5fa;font-size:0.78rem;font-weight:700;">Distance: ' + escapeHtml(distanceText) + '</div>'
                : '';

            return '<div style="text-align:center;"><strong>' + escapeHtml(businessName) + '</strong>' + distanceRow + '<a href="' + gymUrl + '" style="display:inline-block;margin-top:8px;padding:6px 10px;border-radius:999px;background:#fbbf24;color:#07130b;text-decoration:none;font-weight:700;font-size:0.8rem;">View Gym</a></div>';
        }

        function formatDistance(meters) {
            if (!Number.isFinite(meters) || meters < 0) {
                return 'N/A';
            }

            if (meters < 1000) {
                return Math.round(meters) + ' m';
            }

            return (meters / 1000).toFixed(2) + ' km';
        }

        function setStatus(message, state) {
            statusLabel.textContent = message;
            statusLabel.classList.remove('status-loading', 'status-success', 'status-route');

            if (state === 'loading') {
                statusLabel.classList.add('status-loading');
            }

            if (state === 'success') {
                statusLabel.classList.add('status-success');
            }

            if (state === 'route') {
                statusLabel.classList.add('status-route');
            }
        }

        const map = L.map('map', {
            zoomControl: false
        }).setView([14.5995, 120.9842], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let userMarker;
        let userLatLng = null;
        let routeLine = null;
        const adminGymsLayer = L.layerGroup().addTo(map);
        const gymSearchResultsLayer = L.layerGroup().addTo(map);
        const gymIcon = L.divIcon({
            className: 'gym-marker-icon',
            html: '<i class="fas fa-dumbbell" style="color:#000000; font-size:24px;"></i>',
            iconSize: [50, 50],
            iconAnchor: [15, 15]
        });

        async function drawRouteToGym(gym, gymLat, gymLng, gymMarker) {
            if (!userLatLng) {
                setStatus('Enable your location first to calculate route distance.');
                gymMarker.setPopupContent(gymPopupContent(gym)).openPopup();
                return;
            }

            setStatus('Calculating road route...', 'loading');

            const fromLon = userLatLng[1];
            const fromLat = userLatLng[0];
            const toLon = gymLng;
            const toLat = gymLat;
            const osrmUrl = 'https://router.project-osrm.org/route/v1/driving/'
                + fromLon + ',' + fromLat + ';' + toLon + ',' + toLat
                + '?overview=full&geometries=geojson';

            try {
                const response = await fetch(osrmUrl);
                if (!response.ok) {
                    throw new Error('Routing service unavailable');
                }

                const data = await response.json();
                if (!data.routes || !data.routes.length) {
                    throw new Error('No route found');
                }

                const route = data.routes[0];
                const latLngs = route.geometry.coordinates.map(function (coord) {
                    return [coord[1], coord[0]];
                });

                if (routeLine) {
                    map.removeLayer(routeLine);
                }

                routeLine = L.polyline(latLngs, {
                    color: '#3b82f6',
                    weight: 5,
                    opacity: 0.95,
                    className: 'route-line-animated'
                }).addTo(map);

                const distanceText = formatDistance(Number(route.distance));
                gymMarker.setPopupContent(gymPopupContent(gym, distanceText)).openPopup();
                map.fitBounds(routeLine.getBounds(), {
                    padding: [36, 36]
                });
                setStatus('Distance to ' + (gym.business_name || 'Gym') + ': ' + distanceText, 'success');
            } catch (error) {
                const straightDistance = formatDistance(map.distance(userLatLng, [gymLat, gymLng]));
                gymMarker.setPopupContent(gymPopupContent(gym, straightDistance)).openPopup();
                setStatus('Could not draw road route. Showing estimated distance: ' + straightDistance);
            }
        }

        function renderAdminGyms() {
            adminGymsLayer.clearLayers();

            adminGymLocations.forEach(function (gym) {
                const lat = Number(gym.latitude);
                const lng = Number(gym.longitude);

                if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                    return;
                }

                const marker = L.marker([lat, lng], { icon: gymIcon }).addTo(adminGymsLayer);
                marker.bindPopup(gymPopupContent(gym), {
                    offset: L.point(0, -14)
                });
                marker.bindTooltip(gym.business_name || 'Gym', {
                    permanent: true,
                    direction: 'bottom',
                    offset: [0, 6],
                    className: 'gym-name-label'
                });
                marker.on('click', function () {
                    drawRouteToGym(gym, lat, lng, marker);
                });
            });
        }

        function renderLocation(latitude, longitude) {
            const latLng = [latitude, longitude];

            userLatLng = latLng;

            if (!userMarker) {
                userMarker = L.marker(latLng).addTo(map).bindPopup('You are here').openPopup();
            } else {
                userMarker.setLatLng(latLng);
            }

            map.setView(latLng, 16, {
                animate: true
            });
            setStatus('Location found.', 'success');
        }

        function handleLocationError(error) {
            if (error.code === 1) {
                setStatus('Location access was denied. Please enable location permission.');
                return;
            }

            setStatus('Unable to get your location. Try again.');
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

        function searchGyms() {
            const rawQuery = gymSearchInput.value.trim();
            gymSearchResultsLayer.clearLayers();

            if (!rawQuery) {
                setStatus('Type a business name to search.');
                return;
            }

            const query = rawQuery.toLowerCase();
            const matches = adminGymLocations.filter(function (gym) {
                const name = String(gym.business_name || '').toLowerCase();
                return name.includes(query);
            });

            if (!matches.length) {
                setStatus('No business found for that search.');
                return;
            }

            const bounds = [];

            matches.forEach(function (gym) {
                const lat = Number(gym.latitude);
                const lng = Number(gym.longitude);

                if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                    return;
                }

                bounds.push([lat, lng]);

                const marker = L.marker([lat, lng], { icon: gymIcon }).addTo(gymSearchResultsLayer);
                const businessName = gym.business_name || 'Gym';
                marker
                    .bindPopup(gymPopupContent(gym), {
                        offset: L.point(0, -14)
                    })
                    .bindTooltip(businessName, {
                        permanent: true,
                        direction: 'bottom',
                        offset: [0, 6],
                        className: 'gym-name-label'
                    })
                    .openPopup();

                marker.on('click', function () {
                    drawRouteToGym(gym, lat, lng, marker);
                });
            });

            if (bounds.length) {
                map.fitBounds(bounds, {
                    padding: [30, 30]
                });
            }

            setStatus(matches.length + ' business result(s) found.', 'success');
        }

        function findNearestGym() {
            if (!userLatLng) {
                setStatus('Enable your location first to find nearby gyms.');
                return;
            }

            if (!adminGymLocations || adminGymLocations.length === 0) {
                setStatus('No gyms found in the system.');
                return;
            }

            locateNearbyBtn.setAttribute('data-loading', 'true');
            setStatus('Finding nearest gym...', 'loading');

            let nearestGym = null;
            let nearestDistance = Infinity;

            adminGymLocations.forEach(function (gym) {
                const lat = Number(gym.latitude);
                const lng = Number(gym.longitude);

                if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                    return;
                }

                const distance = map.distance(userLatLng, [lat, lng]);
                if (distance < nearestDistance) {
                    nearestDistance = distance;
                    nearestGym = { gym: gym, lat: lat, lng: lng, distance: distance };
                }
            });

            if (!nearestGym) {
                locateNearbyBtn.setAttribute('data-loading', 'false');
                setStatus('Could not find any nearby gyms.');
                return;
            }

            // Clear search results
            gymSearchResultsLayer.clearLayers();
            gymSearchInput.value = '';

            // Find and click the nearest gym marker
            const nearest = nearestGym;
            adminGymsLayer.eachLayer(function (layer) {
                if (layer instanceof L.Marker) {
                    const markerLatLng = layer.getLatLng();
                    if (markerLatLng.lat === nearest.lat && markerLatLng.lng === nearest.lng) {
                        setTimeout(function () {
                            layer.fire('click');
                            locateNearbyBtn.setAttribute('data-loading', 'false');
                        }, 200);
                    }
                }
            });
        }

        gymSearchBtn.addEventListener('click', searchGyms);
        gymSearchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                searchGyms();
            }
        });

        locateNearbyBtn.addEventListener('click', findNearestGym);

        renderAdminGyms();
        locateUser();
        runMapTagTimer();
    </script>
</body>

</html>
