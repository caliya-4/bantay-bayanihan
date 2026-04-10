<?php
session_start();
require '../db_connect.php';
include '../includes/header.php';

// Auth guard — matches your new structure
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'responder') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evacuation Routes & Safe Zones</title>
    <!-- Leaflet CSS (fixed trailing space) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responder.css">
    <style>
        #map {
            height: 55vh;
            border: 6px solid #1e3a8a;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(30,58,138,0.3);
            width: 100%;
        }
        .control-panel {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        .site-info-card {
            background: #f8fafc;
            border-left: 4px solid #3b82f6;
            padding: 12px;
            margin-bottom: 12px;
            border-radius: 8px;
        }
        .distance-badge {
            font-size: 0.9rem;
            padding: 4px 10px;
            background: #dbeafe;
            color: #1d4ed8;
            border-radius: 20px;
            font-weight: bold;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="row">
            <!-- Map + Controls (Main) -->
            <div class="col-md-9">
                <div class="control-panel">
                    <h4>🗺️ Evacuation Centers Map</h4>
                    <p class="text-muted mb-3">Find the nearest safe evacuation center in Baguio City</p>
                    
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-primary" id="findNearestBtn" onclick="findNearestSites()">
                            📍 Find Nearest Centers
                        </button>
                        <button class="btn btn-success" onclick="goToMyLocation()">
                            🎯 Go to My Location
                        </button>
                        <button class="btn btn-info" onclick="showAllSites()">
                            👁️ Show All Sites
                        </button>
                        <button class="btn btn-secondary" onclick="clearRoutes()">
                            🗑️ Clear Routes
                        </button>
                    </div>
                </div>

                <div id="map"></div>
            </div>

            <!-- Nearest Sites Sidebar -->
            <div class="col-md-3">
                <div class="control-panel">
                    <h5>ℹ️ Legend</h5>
                    <div class="legend-item">
                        <span style="font-size:24px;">🏫</span>
                        <span><strong>School</strong></span>
                    </div>
                    <div class="legend-item">
                        <span style="font-size:24px;">🏛️</span>
                        <span><strong>Barangay Hall</strong></span>
                    </div>
                    <div class="legend-item">
                        <span style="font-size:24px;">🏀</span>
                        <span><strong>Court/Gym</strong></span>
                    </div>
                    <div class="legend-item">
                        <span style="font-size:24px;">📍</span>
                        <span><strong>You</strong></span>
                    </div>
                </div>

                <div class="control-panel mt-3">
                    <h5>📍 Nearest Centers</h5>
                    <p class="small text-muted">Click "Find Nearest Centers" to see results</p>
                    <div id="nearestSitesList">
                        <div class="text-center text-muted py-3">
                            <p>Use your location to find nearest evacuation centers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS (fixed trailing space) -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize map centered on Baguio City
        const map = L.map('map').setView([16.4023, 120.5960], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        // State
        let markers = [];
        let userMarker = null;
        let userLocation = null;
        let routeLines = [];

        // Icons by type (match your DB 'type' field)
        const siteIcons = {
            school: '🏫',
            barangay_hall: '🏛️',
            court: '🏀',
            covered_court: '🏀',
            gym: '🏋️',
            other: '✅'
        };

        // 🆕 ADAPT: Fetch from your NEW API endpoint
        async function loadAllSites() {
            try {
                // 🔁 IMPORTANT: Update this path to match your NEW project's API structure
                const response = await fetch('../api/evacuation/get-sites.php?t=' + Date.now(), {
                    cache: 'no-store'
                });
                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();
                
                if (data.success && Array.isArray(data.data)) {
                    markers = []; // reset
                    data.data.forEach(site => addSiteMarker(site));
                    console.log(`Loaded ${data.data.length} evacuation sites`);
                }
            } catch (error) {
                console.error('Error loading sites:', error);
                alert('Failed to load evacuation sites. Please try again.');
            }
        }

                // Load road closures for residents
        async function loadRoadClosures() {
            try {
                const response = await fetch('../api/admin/get-closures.php');
                const data = await response.json();
                if (data.success) {
                    data.data.forEach(closure => {
                        L.polyline([
                            [closure.start_lat, closure.start_lng],
                            [closure.end_lat, closure.end_lng]
                        ], {
                            color: '#e11d48',
                            weight: 5,
                            dashArray: '12, 8',
                            opacity: 0.9
                        }).addTo(map)
                        .bindPopup(`<b>⚠️ Road Closed</b><br>${closure.description}`);
                    });
                }
            } catch (err) {
                console.warn('Could not load road closures:', err);
            }
        }

        // Call after map loads
        loadRoadClosures();

        function addSiteMarker(site) {
            const icon = siteIcons[site.type] || '📍';
            const customIcon = L.divIcon({
                html: `<div style="font-size: 30px;">${icon}</div>`,
                className: 'custom-marker',
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            });

            const marker = L.marker([site.latitude, site.longitude], { icon: customIcon }).addTo(map);
            
            const facilities = Array.isArray(site.facilities) 
                ? site.facilities.join(', ') 
                : (site.facilities || 'Not specified');

            marker.bindPopup(`
                <div style="min-width:200px;">
                    <h6><strong>${site.name}</strong></h6>
                    <p class="mb-1"><strong>Type:</strong> ${site.type.replace('_', ' ')}</p>
                    <p class="mb-1"><strong>Barangay:</strong> ${site.barangay}</p>
                    
                    <!-- <p class="mb-1"><strong>Capacity:</strong> ${site.capacity || 'N/A'} people</p> -->
                    
                    <p class="mb-1"><strong>Facilities:</strong> ${facilities}</p>
                    <button class="btn btn-sm btn-primary mt-2" onclick="getDirections(${site.latitude}, ${site.longitude}, '${site.name.replace(/'/g, "\\'")}')">
                        Get Directions
                    </button>
                </div>
            `);
            markers.push({ marker, site });
        }

        async function findNearestSites() {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser');
                return;
            }

            const btn = document.getElementById('findNearestBtn');
            btn.disabled = true;
            btn.innerHTML = '🔄 Finding...';

            navigator.geolocation.getCurrentPosition(async function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                userLocation = { lat, lng };

                // Remove old user marker
                if (userMarker) map.removeLayer(userMarker);

                // Add new user marker
                const userIcon = L.divIcon({
                    html: '<div style="font-size: 36px;">📍</div>',
                    className: 'user-marker',
                    iconSize: [36, 36],
                    iconAnchor: [18, 36]
                });
                userMarker = L.marker([lat, lng], { icon: userIcon })
                    .addTo(map)
                    .bindPopup('<strong>You are here</strong>')
                    .openPopup();

                map.setView([lat, lng], 15);

                try {
                    // 🔁 IMPORTANT: Update this path too!
                    const response = await fetch(`../api/evacuation/nearest.php?lat=${lat}&lng=${lng}&limit=5&t=${Date.now()}`, {
                        cache: 'no-store'
                    });
                    const data = await response.json();
                    if (data.success) {
                        displayNearestSites(data.data);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Failed to find nearest sites');
                }

                btn.disabled = false;
                btn.innerHTML = '📍 Find Nearest Centers';
            }, function(error) {
                alert('Unable to get your location. Please enable location services.');
                btn.disabled = false;
                btn.innerHTML = '📍 Find Nearest Centers';
            });
        }

        function displayNearestSites(sites) {
            const list = document.getElementById('nearestSitesList');
            list.innerHTML = '';

            if (sites.length === 0) {
                list.innerHTML = '<p class="text-muted text-center py-3">No nearby centers found</p>';
                return;
            }

            sites.forEach((site, index) => {
                const siteCard = `
                    <div class="site-info-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0">${index + 1}. ${site.name}</h6>
                            <span class="distance-badge">${site.distance_km} km</span>
                        </div>
                        <p class="small mb-1">${site.barangay}</p>
                        <p class="small mb-2">${site.type.replace('_', ' ')}</p>
                        <button class="btn btn-sm btn-success w-100" onclick="getDirections(${site.latitude}, ${site.longitude}, '${site.name.replace(/'/g, "\\'")}')">
                            🧭 Get Directions
                        </button>
                    </div>
                `;
                list.innerHTML += siteCard;
            });
        }

        function getDirections(toLat, toLng, siteName) {
            if (!userLocation) {
                alert('Please find your location first by clicking "Find Nearest Centers"');
                return;
            }

            clearRoutes();

            const routeLine = L.polyline([
                [userLocation.lat, userLocation.lng],
                [toLat, toLng]
            ], {
                color: '#3b82f6',
                weight: 5,
                opacity: 0.8
            }).addTo(map);

            routeLines.push(routeLine);
            map.fitBounds([
                [userLocation.lat, userLocation.lng],
                [toLat, toLng]
            ]);

            const distance = map.distance([userLocation.lat, userLocation.lng], [toLat, toLng]);
            const distanceKm = (distance / 1000).toFixed(2);
            const estimatedTime = Math.ceil(distanceKm * 15); // walking

            alert(`Route to ${siteName}\n\nDistance: ${distanceKm} km\nEstimated walking time: ~${estimatedTime} minutes`);
        }

        function goToMyLocation() {
            if (userLocation) {
                map.setView([userLocation.lat, userLocation.lng], 15);
                if (userMarker) userMarker.openPopup();
            } else {
                alert('Please click "Find Nearest Centers" first to share your location.');
            }
        }

        function showAllSites() {
            if (markers.length > 0) {
                const group = L.featureGroup(markers.map(m => m.marker));
                map.fitBounds(group.getBounds().pad(0.1));
            }
        }

        function clearRoutes() {
            routeLines.forEach(line => map.removeLayer(line));
            routeLines = [];
        }

        // 🔁 Load sites on startup
        loadAllSites();
    </script>
    <script src="../assets/js/chatbot.js"></script>
</body>
</html>