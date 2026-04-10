<?php
session_start();
require '../db_connect.php';
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'responder'])) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/design-system.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
    <style>
        #map { 
            height: 85vh; 
            border: 2px solid #1e40af;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .toolbar-panel {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }

    </style>
</head>
<body>
    <div class="container-fluid mt-3">
        <div class="toolbar-panel">
            <h4>🛡️ Admin Operations Dashboard</h4>
            <p>Draw features on the map, then click "Save" to submit.</p>
            <div id="draw-controls"></div>
        </div>
        <div class="mt-3">
            <h6>👁️ Layer Controls</h6>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="toggleCenters" checked onchange="toggleLayer(evacCentersLayer, this.checked)">
                <label class="form-check-label" for="toggleCenters">Evacuation Centers</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="toggleClosures" checked onchange="toggleLayer(roadClosuresLayer, this.checked)">
                <label class="form-check-label" for="toggleClosures">Road Closures</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="toggleDraw" checked onchange="toggleLayer(drawnItems, this.checked)">
                <label class="form-check-label" for="toggleDraw">New Drafts</label>
            </div>
        </div>
        <div id="map"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
    <script>
        const map = L.map('map').setView([16.4023, 120.5960], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Feature groups
        const evacCentersLayer = L.layerGroup().addTo(map);
        const roadClosuresLayer = L.layerGroup().addTo(map);

        // Load existing data
        loadEvacuationCenters();
        loadRoadClosures();

        // --- Drawing Control ---
        const drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        const drawControl = new L.Control.Draw({
            edit: { featureGroup: drawnItems, remove: true },
            draw: {
                polyline: {
                    shapeOptions: { color: '#dc2626', weight: 6 }
                },
                polygon: false,
                circle: false,
                circlemarker: false,
                marker: {
                    icon: L.divIcon({
                        html: '<div style="background:#0066FF;width:24px;height:24px;border-radius:50%;border:3px solid white;box-shadow:0 0 10px rgba(0,0,0,0.5);"></div>',
                        iconSize: [24, 24],
                        iconAnchor: [12, 24]
                    })
                },
                rectangle: false
            }
        });
        map.addControl(drawControl);

        // Handle new drawings
        map.on(L.Draw.Event.CREATED, function(e) {
            const layer = e.layer;
            drawnItems.addLayer(layer);

            // Prompt for feature type
            const type = prompt("Is this an evacuation center (type 'center') or road closure (type 'road')?", "center");
            if (!type || !['center', 'road'].includes(type)) {
                drawnItems.removeLayer(layer);
                return;
            }

            // Save to DB
            saveFeature(layer, type);
        });

        // --- Save to Database ---
        function saveFeature(layer, type) {
            let data = { type, reported_by: <?= json_encode($_SESSION['user_id']) ?> };

            if (type === 'center') {
                const latLng = layer.getLatLng();
                data.lat = latLng.lat;
                data.lng = latLng.lng;
                data.name = prompt("Name of center:", "New Evacuation Center");
                data.type = prompt("Type (school, barangay_hall, safe_zone):", "safe_zone");
                data.barangay = prompt("Barangay:", "Baguio City");
                
                fetch('../api/admin/save-center.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                }).then(r => r.json()).then(res => {
                    if (res.success) {
                        alert('✅ Evacuation center saved!');
                        loadEvacuationCenters(); // refresh
                    } else {
                        alert('❌ Failed: ' + res.error);
                    }
                });
            } 
            else if (type === 'road') {
                const latLngs = layer.getLatLngs();
                if (latLngs.length < 2) {
                    alert('Road must have at least 2 points');
                    return;
                }
                data.start_lat = latLngs[0].lat;
                data.start_lng = latLngs[0].lng;
                data.end_lat = latLngs[latLngs.length - 1].lat;
                data.end_lng = latLngs[latLngs.length - 1].lng;
                data.description = prompt("Reason for closure:", "Landslide");

                fetch('../api/admin/save-closure.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                }).then(r => r.json()).then(res => {
                    if (res.success) {
                        alert('✅ Road closure saved!');
                        loadRoadClosures(); // refresh
                    } else {
                        alert('❌ Failed: ' + res.error);
                    }
                });
            }
        }

        // Context menu for features
        function attachContextMenu(layer, type, id) {
            layer.on('contextmenu', function(e) {
                const action = confirm(`Click OK to DELETE this ${type}.\nCancel to edit.`);
                if (action) {
                    deleteFeature(type, id);
                } else {
                    editFeature(layer, type, id);
                }
            });
        }

        // --- Load Existing Data ---
        function loadEvacuationCenters() {
        fetch('../api/evacuation/get-sites.php')
            .then(r => r.json())
            .then(data => {
                evacCentersLayer.clearLayers();
                if (data.success) {
                    data.data.forEach(site => {
                        const marker = L.marker([site.latitude, site.longitude], {
                            title: site.name
                        }).addTo(evacCentersLayer)
                        .bindPopup(`<b>${site.name}</b><br>${site.barangay}<br>Type: ${site.type}`);
                        
                        // Attach context menu (right-click)
                        attachContextMenu(marker, 'evacuation center', site.id);
                    });
                }
            });
        }

        function loadRoadClosures() {
        fetch('../api/admin/get-closures.php')
            .then(r => r.json())
            .then(data => {
                roadClosuresLayer.clearLayers();
                if (data.success) {
                    data.data.forEach(closure => {
                        const polyline = L.polyline([
                            [closure.start_lat, closure.start_lng],
                            [closure.end_lat, closure.end_lng]
                        ], {
                            color: '#dc2626',
                            weight: 6,
                            dashArray: '10, 5'
                        }).addTo(roadClosuresLayer)
                        .bindPopup(`<b>Road Closed</b><br>${closure.description}`);
                        
                        attachContextMenu(polyline, 'road closure', closure.id);
                    });
                }
            });
        }
        function deleteFeature(type, id) {
        if (!confirm(`Are you sure you want to permanently delete this ${type}?`)) return;

        const endpoint = type.includes('center') 
            ? '../api/admin/delete-center.php' 
            : '../api/admin/delete-closure.php';

        fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                alert(`✅ ${type} deleted.`);
                loadEvacuationCenters();
                loadRoadClosures();
            } else {
                alert('❌ Delete failed: ' + res.error);
            }
        });
    }

        function editFeature(layer, type, id) {
            if (type.includes('center')) {
                const newName = prompt("Edit name:", layer.getPopup().getContent().split('<b>')[1]?.split('</b>')[0]);
                if (!newName) return;

                fetch('../api/admin/edit-center.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, name: newName })
                }).then(r => r.json()).then(res => {
                    if (res.success) {
                        alert('✅ Updated!');
                        loadEvacuationCenters();
                    }
                });
            } else {
                const newDesc = prompt("Edit closure reason:", 
                    layer.getPopup().getContent().split('<br>')[1]
                );
                if (newDesc) {
                    fetch('../api/admin/edit-closure.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id, description: newDesc })
                    }).then(r => r.json()).then(res => {
                        if (res.success) {
                            alert('✅ Updated!');
                            loadRoadClosures();
                        }
                    });
                }
            }
        }
        function toggleLayer(layerGroup, show) {
        if (show) {
            map.addLayer(layerGroup);
        } else {
            map.removeLayer(layerGroup);
        }
    }
    </script>
</body>
</html>