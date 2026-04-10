// ../assets/js/map.js

let currentMap; // will hold the Leaflet map instance

function initMap() {
    // Baguio City center coordinates
    const baguioCoords = [16.4023, 120.5960];

    // Create the map and immediately center + zoom to Baguio
    const map = L.map('map').setView(baguioCoords, 14);   // 14 is the sweet spot for Baguio

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    // Optional: small marker in the very center (Session Road area)
    L.marker(baguioCoords)
        .addTo(map)
        .bindPopup('<b>Baguio City</b><br>Emergency Monitoring Center')
        .openPopup();

    // OPTIONAL – keep users inside Baguio + nearby areas only
    map.setMaxBounds([
        [16.30, 120.50],   // Southwest
        [16.50, 120.70]    // Northeast
    ]);

    return map;
}

let markersLayer = L.layerGroup(); // to manage emergency markers

function loadEmergencies() {
    fetch('get_emergencies.php') // make sure this file exists and returns JSON
        .then(response => response.json())
        .then(data => {
            // Clear old markers
            markersLayer.clearLayers();

            data.forEach(emergency => {
                if (emergency.lat && emergency.lng) {
                    const marker = L.marker([emergency.lat, emergency.lng])
                        .bindPopup(`
                            <b>${emergency.type}</b><br>
                            ${emergency.description}<br>
                            <small>Reported: ${emergency.created_at}</small><br>
                            <a href="?id=${emergency.id}&status=resolved" 
                               style="color:green; font-weight:bold;">Mark Resolved</a>
                        `);

                    // Color coding by status or type (optional enhancement)
                    if (emergency.status === 'pending') {
                        marker.setIcon(redIcon());
                    } else if (emergency.status === 'responding') {
                        marker.setIcon(orangeIcon());
                    } else {
                        marker.setIcon(greenIcon());
                    }

                    marker.addTo(markersLayer);
                }
            });

            // Add all emergency markers to map
            markersLayer.addTo(currentMap);
        })
        .catch(err => console.error("Error loading emergencies:", err));
}

// Optional: Custom icons for better visuals
function redIcon() {
    return L.divIcon({
        className: 'custom-div-icon',
        html: "<div style='background-color:#CC0000; width:20px; height:20px; border-radius:50%; border:3px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.7);'></div>",
        iconSize: [20, 20],
        iconAnchor: [10, 10]
    });
}

function orangeIcon() {
    return L.divIcon({
        className: 'custom-div-icon',
        html: "<div style='background-color:#FF8800; width:20px; height:20px; border-radius:50%; border:3px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.7);'></div>",
        iconSize: [20, 20],
        iconAnchor: [10, 10]
    });
}

function greenIcon() {
    return L.divIcon({
        className: 'custom-div-icon',
        html: "<div style='background-color:#00AA00; width:20px; height:20px; border-radius:50%; border:3px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.7);'></div>",
        iconSize: [20, 20],
        iconAnchor: [10, 10]
    });
}