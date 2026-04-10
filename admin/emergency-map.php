<?php 
session_start(); 
require '../db_connect.php';

// Ensure user is authenticated before sending any output
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'],['admin','responder'])) {
    header("Location: ../login.php");
    exit;
}

// Update emergency status (handle redirects before including header/output)
if(isset($_GET['status']) && isset($_GET['id'])){
    $pdo->prepare("UPDATE emergencies SET status=? WHERE id=?")
        ->execute([$_GET['status'], $_GET['id']]);
    header("Location: emergency-map.php");
    exit;
}

include '../includes/header.php';

// Get all barangays for dropdown (complete list for Baguio City)
$barangays = [
    "A. Bonifacio-Caguioa-Rimando (ABCR)",
    "Abanao-Zandueta-Kayong-Chugum-Otek (AZKCO)",
    "Alfonso Tabora",
    "Ambiong",
    "Andres Bonifacio (Lower Bokawkan)",
    "Apugan-Loakan",
    "Asin Road",
    "Atok Trail",
    "Aurora Hill Proper (Malvar-Sgt. Floresca)",
    "Aurora Hill, North Central",
    "Aurora Hill, South Central",
    "Bagong Lipunan (Market Area)",
    "Bakakeng Central",
    "Bakakeng North",
    "Bal-Marcoville (Marcoville)",
    "Balsigan",
    "Bayan Park East",
    "Bayan Park Village",
    "Bayan Park West (Bayan Park, Leonila Hill)",
    "BGH Compound",
    "Brookside",
    "Brookspoint",
    "Cabinet Hill-Teacher's Camp",
    "Camdas Subdivision",
    "Camp 7",
    "Camp 8",
    "Camp Allen",
    "Campo Filipino",
    "City Camp Central",
    "City Camp Proper",
    "Country Club Village",
    "Cresencia Village",
    "Dagsian, Lower",
    "Dagsian, Upper",
    "Dizon Subdivision",
    "Dominican Hill-Mirador",
    "Dontogan",
    "DPS Compound",
    "Engineers' Hill",
    "Fairview Village",
    "Ferdinand (Happy Homes-Campo Sioco)",
    "Fort del Pilar",
    "Gabriela Silang",
    "General Emilio F. Aguinaldo (Quirino‑Magsaysay, Lower)",
    "General Luna, Upper",
    "General Luna, Lower",
    "Gibraltar",
    "Greenwater Village",
    "Guisad Central",
    "Guisad Sorong",
    "Happy Hollow",
    "Happy Homes (Happy Homes-Lucban)",
    "Harrison-Claudio Carantes",
    "Hillside",
    "Holy Ghost Extension",
    "Holy Ghost Proper",
    "Honeymoon (Honeymoon-Holy Ghost)",
    "Imelda R. Marcos (La Salle)",
    "Imelda Village",
    "Irisan",
    "Kabayanihan",
    "Kagitingan",
    "Kayang Extension",
    "Kayang-Hilltop",
    "Kias",
    "Legarda-Burnham-Kisad",
    "Liwanag-Loakan",
    "Loakan Proper",
    "Lopez Jaena",
    "Lourdes Subdivision Extension",
    "Lourdes Subdivision, Lower",
    "Lourdes Subdivision, Proper",
    "Lualhati",
    "Lucnab",
    "Magsaysay Private Road",
    "Magsaysay, Lower",
    "Magsaysay, Upper",
    "Malcolm Square-Perfecto (Jose Abad Santos)",
    "Manuel A. Roxas",
    "Market Subdivision, Upper",
    "Middle Quezon Hill Subdivision (Quezon Hill Middle)",
    "Military Cut-off",
    "Mines View Park",
    "Modern Site, East",
    "Modern Site, West",
    "MRR-Queen of Peace",
    "New Lucban",
    "Outlook Drive",
    "Pacdal",
    "Padre Burgos",
    "Padre Zamora",
    "Palma-Urbano (Cariño-Palma)",
    "Phil-Am",
    "Pinget",
    "Pinsao Pilot Project",
    "Pinsao Proper",
    "Poliwes",
    "Pucsusan",
    "Quezon Hill Proper",
    "Quezon Hill, Upper",
    "Quirino Hill, East",
    "Quirino Hill, Lower",
    "Quirino Hill, Middle",
    "Quirino Hill, West",
    "Quirino-Magsaysay, Upper (Upper QM)",
    "Rizal Monument Area",
    "Rock Quarry, Lower",
    "Rock Quarry, Middle",
    "Rock Quarry, Upper",
    "Saint Joseph Village",
    "Salud Mitra",
    "San Antonio Village",
    "San Luis Village",
    "San Roque Village",
    "San Vicente",
    "Sanitary Camp, North",
    "Sanitary Camp, South",
    "Santa Escolastica",
    "Santo Rosario",
    "Santo Tomas Proper",
    "Santo Tomas School Area",
    "Scout Barrio",
    "Session Road Area",
    "Slaughter House Area (Santo Niño Slaughter)",
    "SLU-SVP Housing Village",
    "South Drive",
    "Teodora Alonzo",
    "Trancoville",
    "Victoria Village"
];
sort($barangays);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Emergency Map | Bantay Bayanihan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
        <link rel="stylesheet" href="../assets/css/design-system.css">
        <link rel="stylesheet" href="../assets/css/responder.css">
    <style>
        :root {
            --pink: #ff0065;
            --purple: #6161ff;
            --navy: #00167a;
            --gradient-primary: linear-gradient(135deg, #6161ff, #ff0065);
            --gradient-secondary: linear-gradient(135deg, #00167a, #6161ff);
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f0f4ff 0%, #fef3f8 100%);
        }

        .main-content {
            margin-left: 280px;
            padding: 40px 35px;
            min-height: calc(100vh - 75px);
            transition: margin-left 0.3s ease;
        }

        /* PAGE HEADER */
        .page-header {
            background: var(--gradient-primary);
            padding: 50px 40px;
            margin: -40px -35px 40px;
            border-radius: 0 0 32px 32px;
            box-shadow: 0 15px 50px rgba(97, 97, 255, 0.25);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-25px) rotate(3deg); }
        }

        .page-header-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .page-header h1 {
            color: white;
            font-size: clamp(28px, 5vw, 42px);
            font-weight: 900;
            margin: 0 0 12px 0;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .page-header p {
            color: rgba(255, 255, 255, 0.95);
            font-size: clamp(14px, 2.5vw, 18px);
            margin: 0;
            font-weight: 600;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        /* MAP CONTAINER */
        .map-container {
            background: white;
            padding: 30px;
            border-radius: 24px;
            box-shadow: 0 15px 50px rgba(97, 97, 255, 0.15);
            border: 2px solid rgba(97, 97, 255, 0.1);
        }

        /* CONTROL PANEL */
        .control-panel {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9ff, #fef3f8);
            border-radius: 16px;
            border: 2px solid rgba(97, 97, 255, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            font-size: 15px;
            font-weight: 800;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: white;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .btn i {
            font-size: 18px;
        }

        .btn-primary {
            background: var(--gradient-primary);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(97, 97, 255, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff0065, #dc2626);
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 0, 101, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #64748b, #475569);
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(100, 116, 139, 0.4);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        #cancelAdd {
            display: none;
        }

        /* MAP STYLES */
        #map {
            height: 600px;
            border: 6px solid var(--purple);
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(97, 97, 255, 0.25);
            overflow: hidden;
        }

        #map.crosshair {
            cursor: crosshair !important;
        }

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 22, 122, 0.7);
            backdrop-filter: blur(5px);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 24px;
            width: 90%;
            max-width: 550px;
            box-shadow: 0 25px 80px rgba(0, 22, 122, 0.3);
            border: 3px solid var(--purple);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 3px solid rgba(97, 97, 255, 0.1);
        }

        .modal-header h2 {
            color: var(--navy);
            font-size: 24px;
            font-weight: 900;
            margin: 0;
        }

        .modal-close {
            cursor: pointer;
            font-size: 28px;
            color: #cbd5e1;
            transition: all 0.2s;
            background: none;
            border: none;
            padding: 0;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            color: var(--pink);
            background: rgba(255, 0, 101, 0.1);
            transform: rotate(90deg);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: var(--navy);
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: inherit;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--purple);
            box-shadow: 0 0 0 4px rgba(97, 97, 255, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .form-actions button {
            flex: 1;
            padding: 16px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }

        .btn-submit {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 8px 25px rgba(97, 97, 255, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(97, 97, 255, 0.4);
        }

        .btn-cancel {
            background: #e2e8f0;
            color: #475569;
        }

        .btn-cancel:hover {
            background: #cbd5e1;
        }

        /* STATUS MESSAGE */
        .status-message {
            position: fixed;
            top: 100px;
            right: 30px;
            padding: 18px 28px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 35px rgba(97, 97, 255, 0.25);
            border-left: 5px solid var(--purple);
            z-index: 10001;
            display: none;
            animation: slideIn 0.3s ease;
            max-width: 400px;
        }

        .status-message.show {
            display: block;
        }

        .status-message.success {
            border-left-color: #10b981;
        }

        .status-message.error {
            border-left-color: var(--pink);
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* LEGEND */
        .legend {
            background: white;
            padding: 20px;
            border-radius: 16px;
            margin-top: 25px;
            box-shadow: 0 8px 25px rgba(97, 97, 255, 0.1);
            border: 2px solid rgba(97, 97, 255, 0.1);
        }

        .legend h3 {
            color: var(--navy);
            font-size: 20px;
            font-weight: 800;
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .legend-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        /* Evacuation centers list panel */
        .centers-panel {
            margin-top: 20px;
            background: white;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 8px 25px rgba(97,97,255,0.06);
            border: 1px solid rgba(97,97,255,0.06);
        }

        .centers-panel h3 {
            margin: 0 0 12px 0;
            color: var(--navy);
            font-size: 16px;
            font-weight: 800;
        }

        .centers-list { padding: 0; margin: 0; list-style: none; }
        .center-item { padding: 10px 8px; border-bottom: 1px solid #f1f5ff; display:flex; align-items:center; justify-content:space-between; }
        .center-meta { color:#64748b; font-size:13px; }
        .center-actions button { margin-left:8px; }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: linear-gradient(135deg, #f8f9ff, #fef3f8);
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .legend-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(97, 97, 255, 0.15);
        }

        .legend-icon {
            font-size: 24px;
            width: 32px;
            text-align: center;
        }

        .legend-text {
            font-weight: 700;
            color: var(--navy);
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 25px 20px;
            }

            .page-header {
                margin: -25px -20px 30px;
                padding: 35px 25px;
            }

            .map-container {
                padding: 20px;
            }

            #map {
                height: 450px;
            }

            .control-panel {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .modal-content {
                width: 95%;
                padding: 25px;
            }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <div class="page-header-content">
            <h1><i class="fas fa-map-marked-alt"></i> Live Emergency Map</h1>
            <p>Real-time tracking of emergencies, evacuation sites, and road closures in Baguio City</p>
        </div>
    </div>

    <div class="map-container">
        <div class="control-panel">
            <button class="btn btn-success" id="addSiteBtn">
                <i class="fas fa-plus-circle"></i>
                <span>Add Evacuation Site</span>
            </button>
            <button class="btn btn-danger" id="addClosureBtn">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Add Road Closure</span>
            </button>
            <button class="btn btn-primary" id="refreshBtn">
                <i class="fas fa-sync-alt"></i>
                <span>Refresh Map</span>
            </button>
            <button class="btn btn-secondary" id="cancelAdd">
                <i class="fas fa-times"></i>
                <span>Cancel</span>
            </button>
        </div>

        <div id="map"></div>

        <div class="legend">
            <h3>
                <i class="fas fa-info-circle"></i>
                Map Legend
            </h3>
            <div class="legend-grid">
                <div class="legend-item">
                    <div class="legend-icon" style="background:#ff0065;width:24px;height:24px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3);"></div>
                    <span class="legend-text">Active Emergency</span>
                </div>
                <div class="legend-item">
                    <div class="legend-icon">🏫</div>
                    <span class="legend-text">School Site</span>
                </div>
                <div class="legend-item">
                    <div class="legend-icon">🏛️</div>
                    <span class="legend-text">Barangay Hall</span>
                </div>
                <div class="legend-item">
                    <div class="legend-icon">🏀</div>
                    <span class="legend-text">Court/Gym</span>
                </div>
                <div class="legend-item">
                    <div class="legend-icon" style="background:transparent;width:40px;height:4px;background:#ff0065;box-shadow:0 2px 8px rgba(255,0,101,0.4);"></div>
                    <span class="legend-text">Road Closure</span>
                </div>
            </div>
        </div>
        
        <!-- Evacuation Centers List (unique names) -->
        <div class="centers-panel" aria-live="polite">
            <h3><i class="fas fa-list"></i> Evacuation Centers</h3>
            <div style="margin:10px 0 14px; display:flex; gap:8px; align-items:center;">
                <input id="centersSearch" placeholder="Search centers by name, barangay, type..." style="flex:1;padding:8px 10px;border-radius:8px;border:1px solid #e6eefc;" />
                <button class="btn btn-sm btn-secondary" id="centersRefreshBtn"><i class="fas fa-sync-alt"></i></button>
            </div>
            <div id="evac-centers-list">
                <div class="empty-state">
                    <i class="fas fa-spinner fa-pulse"></i>
                    <p>Loading centers...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Site Modal -->
<div class="modal" id="siteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add Evacuation Site</h2>
            <button class="modal-close" onclick="closeSiteModal()">×</button>
        </div>
        <form id="siteForm" onsubmit="submitSite(event)">
            <input type="hidden" id="siteLat">
            <input type="hidden" id="siteLng">
            
            <div class="form-group">
                <label for="siteName">Site Name *</label>
                <input type="text" id="siteName" required placeholder="e.g., Baguio Central School">
            </div>

            <div class="form-group">
                <label for="siteType">Type *</label>
                <select id="siteType" required>
                    <option value="">-- Select Type --</option>
                    <option value="school">School</option>
                    <option value="barangay_hall">Barangay Hall</option>
                    <option value="court">Court</option>
                    <option value="covered_court">Covered Court</option>
                    <option value="gym">Gym</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="siteBarangay">Barangay *</label>
                <select id="siteBarangay" required>
                    <option value="">-- Select Barangay --</option>
                    <?php foreach($barangays as $brgy): ?>
                        <option value="<?= htmlspecialchars($brgy) ?>"><?= htmlspecialchars($brgy) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-plus-circle"></i> Add Site
                </button>
                <button type="button" class="btn-cancel" onclick="closeSiteModal()">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Center Modal -->
<div class="modal" id="editCenterModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Evacuation Center</h2>
            <button class="modal-close" onclick="closeEditModal()">×</button>
        </div>
        <form id="editCenterForm" onsubmit="submitEditCenter(event)">
            <input type="hidden" id="editCenterId">

            <div class="form-group">
                <label for="editCenterName">Name *</label>
                <input type="text" id="editCenterName" required>
            </div>

            <div class="form-group">
                <label for="editCenterType">Type</label>
                <select id="editCenterType">
                    <option value="">-- Select Type --</option>
                    <option value="school">School</option>
                    <option value="barangay_hall">Barangay Hall</option>
                    <option value="court">Court</option>
                    <option value="covered_court">Covered Court</option>
                    <option value="gym">Gym</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="editCenterBarangay">Barangay</label>
                <select id="editCenterBarangay">
                    <option value="">-- Select Barangay --</option>
                    <?php foreach($barangays as $brgy): ?>
                        <option value="<?= htmlspecialchars($brgy) ?>"><?= htmlspecialchars($brgy) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="editCenterLat">Latitude</label>
                <input type="text" id="editCenterLat">
            </div>

            <div class="form-group">
                <label for="editCenterLng">Longitude</label>
                <input type="text" id="editCenterLng">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Save Changes</button>
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="status-message" id="statusMessage"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const BAGUIO = [16.4023, 120.5960];
const map = L.map('map').setView(BAGUIO, 14);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

let emergencyLayer = L.layerGroup().addTo(map);
let sitesLayer = L.layerGroup().addTo(map);
let closureLayer = L.layerGroup().addTo(map);

let addingMode = false;
let closureMode = false;
let tempMarker = null;
let closureStart = null;
let tempLine = null;
let centersCache = []; // deduplicated centers currently displayed
let centersSearchTerm = '';

// Status message helper
function showStatus(message, type = 'success') {
    const statusEl = document.getElementById('statusMessage');
    statusEl.textContent = message;
    statusEl.className = 'status-message show ' + type;
    setTimeout(() => {
        statusEl.classList.remove('show');
    }, 4000);
}

// Custom icons
function redIcon() {
    return L.divIcon({
        html: '<div style="background:#ff0065;width:28px;height:28px;border-radius:50%;border:4px solid white;box-shadow:0 4px 15px rgba(255,0,101,0.5);"></div>',
        iconSize: [28, 28],
        iconAnchor: [14, 14]
    });
}

function yellowIcon() {
    return L.divIcon({
        html: '<div style="background:#fbbf24;width:32px;height:32px;border-radius:50%;border:4px solid white;box-shadow:0 4px 15px rgba(251,191,36,0.5);color:#000;font-weight:bold;font-size:18px;line-height:28px;text-align:center;">📍</div>',
        iconSize: [32, 32],
        iconAnchor: [16, 16]
    });
}

// Load emergencies
function loadEmergencies() {
    fetch('../api/get-emergencies.php')
        .then(r => r.json())
        .then(data => {
            emergencyLayer.clearLayers();
            data.forEach(e => {
                // Only show emergencies that are actively being handled (responding)
                if (e.status === 'responding' && e.latitude && e.longitude) {
                    L.marker([e.latitude, e.longitude], { icon: redIcon() })
                        .bindPopup(`
                            <div style="min-width:200px;">
                                <h4 style="margin:0 0 10px;color:#ff0065;font-size:16px;">${e.type}</h4>
                                <p style="margin:0 0 8px;color:#555;font-size:14px;">${e.description}</p>
                                <p style="margin:0;color:#999;font-size:12px;">${e.created_at}</p>
                                <a href="?id=${e.id}&status=resolved" 
                                   style="display:inline-block;margin-top:10px;padding:8px 16px;background:#10b981;color:white;text-decoration:none;border-radius:8px;font-weight:bold;font-size:13px;">
                                   ✓ Mark as Resolved
                                </a>
                            </div>
                        `)
                        .addTo(emergencyLayer);
                }
            });
            console.log('Loaded', data.length, 'emergencies');
        })
        .catch(e => console.error('Error loading emergencies:', e));
}

// Load road closures
function loadClosures() {
    fetch('../api/admin/get-closures.php')
        .then(r => r.json())
        .then(data => {
            closureLayer.clearLayers();
            if (data.success && data.data) {
                data.data.forEach(c => {
                    if (c.start_lat && c.start_lng && c.end_lat && c.end_lng) {
                        L.polyline(
                            [[c.start_lat, c.start_lng], [c.end_lat, c.end_lng]],
                            {
                                color: '#ff0065',
                                weight: 6,
                                opacity: 0.85,
                                dashArray: '10, 5'
                            }
                        )
                        .bindPopup(`
                            <div style="min-width:220px;">
                                <h4 style="margin:0 0 10px;color:#ff0065;font-size:16px;">⚠ Road Closure</h4>
                                <p style="margin:0 0 8px;color:#555;font-size:14px;">${c.description}</p>
                                <p style="margin:0;color:#999;font-size:12px;">${c.created_at}</p>
                                <div style="margin-top:12px;text-align:right;">
                                    <button class="btn btn-sm btn-danger" onclick="deleteClosure(${c.id})"                                       <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        `)
                        .addTo(closureLayer);
                    }
                });
                console.log('Loaded', data.data.length, 'road closures');
            }
        })
        .catch(e => console.warn('Could not load closures:', e));
}

// Delete closure via API
function deleteClosure(id) {
    if (!confirm('Remove this road closure?')) return;
    fetch('../api/admin/delete-closure.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    })
    .then(r => r.json())
    .then(res => {
        if (res && res.success) {
            if (typeof showStatus === 'function') showStatus(res.message || 'Road closure removed', 'success');
            loadClosures();
        } else {
            if (typeof showStatus === 'function') showStatus(res.message || 'Could not remove closure', 'error');
        }
    })
    .catch(e => {
        console.error('Delete closure error', e);
        if (typeof showStatus === 'function') showStatus('Error removing closure', 'error');
    });
}

// Load evacuation sites
function loadSites() {
    const siteIcons = {
        school: '🏫',
        barangay_hall: '🏛️',
        court: '🏀',
        covered_court: '🏀',
        gym: '🏋️',
        other: '✅'
    };

    fetch('../api/evacuation/get-sites.php?t=' + Date.now())
        .then(r => r.json())
        .then(data => {
            sitesLayer.clearLayers();
            if (data.success && data.data) {
                data.data.forEach(site => {
                    const icon = siteIcons[site.type] || '📍';
                    const customIcon = L.divIcon({
                        html: `<div style="font-size: 30px;filter:drop-shadow(0 2px 4px rgba(0,0,0,0.3));">${icon}</div>`,
                        className: 'custom-marker',
                        iconSize: [30, 30],
                        iconAnchor: [15, 30]
                    });
                    L.marker([site.latitude, site.longitude], { icon: customIcon })
                        .bindPopup(`
                            <div style="min-width:200px;">
                                <h4 style="margin:0 0 10px;color:#00167a;font-size:16px;">${site.name}</h4>
                                <p style="margin:0 0 4px;"><strong>Type:</strong> ${site.type}</p>
                                <p style="margin:0 0 4px;"><strong>Barangay:</strong> ${site.barangay}</p>
                            </div>
                        `)
                        .addTo(sitesLayer);
                });
                console.log('Loaded', data.data.length, 'evacuation centers');
            }
        })
        .catch(e => console.warn('Could not load sites:', e));
}

// Load unique evacuation centers list and render
function loadCentersList() {
    fetch('../api/evacuation/get-sites.php?t=' + Date.now())
        .then(r => r.json())
        .then(res => {
            if (!res || !res.success || !res.data) return renderCenters([]);

            const byName = new Map();
            res.data.forEach(s => {
                const nameKey = (s.name || '').trim().toLowerCase();
                if (!nameKey) return;
                if (!byName.has(nameKey)) {
                    byName.set(nameKey, s);
                }
            });

            const list = Array.from(byName.values());
            centersCache = list;
            renderCenters(list);
        })
        .catch(e => {
            console.warn('Could not load centers list', e);
            renderCenters([]);
        });
}

function renderCenters(list) {
    const container = document.getElementById('evac-centers-list');
    if (!list || list.length === 0) {
        container.innerHTML = `<div class="empty-state"><i class="fas fa-inbox"></i><p>No centers found.</p></div>`;
        return;
    }
    // apply search filter
    const term = (centersSearchTerm || '').trim().toLowerCase();
    const filtered = term ? list.filter(c => {
        return (c.name||'').toLowerCase().includes(term)
            || (c.barangay||'').toLowerCase().includes(term)
            || (c.type||'').toLowerCase().includes(term);
    }) : list;

    let html = '<ul class="centers-list">';
    filtered.forEach(c => {
        html += `<li class="center-item">
            <div>
                <div style="font-weight:800;color:var(--navy);">${escapeHtml(c.name)}</div>
                <div class="center-meta">${escapeHtml(c.barangay || '')} • ${escapeHtml(c.type || '')}</div>
            </div>
            <div class="center-actions">
                <button class="btn btn-sm btn-secondary" onclick="openEditModal(${c.id})"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-danger" onclick="deleteCenter(${c.id})"><i class="fas fa-trash"></i></button>
            </div>
        </li>`;
    });
    html += '</ul>';
    container.innerHTML = html;
}

// search handling
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('centersSearch');
    if (input) {
        input.addEventListener('input', (e) => {
            centersSearchTerm = e.target.value;
            renderCenters(centersCache);
        });
    }
    const rbtn = document.getElementById('centersRefreshBtn');
    if (rbtn) rbtn.addEventListener('click', () => loadCentersList());
    // wire edit modal form submit
    const editForm = document.getElementById('editCenterForm');
    if (editForm) editForm.addEventListener('submit', submitEditCenter);
});

function editCenter(id) {
    // open edit modal populated with center data
    openEditModal(id);
}

function deleteCenter(id) {
    if (!confirm('Delete this evacuation center permanently?')) return;
    fetch('../api/admin/delete-center.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    }).then(r => r.json()).then(res => {
        if (res.success) {
            showStatus('Center deleted', 'success');
            loadCentersList();
            loadSites();
        } else {
            showStatus('Delete failed: ' + (res.error || res.message || 'unknown'), 'error');
        }
    }).catch(e => {
        console.error(e);
        showStatus('Network error', 'error');
    });
}

// Modal helpers for edit
function openEditModal(id) {
    const center = centersCache.find(c => c.id == id);
    if (!center) return showStatus('Center not found', 'error');

    document.getElementById('editCenterId').value = center.id;
    document.getElementById('editCenterName').value = center.name || '';
    document.getElementById('editCenterType').value = center.type || '';
    document.getElementById('editCenterBarangay').value = center.barangay || '';
    document.getElementById('editCenterLat').value = center.latitude || center.lat || '';
    document.getElementById('editCenterLng').value = center.longitude || center.lng || '';

    document.getElementById('editCenterModal').classList.add('show');
}

function closeEditModal() {
    document.getElementById('editCenterForm').reset();
    document.getElementById('editCenterModal').classList.remove('show');
}

function submitEditCenter(e) {
    e.preventDefault();
    const id = document.getElementById('editCenterId').value;
    const payload = {
        id: id,
        name: document.getElementById('editCenterName').value,
        type: document.getElementById('editCenterType').value,
        barangay: document.getElementById('editCenterBarangay').value,
        lat: document.getElementById('editCenterLat').value || null,
        lng: document.getElementById('editCenterLng').value || null
    };

    fetch('../api/admin/edit-center.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    }).then(r => r.json()).then(res => {
        if (res.success) {
            showStatus('Center updated', 'success');
            closeEditModal();
            loadCentersList();
            loadSites();
        } else {
            showStatus('Update failed: ' + (res.error || res.message || 'unknown'), 'error');
        }
    }).catch(e => {
        console.error(e);
        showStatus('Network error', 'error');
    });
}

function escapeHtml(str){
    if(!str) return '';
    return String(str).replace(/[&<>"']/g, (s) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
}

// Modal functions
function closeSiteModal() {
    document.getElementById('siteModal').classList.remove('show');
    document.getElementById('siteForm').reset();
    if (tempMarker) map.removeLayer(tempMarker);
}

function submitSite(event) {
    event.preventDefault();
    
    const formData = {
        name: document.getElementById('siteName').value,
        type: document.getElementById('siteType').value,
        barangay: document.getElementById('siteBarangay').value,
        lat: parseFloat(document.getElementById('siteLat').value) || 0,
        lng: parseFloat(document.getElementById('siteLng').value) || 0
    };

    fetch('../api/admin/add-center.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showStatus('Evacuation site added successfully!', 'success');
            loadSites();
            loadCentersList();
            closeSiteModal();
        } else {
            showStatus('Error: ' + data.message, 'error');
        }
        addingMode = false;
        document.getElementById('addSiteBtn').style.display = 'inline-flex';
        document.getElementById('addClosureBtn').style.display = 'inline-flex';
        document.getElementById('refreshBtn').style.display = 'inline-flex';
        document.getElementById('cancelAdd').style.display = 'none';
        document.getElementById('map').classList.remove('crosshair');
    })
    .catch(err => {
        showStatus('Network error: ' + err.message, 'error');
        console.error(err);
    });
}

// Button handlers
document.getElementById('addSiteBtn').onclick = () => {
    addingMode = true;
    closureMode = false;
    document.getElementById('addSiteBtn').style.display = 'none';
    document.getElementById('addClosureBtn').style.display = 'none';
    document.getElementById('refreshBtn').style.display = 'none';
    document.getElementById('cancelAdd').style.display = 'inline-flex';
    document.getElementById('map').classList.add('crosshair');
    showStatus('Click on the map to place evacuation site', 'success');
};

document.getElementById('addClosureBtn').onclick = () => {
    closureMode = true;
    addingMode = false;
    closureStart = null;
    document.getElementById('addSiteBtn').style.display = 'none';
    document.getElementById('addClosureBtn').style.display = 'none';
    document.getElementById('refreshBtn').style.display = 'none';
    document.getElementById('cancelAdd').style.display = 'inline-flex';
    document.getElementById('map').classList.add('crosshair');
    showStatus('Click START point of road closure', 'success');
};

document.getElementById('cancelAdd').onclick = () => {
    addingMode = false;
    closureMode = false;
    closureStart = null;
    document.getElementById('addSiteBtn').style.display = 'inline-flex';
    document.getElementById('addClosureBtn').style.display = 'inline-flex';
    document.getElementById('refreshBtn').style.display = 'inline-flex';
    document.getElementById('cancelAdd').style.display = 'none';
    document.getElementById('map').classList.remove('crosshair');
    if (tempMarker) map.removeLayer(tempMarker);
    if (tempLine) map.removeLayer(tempLine);
    closeSiteModal();
    showStatus('Action cancelled', 'error');
};

document.getElementById('refreshBtn').onclick = () => {
    loadEmergencies();
    loadSites();
    loadClosures();
    loadCentersList();
    showStatus('Map refreshed successfully', 'success');
};

// Map click handler
map.on('click', e => {
    if (!addingMode && !closureMode) return;

    if (addingMode) {
        if (tempMarker) map.removeLayer(tempMarker);
        
        const icon = L.divIcon({
            html: '<div style="font-size: 30px;">📍</div>',
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        });
        tempMarker = L.marker(e.latlng, { icon: icon }).addTo(map);

        document.getElementById('siteLat').value = e.latlng.lat;
        document.getElementById('siteLng').value = e.latlng.lng;
        document.getElementById('siteModal').classList.add('show');

    } else if (closureMode) {
        if (!closureStart) {
            // First point
            closureStart = e.latlng;
            tempMarker = L.marker(e.latlng, { icon: yellowIcon() }).addTo(map);
            showStatus('Now click END point of road closure', 'success');
        } else {
            // Second point
            let description = prompt("Road Closure Description:", "Road closed due to emergency");
            if (!description) {
                map.removeLayer(tempMarker);
                closureStart = null;
                return;
            }

            // Show preview line
            if (tempLine) map.removeLayer(tempLine);
            tempLine = L.polyline([closureStart, e.latlng], {
                color: '#ff0065',
                weight: 6,
                opacity: 0.85,
                dashArray: '10, 5'
            }).addTo(map);

            fetch('../api/admin/add-closure.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    start_lat: closureStart.lat,
                    start_lng: closureStart.lng,
                    end_lat: e.latlng.lat,
                    end_lng: e.latlng.lng,
                    description: description,
                    severity: 'high',
                    status: 'active'
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    loadClosures();
                    showStatus('Road closure added successfully', 'success');
                } else {
                    showStatus('Error: ' + data.message, 'error');
                }
                closureMode = false;
                closureStart = null;
                document.getElementById('addSiteBtn').style.display = 'inline-flex';
                document.getElementById('addClosureBtn').style.display = 'inline-flex';
                document.getElementById('refreshBtn').style.display = 'inline-flex';
                document.getElementById('cancelAdd').style.display = 'none';
                document.getElementById('map').classList.remove('crosshair');
                if (tempMarker) map.removeLayer(tempMarker);
                if (tempLine) map.removeLayer(tempLine);
            })
            .catch(err => {
                showStatus('Network error: ' + err.message, 'error');
                console.error(err);
            });
        }
    }
});

// Load everything on start
loadEmergencies();
loadSites();
loadClosures();
loadCentersList();

// Auto-refresh every 30 seconds
setInterval(() => {
    loadEmergencies();
    loadSites();
    loadClosures();
    loadCentersList();
}, 30000);
</script>

<script src="../assets/js/chatbot.js"></script>
</body>
</html>