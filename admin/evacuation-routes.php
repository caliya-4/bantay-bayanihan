<?php session_start(); require '../db_connect.php';
include '../includes/header.php';
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'],['admin','responder'])) header("Location: ../login.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evacuation Routes | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/design-system.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
    h1 { 
        color:#CC0000; 
        text-align:center; 
        font-weight:900; 
        font-size:36px; 
        margin:0 0 15px 0; 
    }
    h1::after {
        content:''; 
        display:block; 
        width:140px; 
        height:6px; 
        background:#CC0000; 
        margin:12px auto 0; 
        border-radius:3px;
    }
    #map{
        height:62vh;                    /* ← Smaller & perfectly fitted */
        max-height:680px;
        border:8px solid #CC0000;       /* ← Scarlet Red frame */
        border-radius:20px;
        box-shadow:0 15px 40px rgba(204,0,0,0.35);
        margin-top:20px;
    }
</style>
</head><body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <h1>Evacuation Routes & Safe Zones</h1>
    <div id="map"></div>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../assets/js/map.js"></script>
<script>
    currentMap = initMap(14.5995, 120.9842, 12);
    loadEmergencies();

    // Example: Add evacuation centers (you can add more)
    const centers = [
        {name:"Barangay Hall 1", lat:14.6095, lng:120.9942},
        {name:"School Evac Center", lat:14.5890, lng:120.9790},
        {name:"Church Safe Zone", lat:14.6200, lng:120.9900}
    ];
    centers.forEach(c => {
        L.marker([c.lat, c.lng]).addTo(currentMap)
         .bindPopup(`<b>Safe Zone:</b> ${c.name}`);
    });
</script>
</body></html>