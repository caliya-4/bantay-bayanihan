<?php
require 'db_connect.php';
$msg = '';
$barangays = [
    "Abanao-Zandueta-Kayong-Chugum-Otek", "Abatan", "Agdao", "Alno", "Ambassador",
    "Andres Bonifacio", "Apugan-Loakan", "Asin Road", "Aspin",
    "Aurora Hill Proper", "Aurora Hill North Central", "Aurora Hill South Central",
    "Bagong Lipunan", "Bakakeng Central", "Bakakeng North", "Balsigan",
    "Banao-Gernale", "Bayan Park East", "Bayan Park West", "Bayan Park Village",
    "BGH Compound", "Cabinet Hill-Teachers Camp", "Camdas Subdivision",
    "Camp 7", "Camp 8", "Camp Allen", "Campo Filipino",
    "City Camp Central", "City Camp Proper", "Commercial",
    "Dagsian Lower", "Dagsian Upper", "Dizon Subdivision", "Dontogan", "DPS Area",
    "Engineers Hill", "Fairview Village", "Ferdinand", "Fort del Pilar",
    "Gabriela Silang", "General Luna Road", "Gibraltar", "Greenwater Village",
    "Guisad Central", "Guisad Sorong", "Happy Hollow",
    "Happy Homes-Engineer's Hill", "Harrison Road", "Holy Ghost Extension",
    "Holy Ghost Proper", "Honeymoon-Military Cut-off", "Ib-ib",
    "Imelda Marcos Avenue", "Imelda Village", "Irisan", "Kabayanihan",
    "Kagitingan", "Kayang Extension", "Kayang-Hilltop", "Kias",
    "Legarda-Burnham-Kisad", "Lourdes Subdivision Extension",
    "Lourdes Subdivision Proper", "Lower Magsaysay", "Lower Rock Quarry",
    "Lualhati", "Lucban", "Lucky", "Magsaysay Private Road",
    "Magsaysay Lower", "Magsaysay Upper", "Malcolm Square-Perfecto",
    "Manor House", "Magsaysay-Hilltop", "Market Subdivision Upper",
    "Military Cut-off", "Mines View Park", "Modern Sites", "MRR-Queen of Peace",
    "New Lucban", "Outlook Drive", "Pacdal", "Padre Burgos", "Padre Zamora",
    "Palma-Urbano", "Park Place", "Phillippine Rabbit", "Pinget",
    "Pinsao Pilot Project", "Pinsao Proper", "Poliwes", "Pucay", "Purok 3-A",
    "Quezon Hill Proper", "Quezon Hill Upper", "Quirino Hill East",
    "Quirino Hill Lower", "Quirino Hill Middle", "Quirino Hill West",
    "Quirino-Magsaysay-Prieto-Magalong", "Rizal Monument Area",
    "Rock Quarry Lower", "Rock Quarry Middle", "Rock Quarry Upper",
    "Roxas-Triangle", "Saint Joseph Village", "Salud Mitra",
    "San Antonio Village", "San Luis Village", "San Roque Village",
    "San Vicente", "Santa Escolastica", "Santo Rosario-Assumption",
    "Santo Tomas Proper", "Santo Tomas School Area", "Scout Barrio",
    "Session Road Area", "Slaughter House Area", "SLU-SVP Housing Village",
    "South Drive", "Teodora Alonzo", "Tiptop", "Trancoville",
    "Upper Dagsian", "Upper Magsaysay", "Upper QM", "Upper Rock Quarry",
    "Veterans Ampitheatre"
];

