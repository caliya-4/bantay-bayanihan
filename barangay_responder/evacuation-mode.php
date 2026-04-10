<?php 
session_start(); 
require '../db_connect.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'responder') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EVACUATION MODE ACTIVATED</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responder.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #000;
            color: white;
            font-family: 'Arial Black', Arial, sans-serif;
            text-align: center;
            overflow: hidden;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .alert-text {
            font-size: 48px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 8px;
            animation: blink 1.5s infinite;
            text-shadow: 0 0 20px red;
            margin-bottom: 30px;
        }
        .subtitle {
            font-size: 28px;
            margin: 20px 0 50px;
            opacity: 0.9;
        }
        .sos-button {
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, #ff0000, #8b0000);
            color: white;
            font-size: 56px;
            font-weight: bold;
            border: 8px solid #ff3333;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 0 60px rgba(255, 0, 0, 0.8), inset 0 0 40px rgba(255, 255, 255, 0.3);
            animation: pulse 2s infinite, glow 1.5s infinite alternate;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            text-shadow: 0 0 20px white;
        }
        .sos-button:hover {
            transform: scale(1.1);
            box-shadow: 0 0 100px red;
        }
        .sos-button:active {
            background: #ff3333;
            transform: scale(0.95);
        }

        /* Animations */
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 60px rgba(255,0,0,0.8); }
            70% { box-shadow: 0 0 100px rgba(255,0,0,1); }
            100% { box-shadow: 0 0 60px rgba(255,0,0,0.8); }
        }
        @keyframes glow {
            from { text-shadow: 0 0 20px white; }
            to { text-shadow: 0 0 50px white, 0 0 80px red; }
        }

        /* Mobile Optimization */
        @media (max-width: 480px) {
            .alert-text { font-size: 32px; letter-spacing: 4px; }
            .subtitle { font-size: 20px; }
            .sos-button { width: 280px; height: 280px; font-size: 44px; }
        }
    </style>
</head>
<body>

    <div class="alert-text">EVACUATION MODE ACTIVATED</div>
    <div class="subtitle">Your safety is our priority. Tap below for immediate help!</div>

    <!-- BIG RED SOS BUTTON -->
    <button onclick="triggerSOS()" class="sos-button">
        SOS<br>HELP ME NOW!
    </button>

    <div style="margin-top:50px;font-size:18px;opacity:0.7;">
        This device is in emergency mode
    </div>

    <!-- Hidden Form for Instant Report -->
    <form id="sosForm" method="POST" action="report-emergency.php" style="display:none;">
        <input type="hidden" name="type" value="Emergency">
        <input type="hidden" name="desc" value="USER ACTIVATED EVACUATION MODE SOS BUTTON - URGENT HELP NEEDED">
        <input type="hidden" name="lat" id="sosLat">
        <input type="hidden" name="lng" id="sosLng">
        <input type="hidden" name="address" value="Location from Evacuation Mode">
    </form>

    <!-- Alert Sound (Silent fallback if blocked) -->
    <audio id="alertSound" preload="auto">
        <source src="https://cdn.pixabay.com/download/audio/2022/03/15/audio_859c38d441.mp3?filename=emergency-alarm-12909.mp3" type="audio/mp3">
    </audio>

    <script>
        // Play alarm sound (with user interaction fallback)
        function playAlarm() {
            const sound = document.getElementById('alertSound');
            sound.play().catch(() => {
                // If blocked, wait for user click
                document.body.addEventListener('click', () => sound.play(), { once: true });
            });
        }

        // Get location and submit SOS
        function triggerSOS() {
            playAlarm();
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    pos => {
                        document.getElementById('sosLat').value = pos.coords.latitude;
                        document.getElementById('sosLng').value = pos.coords.longitude;
                        document.getElementById('sosForm').submit();
                    },
                    () => {
                        // Even without location, send SOS
                        document.getElementById('sosForm').submit();
                    },
                    { timeout: 10000, enableHighAccuracy: true }
                );
            } else {
                document.getElementById('sosForm').submit();
            }
        }

        // Auto-play alarm on page load (after user gesture if needed)
        window.addEventListener('load', () => {
            setTimeout(playAlarm, 1000);
        });
    </script>

    <!-- Keep chatbot (optional - you can remove if too much) -->
    <script src="../assets/js/chatbot.js"></script>
</body>
</html>