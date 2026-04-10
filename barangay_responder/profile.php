<?php 
session_start(); 
require '../db_connect.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found!";
    exit;
}

$msg = '';

if ($_POST) {
    $name    = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);

    $update = $pdo->prepare("UPDATE users SET name = ?, contact = ?, address = ? WHERE id = ?");
    $update->execute([$name, $contact, $address, $_SESSION['user_id']]);

    $_SESSION['name'] = $name;
    $msg = "Profile updated successfully!";

    // Refresh displayed data
    $user['name'] = $name;
    $user['contact'] = $contact;
    $user['address'] = $address;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Bantay Bayanihan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/responder.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f8fafc;
            margin: 0;
        }
        .main-content {
            margin-left: 270px;
            padding: 40px 30px;
            min-height: calc(100vh - 70px);
        }

        /* NAVY BLUE PAGE TITLE */
        .main-content h1 {
            color: #1e3a8a;
            font-size: 34px;
            font-weight: 900;
            text-align: center;
            margin: 0 0 15px 0;
        }
        .main-content h1::after {
            content: '';
            display: block;
            width: 110px;
            height: 6px;
            background: #1e3a8a;
            margin: 12px auto 0;
            border-radius: 3px;
        }

        .page-subtitle {
            text-align: center;
            color: #475569;
            font-size: 18px;
            margin-bottom: 40px;
        }

        /* SUCCESS MESSAGE */
        .success-alert {
            background: #ecfdf5;
            color: #166534;
            padding: 18px;
            border-radius: 14px;
            text-align: center;
            font-weight: bold;
            font-size: 17px;
            border: 2px solid #16a34a;
            margin: 20px auto;
            max-width: 680px;
            box-shadow: 0 4px 15px rgba(22,163,74,0.1);
        }

        /* PROFILE FORM CARD */
        .profile-card {
            background: white;
            max-width: 680px;
            margin: 0 auto 35px;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(30,58,138,0.15);
            border: 1px solid #e2e8f0;
            border-left: 6px solid #1e3a8a;
        }

        .profile-card h2 {
            color: #1e3a8a;
            font-size: 26px;
            margin: 0 0 25px 0;
            font-weight: 800;
            text-align: center;
        }

        .form-group {
            margin-bottom: 22px;
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 700;
            color: #1e3a8a;
            font-size: 16px;
        }
        input, textarea {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #cbd5e1;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
            font-family: inherit;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #1e3a8a;
            box-shadow: 0 0 0 4px rgba(30,58,138,0.15);
        }
        textarea {
            resize: vertical;
            min-height: 110px;
        }

        /* NAVY BLUE SAVE BUTTON */
        .save-btn {
            background: #1e3a8a;
            color: white;
            padding: 16px 40px;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 900;
            cursor: pointer;
            transition: all 0.35s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 8px 25px rgba(30,58,138,0.4);
            display: block;
            margin: 20px auto 0;
        }
        .save-btn:hover {
            background: #172554;
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(30,58,138,0.5);
        }

        /* ACCOUNT INFO CARD */
        .info-card {
            background: #f0f5ff;
            max-width: 680px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 18px;
            border: 2px dashed #1e3a8a;
            text-align: center;
            font-size: 16px;
        }
        .info-card h3 {
            color: #1e3a8a;
            font-size: 24px;
            margin: 0 0 20px 0;
            font-weight: 800;
        }
        .info-card p {
            margin: 12px 0;
            color: #334155;
        }
        .info-card strong {
            color: #1e3a8a;
        }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; padding: 25px 15px; }
            .profile-card, .info-card { padding: 25px; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <h1>My Profile</h1>
    <p class="page-subtitle">Update your personal information for faster emergency response</p>

    <?php if ($msg): ?>
        <div class="success-alert">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <!-- EDITABLE PROFILE FORM -->
    <div class="profile-card">
        <h2>Edit Your Information</h2>
        <form method="POST">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="contact" value="<?= htmlspecialchars($user['contact'] ?? '') ?>" 
                       placeholder="e.g. 0917-123-4567">
            </div>

            <div class="form-group">
                <label>Complete Address *</label>
                <textarea name="address" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="save-btn">
                Save Changes
            </button>
        </form>
    </div>

    <!-- ACCOUNT DETAILS -->
    <div class="info-card">
        <h3>Your Account Details</h3>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Role:</strong> Resident</p>
        <p><strong>Member Since:</strong> <?= date('F d, Y', strtotime($user['created_at'])) ?></p>
        <p><strong>Status:</strong> <span style="color:#16a34a;font-weight:bold;">Active & Verified</span></p>
    </div>
</div>

<script src="../assets/js/chatbot.js"></script>
</body>
</html>