if ($_POST) {
    // Verify CSRF token if present
    if (isset($_POST['csrf_token']) && !verifyCSRFToken($_POST['csrf_token'])) {
        $msg = "Invalid security token. Please refresh and try again.";
        $msgClass = "error";
    } else {
        $name     = trim($_POST['name']);
        $email    = trim($_POST['email']);
        $pass     = $_POST['password'];
        $contact  = trim($_POST['contact']);
        $address  = trim($_POST['address']);
        $barangay = trim($_POST['barangay']);

        // Validate password strength
        if (strlen($pass) < 8) {
            $msg = "Password must be at least 8 characters long.";
            $msgClass = "error";
        } else {
            // Hash password before storing
            $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

            try {
                $stmt = $pdo->prepare("INSERT INTO users(name,email,password,contact,address,barangay,role) VALUES(?,?,?,?,?,?,'responder')");
                $stmt->execute([$name, $email, $hashedPassword, $contact, $address, $barangay]);
                $msg = "Registration successful! Wait for admin approval.";
                $msgClass = "success";
            } catch(Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    $msg = "Email already taken.";
                } else {
                    $msg = "Registration failed. Please try again.";
                }
                $msgClass = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bantay Bayanihan | Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #f8f9ff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            display: flex;
            width: 100%;
            max-width: 860px;
            min-height: 560px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 80px rgba(0,22,122,0.25);
        }

        .left {
            background: linear-gradient(135deg, #6161ff, #ff0065);
            color: white;
            padding: 30px 25px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }
        .shield { font-size: 70px; margin-bottom: 15px; }
        .left h1 { font-size: 26px; font-weight: 900; margin-bottom: 8px; }
        .tagline { font-size: 16px; font-weight: bold; margin: 12px 0; }
        .left p { font-size: 13px; opacity: 0.9; line-height: 1.4; }

        .right {
            padding: 35px 40px;
            flex: 1.1;
            background: #fff;
            overflow-y: auto;
        }
        .right h2 {
            color: #ff0065;
            font-size: 22px;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }

        input, textarea, select {
            width: 100%;
            padding: 11px 14px;
            margin: 8px 0;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background: #fff;
        }
        input:focus, textarea:focus, select:focus {
            border-color: #ff0065;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255,0,101,0.1);
        }
        textarea { min-height: 70px; resize: none; }

        /* Barangay select label */
        .field-label {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            margin: 10px 0 2px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        select {
            color: #334155;
            cursor: pointer;
        }
        select option[value=""] { color: #94a3b8; }

        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        button[type="submit"], .back-btn {
            background: #ff0065;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            flex: 1;
            transition: 0.3s;
            text-align: center;
        }
        button[type="submit"]:hover, .back-btn:hover {
            background: #00167a;
            transform: translateY(-2px);
        }
        .success, .error {
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            font-size: 14px;
            margin: 10px 0;
            font-weight: bold;
        }
        .success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        .error   { background:#ffe6e6; color:#CC0000; border:1px solid #CC0000; }

        @media (max-width: 820px) {
            .container { flex-direction: column; max-width: 380px; }
            .left { padding: 30px 20px; }
            .shield { font-size: 60px; }
            .right { padding: 30px 25px; }
            .form-buttons { flex-direction: column; gap: 8px; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- LEFT: HERO -->
    <div class="left">
        <i class="fas fa-shield-alt shield"></i>
        <h1>BANTAY BAYANIHAN</h1>
        <div class="tagline">Be Ready. Be a Hero.</div>
        <p>Join your community in emergency preparedness.</p>
    </div>

    <!-- RIGHT: FORM -->
    <div class="right">
        <h2>Register</h2>
        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="contact" placeholder="Contact Number">
            <textarea name="address" placeholder="Full Address" required></textarea>

            <!-- ✅ NEW: Barangay Dropdown -->
            <p class="field-label"><i class="fas fa-map-marker-alt"></i> Select Your Barangay</p>
            <select name="barangay" required>
                <option value="" disabled selected>-- Select Barangay (Baguio City) --</option>
                <?php foreach($barangays as $b): ?>
                    <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
                <?php endforeach; ?>
            </select>

            <div class="form-buttons">
                <button type="submit">REGISTER</button>
                <button type="button" class="back-btn" onclick="window.location.href='login.php'">BACK TO LOGIN</button>
            </div>
        </form>

        <?php if($msg): ?>
            <div class="<?= $msgClass ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>