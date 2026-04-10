<?php 
session_start(); 
require '../db_connect.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'responder') {
    header("Location: ../login.php");
    exit;
}

$msg = $error = '';

// Handle photo upload
function handlePhotoUpload($emergencyId) {
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($_FILES['photo']['type'], $allowed)) {
        return null;
    }

    $maxSize = 5 * 1024 * 1024;
    if ($_FILES['photo']['size'] > $maxSize) {
        return null;
    }

    $uploadDir = '../uploads/emergency-photos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $filename = 'emergency_' . $emergencyId . '_' . time() . '.' . $ext;
    $filePath = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
        return $filename;
    }
    return null;
}

// CREATE or UPDATE EMERGENCY
if ($_POST && isset($_POST['action'])) {
    $action = $_POST['action'];
    $type     = $_POST['type'] ?? '';
    $desc     = trim($_POST['desc'] ?? '');
    $lat      = !empty($_POST['lat']) ? (float)$_POST['lat'] : null;
    $lng      = !empty($_POST['lng']) ? (float)$_POST['lng'] : null;
    $address  = trim($_POST['address'] ?? '');

    // Get severity based on type
    $severity = '';
    if ($type === 'Flood') {
        $severity = trim($_POST['flood_severity'] ?? '');
    } elseif ($type === 'Fire') {
        $severity = trim($_POST['fire_severity'] ?? '');
    } elseif ($type === 'Earthquake') {
        $severity = trim($_POST['earthquake_severity'] ?? '');
    } elseif ($type === 'Landslide') {
        $severity = trim($_POST['landslide_severity'] ?? '');
    } elseif ($type === 'Road Blockage') {
        $severity = trim($_POST['roadblock_severity'] ?? '');
    }

    // Append severity to type label if applicable
    if ($severity) {
        $type = $type . ' (' . $severity . ')';
    }

    if (!$type || !$desc || !$address) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO emergencies (user_id, type, description, latitude, longitude, address, status) 
                                       VALUES (?, ?, ?, ?, ?, ?, 'pending')");
                $stmt->execute([$_SESSION['user_id'], $type, $desc, $lat, $lng, $address]);
                $newId = $pdo->lastInsertId();

                $photoName = handlePhotoUpload($newId);
                if ($photoName) {
                    $updatePhoto = $pdo->prepare("UPDATE emergencies SET photo = ? WHERE id = ?");
                    $updatePhoto->execute([$photoName, $newId]);
                }

                try {
                    $admins = $pdo->query("SELECT id, name FROM users WHERE role IN ('admin','responder')")->fetchAll();
                    $ins = $pdo->prepare('INSERT INTO notifications (user_id, message, url) VALUES (?, ?, ?)');
                    $reporterName = htmlspecialchars($_SESSION['name']);
                    foreach ($admins as $a) {
                        $message = "New emergency reported (ID: $newId) — $type by $reporterName";
                        $url = "admin/manage-emergencies.php?id=$newId";
                        $ins->execute([$a['id'], $message, $url]);
                    }
                } catch (Exception $e) {
                    // non-fatal
                }

                $msg = "Emergency reported successfully! Help is on the way.";
            } elseif ($action === 'edit' && isset($_POST['emergency_id'])) {
                $emergencyId = (int)$_POST['emergency_id'];
                
                $verify = $pdo->prepare("SELECT id FROM emergencies WHERE id = ? AND user_id = ?");
                $verify->execute([$emergencyId, $_SESSION['user_id']]);
                if (!$verify->fetch()) {
                    $error = "You can only edit your own reports.";
                } else {
                    $stmt = $pdo->prepare("UPDATE emergencies SET type = ?, description = ?, latitude = ?, longitude = ?, address = ? WHERE id = ?");
                    $stmt->execute([$type, $desc, $lat, $lng, $address, $emergencyId]);

                    $photoName = handlePhotoUpload($emergencyId);
                    if ($photoName) {
                        $updatePhoto = $pdo->prepare("UPDATE emergencies SET photo = ? WHERE id = ?");
                        $updatePhoto->execute([$photoName, $emergencyId]);
                    }

                    $msg = "Emergency report updated successfully!";
                }
            }
        } catch (Exception $e) {
            $error = "Failed to process report. Please try again.";
        }
    }
}

// DELETE EMERGENCY
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $emergencyId = (int)$_GET['delete'];
    
    $getPhoto = $pdo->prepare("SELECT photo FROM emergencies WHERE id = ? AND user_id = ?");
    $getPhoto->execute([$emergencyId, $_SESSION['user_id']]);
    $emergency = $getPhoto->fetch();

    if ($emergency) {
        if ($emergency['photo']) {
            $photoPath = '../uploads/emergency-photos/' . $emergency['photo'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }

        $delete = $pdo->prepare("DELETE FROM emergencies WHERE id = ? AND user_id = ?");
        $delete->execute([$emergencyId, $_SESSION['user_id']]);
        $msg = "Emergency report deleted successfully.";
    } else {
        $error = "You can only delete your own reports.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Emergency | Bantay Bayanihan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/design-system.css">
    <style>
        .page-header h1 { font-size: clamp(28px, 5vw, 42px); }

        .report-form {
            max-width: 680px;
            margin: 30px auto;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 35px rgba(97, 97, 255, 0.1);
            border: 1px solid rgba(97, 97, 255, 0.08);
        }

        .form-group { margin-bottom: 24px; }

        .form-group label {
            display: block;
            color: var(--navy);
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--purple);
            box-shadow: 0 0 0 4px rgba(97, 97, 255, 0.1);
        }

        .form-group textarea { resize: vertical; min-height: 120px; }

        /* ── Flood severity dropdown ─────────────────────────────── */
        #flood-severity-group {
            display: none;
            margin-top: -10px;
            margin-bottom: 24px;
            animation: fadeSlideDown 0.25s ease;
        }

        #flood-severity-group.visible { display: block; }

        @keyframes fadeSlideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .severity-label {
            display: block;
            color: var(--navy);
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }

        /* ── rest of original styles ─────────────────────────────── */
        .location-info {
            background: var(--gray-100);
            padding: 16px;
            border-radius: 12px;
            margin: 12px 0;
            font-size: 14px;
            border-left: 5px solid var(--purple);
            color: var(--navy);
        }

        .photo-upload {
            position: relative;
            border: 2px dashed var(--gray-300);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--gray-50);
        }

        .photo-upload:hover { border-color: var(--purple); background: rgba(97, 97, 255, 0.05); }
        .photo-upload input[type="file"] { display: none; }
        .photo-upload-text { color: var(--gray-600); font-weight: 600; pointer-events: none; }

        .photo-preview { margin-top: 15px; border-radius: 12px; overflow: hidden; max-width: 300px; }
        .photo-preview img { width: 100%; height: auto; display: block; }

        .big-sos-btn {
            background: var(--gradient-primary);
            color: white;
            font-size: 18px;
            font-weight: 900;
            padding: 18px;
            width: 100%;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            box-shadow: 0 6px 20px rgba(97, 97, 255, 0.3);
            transition: all 0.35s ease;
            margin-top: 20px;
            animation: pulse-primary 3s infinite;
        }

        .big-sos-btn:hover  { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(97, 97, 255, 0.4); }
        .big-sos-btn:active { transform: translateY(-1px); }

        @keyframes pulse-primary {
            0%, 100% { box-shadow: 0 6px 20px rgba(97, 97, 255, 0.3); }
            50%       { box-shadow: 0 6px 35px rgba(97, 97, 255, 0.5); }
        }

        .footer-note { text-align: center; margin-top: 40px; color: var(--gray-600); font-size: 14px; font-style: italic; }

        .reports-section { margin-top: 60px; padding-top: 40px; border-top: 3px solid var(--gray-200); }

        .reports-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; margin-top: 30px; }

        .report-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 15px rgba(97, 97, 255, 0.08);
            border: 1px solid rgba(97, 97, 255, 0.1);
            transition: all 0.3s ease;
        }

        .report-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(97, 97, 255, 0.12); }

        .report-card-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px; }

        .report-card-type {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 18px;
            font-weight: 900;
            margin: 0;
        }

        .report-status { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-pending    { background: #fee2e2; color: #991b1b; }
        .status-responding { background: #fef3c7; color: #92400e; }
        .status-resolved   { background: #dcfce7; color: #166534; }

        .report-card-body { margin-bottom: 16px; }
        .report-description { color: var(--gray-700); font-size: 14px; line-height: 1.6; margin-bottom: 12px; }
        .report-meta { font-size: 12px; color: var(--gray-500); display: flex; gap: 12px; flex-wrap: wrap; }

        .report-photo { width: 100%; height: 180px; object-fit: cover; border-radius: 10px; margin-bottom: 16px; }

        .report-actions { display: flex; gap: 8px; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--gray-200); }

        .report-actions a,
        .report-actions button {
            flex: 1; padding: 8px 12px; border: none; border-radius: 8px;
            font-size: 12px; font-weight: 700; text-decoration: none;
            cursor: pointer; text-align: center; transition: all 0.2s ease;
        }

        .report-actions .edit-btn   { background: rgba(97, 97, 255, 0.1); color: var(--purple); }
        .report-actions .edit-btn:hover   { background: var(--purple); color: white; }
        .report-actions .delete-btn { background: rgba(239, 68, 68, 0.1); color: var(--error); }
        .report-actions .delete-btn:hover { background: var(--error); color: white; }

        .empty-reports { text-align: center; padding: 60px 20px; color: var(--gray-500); }
        .empty-reports i { font-size: 48px; margin-bottom: 15px; opacity: 0.5; }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal.active { display: flex; align-items: center; justify-content: center; }

        .modal-content { background: white; padding: 40px; border-radius: 20px; max-width: 680px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }

        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .modal-header h2 { color: var(--navy); font-size: 24px; font-weight: 800; margin: 0; }
        .modal-close { background: none; border: none; font-size: 28px; cursor: pointer; color: var(--gray-600); }
        .modal-close:hover { color: var(--navy); }

        @media (max-width: 768px) {
            .reports-grid { grid-template-columns: 1fr; }
            .report-form { padding: 25px; }
            .modal-content { width: 95%; padding: 25px; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1>Report Emergency & Manage Reports</h1>
        <p>Submit a new emergency report or manage your previous reports</p>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success" style="max-width: 680px; margin: 30px auto;">
            ✓ <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error" style="max-width: 680px; margin: 30px auto;">
            ✗ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- REPORT FORM -->
    <div class="report-form">
        <h2 style="color: var(--navy); margin-top: 0;">🚨 Report New Emergency</h2>
        <form method="POST" id="emergencyForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create">

            <!-- Type of Emergency -->
            <div class="form-group">
                <label>Type of Emergency *</label>
                <select name="type" id="emergencyType" required onchange="handleTypeChange(this.value)">
                    <option value="">-- Select Emergency Type --</option>
                    <option value="Fire">Fire</option>
                    <option value="Flood">Flood / Heavy Rain</option>
                    <option value="Earthquake">Earthquake</option>
                    <option value="Landslide">Landslide</option>
                    <option value="Road Blockage">Road Blockage</option>
                </select>
            </div>

            <!-- ── Flood Severity (shown only when Flood is selected) ── -->
            <div id="flood-severity-group">
                <label class="severity-label">
                    <i class="fas fa-water" style="color:#3b82f6;margin-right:6px;"></i>
                    Flood Severity Level *
                </label>
                <select name="flood_severity" id="floodSeveritySelect" required style="display: none;">
                    <option value="">-- Select Severity --</option>
                    <option value="Knee height">Knee height - Water level up to the knee, walking is difficult</option>
                    <option value="Waist height">Waist height - Water level up to the waist, movement very dangerous</option>
                    <option value="Above head level">Above head level - Extreme flood, immediate evacuation required</option>
                </select>
            </div>
            <!-- ── end flood severity ── -->

            <!-- ── Fire Severity (shown only when Fire is selected) ── -->
            <div id="fire-severity-group">
                <label class="severity-label">
                    <i class="fas fa-fire" style="color:#ef4444;margin-right:6px;"></i>
                    Fire Severity Level *
                </label>
                <select name="fire_severity" id="fireSeveritySelect" required style="display: none;">
                    <option value="">-- Select Severity --</option>
                    <option value="Level 1 Controllable">Level 1 Controllable - Small localized fire</option>
                    <option value="Level 2 Spreading">Level 2 Spreading - Structural fire</option>
                    <option value="Level 3 Out of Control">Level 3 Out of Control - Massive conflagration</option>
                </select>
            </div>
            <!-- ── end fire severity ── -->

            <!-- ── Earthquake Severity (shown only when Earthquake is selected) ── -->
            <div id="earthquake-severity-group">
                <label class="severity-label">
                    <i class="fas fa-house-crack" style="color:#f59e0b;margin-right:6px;"></i>
                    Earthquake Severity Level *
                </label>
                <select name="earthquake_severity" id="earthquakeSeveritySelect" required style="display: none;">
                    <option value="">-- Select Severity --</option>
                    <option value="Level 1 Weak">Level 1 Weak - Objects shake or rattle</option>
                    <option value="Level 2 Strong">Level 2 Strong - Difficulty standing</option>
                    <option value="Level 3 Destructive">Level 3 Destructive - Structural failure</option>
                </select>
            </div>
            <!-- ── end earthquake severity ── -->

            <!-- ── Landslide Severity (shown only when Landslide is selected) ── -->
            <div id="landslide-severity-group">
                <label class="severity-label">
                    <i class="fas fa-mountain" style="color:#8b5cf6;margin-right:6px;"></i>
                    Landslide Severity Level *
                </label>
                <select name="landslide_severity" id="landslideSeveritySelect" required style="display: none;">
                    <option value="">-- Select Severity --</option>
                    <option value="Level 1 Minor">Level 1 Minor - Small debris/rockfall</option>
                    <option value="Level 2 Significant">Level 2 Significant - Active movement</option>
                    <option value="Level 3 Major">Level 3 Major - Massive displacement</option>
                </select>
            </div>
            <!-- ── end landslide severity ── -->

            <!-- ── Road Blockage Severity (shown only when Road Blockage is selected) ── -->
            <div id="roadblock-severity-group">
                <label class="severity-label">
                    <i class="fas fa-road-barrier" style="color:#6b7280;margin-right:6px;"></i>
                    Road Blockage Severity Level *
                </label>
                <select name="roadblock_severity" id="roadblockSeveritySelect" required style="display: none;">
                    <option value="">-- Select Severity --</option>
                    <option value="Level 1 Partial">Level 1 Partial - Slow movement</option>
                    <option value="Level 2 Limited">Level 2 Limited - Small vehicles only</option>
                    <option value="Level 3 Total">Level 3 Total - No passage</option>
                </select>
            </div>
            <!-- ── end road blockage severity ── -->

            <div class="form-group">
                <label>Describe the Situation *</label>
                <textarea name="desc" placeholder="What is happening? How many people are affected? Any injuries? Be as specific as possible." required></textarea>
            </div>

            <div class="form-group">
                <label>Add Photo (Optional)</label>
                <div class="photo-upload" onclick="document.getElementById('photoInput').click();">
                    <input type="file" id="photoInput" name="photo" accept="image/*">
                    <div class="photo-upload-text">
                        <i class="fas fa-camera" style="font-size: 24px; margin-bottom: 8px; display: block; color: var(--purple);"></i>
                        <p style="margin: 8px 0;">Click to upload or drag & drop</p>
                        <small style="color: var(--gray-500);">PNG, JPG, GIF or WebP up to 5MB</small>
                    </div>
                </div>
                <div class="photo-preview" id="photoPreview"></div>
            </div>

            <div class="form-group">
                <label>Your Exact Location *</label>
                <div class="location-info" id="locationStatus">
                    <i class="fas fa-spinner fa-spin"></i> Detecting your location...
                </div>
                <input type="hidden" name="lat" id="lat">
                <input type="hidden" name="lng" id="lng">
                <input type="text" name="address" id="addressInput" placeholder="Address will be auto-filled from GPS..." required>
            </div>

            <button type="submit" class="big-sos-btn" id="submitBtn">
                <i class="fas fa-exclamation-triangle"></i> Send Emergency Alert Now
            </button>
        </form>
    </div>

    <div class="footer-note">
        <p>This alert will be sent immediately to all nearby responders and authorities.</p>
        <p>Your safety is our highest priority.</p>
    </div>

    <!-- REPORTS LIST -->
    <div class="reports-section">
        <h2 style="color: var(--navy); text-align: center; margin: 0 0 30px;">📋 Your Emergency Reports</h2>
        
        <?php
        $stmt = $pdo->prepare("SELECT * FROM emergencies WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $reports = $stmt->fetchAll();

        if (count($reports) > 0):
        ?>
            <div class="reports-grid">
                <?php foreach ($reports as $report):
                    $statusClass = 'status-' . $report['status'];
                    $statusText = ucfirst(str_replace('_', ' ', $report['status']));
                ?>
                    <div class="report-card">
                        <div class="report-card-header">
                            <h3 class="report-card-type"><?= htmlspecialchars($report['type']) ?></h3>
                            <span class="report-status <?= htmlspecialchars($statusClass) ?>">
                                <?= htmlspecialchars($statusText) ?>
                            </span>
                        </div>

                        <?php if ($report['photo']): ?>
                            <img src="../uploads/emergency-photos/<?= htmlspecialchars($report['photo']) ?>" alt="Emergency photo" class="report-photo">
                        <?php endif; ?>

                        <div class="report-card-body">
                            <p class="report-description">
                                <?= htmlspecialchars(substr($report['description'], 0, 150)) ?>
                                <?php if (strlen($report['description']) > 150): ?>...<?php endif; ?>
                            </p>
                            <div class="report-meta">
                                <span><i class="fas fa-calendar"></i> <?= date('M j, Y g:i A', strtotime($report['created_at'])) ?></span>
                                <?php if ($report['latitude'] && $report['longitude']): ?>
                                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($report['address']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="report-actions">
                            <button class="edit-btn" onclick="editReport(<?= $report['id'] ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <a href="?delete=<?= $report['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure? This cannot be undone.');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-reports">
                <i class="fas fa-inbox"></i>
                <p><strong>No emergency reports yet</strong></p>
                <p style="color: var(--gray-500);">When you report an emergency, it will appear here.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- EDIT MODAL -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Emergency Report</h2>
                <button class="modal-close" onclick="closeEditModal();">&times;</button>
            </div>
            <form method="POST" id="editForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="emergency_id" id="editEmergencyId">

                <div class="form-group">
                    <label>Type of Emergency *</label>
                    <select name="type" id="editType" required onchange="handleEditTypeChange(this.value)">
                        <option value="">-- Select Emergency Type --</option>
                        <option value="Fire">Fire</option>
                        <option value="Flood">Flood / Heavy Rain</option>
                        <option value="Earthquake">Earthquake</option>
                        <option value="Landslide">Landslide</option>
                        <option value="Road Blockage">Road Blockage</option>
                        <option value="Other">Other Emergency</option>
                    </select>
                </div>

                <!-- Edit modal flood severity -->
                <div id="edit-flood-severity-group" style="display:none; margin-top:-10px; margin-bottom:24px;">
                    <label class="severity-label">
                        <i class="fas fa-water" style="color:#3b82f6;margin-right:6px;"></i>
                        Flood Severity Level *
                    </label>
                    <select name="flood_severity" id="editFloodSeveritySelect" required style="display: none;">
                        <option value="">-- Select Severity --</option>
                        <option value="Knee height">Knee height - Water level up to the knee, walking is difficult</option>
                        <option value="Waist height">Waist height - Water level up to the waist, movement very dangerous</option>
                        <option value="Above head level">Above head level - Extreme flood, immediate evacuation required</option>
                    </select>
                </div>

                <!-- Edit modal fire severity -->
                <div id="edit-fire-severity-group" style="display:none; margin-top:-10px; margin-bottom:24px;">
                    <label class="severity-label">
                        <i class="fas fa-fire" style="color:#ef4444;margin-right:6px;"></i>
                        Fire Severity Level *
                    </label>
                    <select name="fire_severity" id="editFireSeveritySelect" required style="display: none;">
                        <option value="">-- Select Severity --</option>
                        <option value="Level 1 Controllable">Level 1 Controllable - Small localized fire</option>
                        <option value="Level 2 Spreading">Level 2 Spreading - Structural fire</option>
                        <option value="Level 3 Out of Control">Level 3 Out of Control - Massive conflagration</option>
                    </select>
                </div>

                <!-- Edit modal earthquake severity -->
                <div id="edit-earthquake-severity-group" style="display:none; margin-top:-10px; margin-bottom:24px;">
                    <label class="severity-label">
                        <i class="fas fa-house-crack" style="color:#f59e0b;margin-right:6px;"></i>
                        Earthquake Severity Level *
                    </label>
                    <select name="earthquake_severity" id="editEarthquakeSeveritySelect" required style="display: none;">
                        <option value="">-- Select Severity --</option>
                        <option value="Level 1 Weak">Level 1 Weak - Objects shake or rattle</option>
                        <option value="Level 2 Strong">Level 2 Strong - Difficulty standing</option>
                        <option value="Level 3 Destructive">Level 3 Destructive - Structural failure</option>
                    </select>
                </div>

                <!-- Edit modal landslide severity -->
                <div id="edit-landslide-severity-group" style="display:none; margin-top:-10px; margin-bottom:24px;">
                    <label class="severity-label">
                        <i class="fas fa-mountain" style="color:#8b5cf6;margin-right:6px;"></i>
                        Landslide Severity Level *
                    </label>
                    <select name="landslide_severity" id="editLandslideSeveritySelect" required style="display: none;">
                        <option value="">-- Select Severity --</option>
                        <option value="Level 1 Minor">Level 1 Minor - Small debris/rockfall</option>
                        <option value="Level 2 Significant">Level 2 Significant - Active movement</option>
                        <option value="Level 3 Major">Level 3 Major - Massive displacement</option>
                    </select>
                </div>

                <!-- Edit modal roadblock severity -->
                <div id="edit-roadblock-severity-group" style="display:none; margin-top:-10px; margin-bottom:24px;">
                    <label class="severity-label">
                        <i class="fas fa-road-barrier" style="color:#6b7280;margin-right:6px;"></i>
                        Road Blockage Severity Level *
                    </label>
                    <select name="roadblock_severity" id="editRoadblockSeveritySelect" required style="display: none;">
                        <option value="">-- Select Severity --</option>
                        <option value="Level 1 Partial">Level 1 Partial - Slow movement</option>
                        <option value="Level 2 Limited">Level 2 Limited - Small vehicles only</option>
                        <option value="Level 3 Total">Level 3 Total - No passage</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Describe the Situation *</label>
                    <textarea name="desc" id="editDesc" placeholder="Describe the emergency..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Add or Update Photo (Optional)</label>
                    <div class="photo-upload" onclick="document.getElementById('editPhotoInput').click();">
                        <input type="file" id="editPhotoInput" name="photo" accept="image/*">
                        <div class="photo-upload-text">
                            <i class="fas fa-camera" style="font-size: 24px; margin-bottom: 8px; display: block; color: var(--purple);"></i>
                            <p style="margin: 8px 0;">Click to upload a new photo</p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Your Exact Location *</label>
                    <input type="hidden" name="lat" id="editLat">
                    <input type="hidden" name="lng" id="editLng">
                    <input type="text" name="address" id="editAddress" placeholder="Address..." required>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 25px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal();" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ── Severity handling — create form ──────────────────────────────────────────────
function handleTypeChange(val) {
    // Hide all severity groups
    const allGroups = ['flood-severity-group', 'fire-severity-group', 'earthquake-severity-group', 'landslide-severity-group', 'roadblock-severity-group'];
    const allSelects = ['floodSeveritySelect', 'fireSeveritySelect', 'earthquakeSeveritySelect', 'landslideSeveritySelect', 'roadblockSeveritySelect'];

    allGroups.forEach(id => {
        const group = document.getElementById(id);
        group.classList.remove('visible');
    });

    allSelects.forEach(id => {
        const select = document.getElementById(id);
        select.style.display = 'none';
        select.required = false;
        select.value = '';
    });

    // Show relevant severity group
    if (val === 'Flood') {
        document.getElementById('flood-severity-group').classList.add('visible');
        document.getElementById('floodSeveritySelect').style.display = 'block';
        document.getElementById('floodSeveritySelect').required = true;
    } else if (val === 'Fire') {
        document.getElementById('fire-severity-group').classList.add('visible');
        document.getElementById('fireSeveritySelect').style.display = 'block';
        document.getElementById('fireSeveritySelect').required = true;
    } else if (val === 'Earthquake') {
        document.getElementById('earthquake-severity-group').classList.add('visible');
        document.getElementById('earthquakeSeveritySelect').style.display = 'block';
        document.getElementById('earthquakeSeveritySelect').required = true;
    } else if (val === 'Landslide') {
        document.getElementById('landslide-severity-group').classList.add('visible');
        document.getElementById('landslideSeveritySelect').style.display = 'block';
        document.getElementById('landslideSeveritySelect').required = true;
    } else if (val === 'Road Blockage') {
        document.getElementById('roadblock-severity-group').classList.add('visible');
        document.getElementById('roadblockSeveritySelect').style.display = 'block';
        document.getElementById('roadblockSeveritySelect').required = true;
    }
}

// ── Severity handling — edit modal ───────────────────────────────────────────────
function handleEditTypeChange(val) {
    // Hide all severity groups
    const allGroups = ['edit-flood-severity-group', 'edit-fire-severity-group', 'edit-earthquake-severity-group', 'edit-landslide-severity-group', 'edit-roadblock-severity-group'];
    const allSelects = ['editFloodSeveritySelect', 'editFireSeveritySelect', 'editEarthquakeSeveritySelect', 'editLandslideSeveritySelect', 'editRoadblockSeveritySelect'];

    allGroups.forEach(id => {
        document.getElementById(id).style.display = 'none';
    });

    allSelects.forEach(id => {
        const select = document.getElementById(id);
        select.style.display = 'none';
        select.required = false;
        select.value = '';
    });

    // Show relevant severity group
    if (val === 'Flood') {
        document.getElementById('edit-flood-severity-group').style.display = 'block';
        document.getElementById('editFloodSeveritySelect').style.display = 'block';
        document.getElementById('editFloodSeveritySelect').required = true;
    } else if (val === 'Fire') {
        document.getElementById('edit-fire-severity-group').style.display = 'block';
        document.getElementById('editFireSeveritySelect').style.display = 'block';
        document.getElementById('editFireSeveritySelect').required = true;
    } else if (val === 'Earthquake') {
        document.getElementById('edit-earthquake-severity-group').style.display = 'block';
        document.getElementById('editEarthquakeSeveritySelect').style.display = 'block';
        document.getElementById('editEarthquakeSeveritySelect').required = true;
    } else if (val === 'Landslide') {
        document.getElementById('edit-landslide-severity-group').style.display = 'block';
        document.getElementById('editLandslideSeveritySelect').style.display = 'block';
        document.getElementById('editLandslideSeveritySelect').required = true;
    } else if (val === 'Road Blockage') {
        document.getElementById('edit-roadblock-severity-group').style.display = 'block';
        document.getElementById('editRoadblockSeveritySelect').style.display = 'block';
        document.getElementById('editRoadblockSeveritySelect').required = true;
    }
}

// ── Validate severity before submit ─────────────────────────────────────
document.getElementById('emergencyForm').addEventListener('submit', function(e) {
    const type = document.getElementById('emergencyType').value;
    let severitySelect = null;
    let severityName = '';

    if (type === 'Flood') {
        severitySelect = document.getElementById('floodSeveritySelect');
        severityName = 'flood';
    } else if (type === 'Fire') {
        severitySelect = document.getElementById('fireSeveritySelect');
        severityName = 'fire';
    } else if (type === 'Earthquake') {
        severitySelect = document.getElementById('earthquakeSeveritySelect');
        severityName = 'earthquake';
    } else if (type === 'Landslide') {
        severitySelect = document.getElementById('landslideSeveritySelect');
        severityName = 'landslide';
    } else if (type === 'Road Blockage') {
        severitySelect = document.getElementById('roadblockSeveritySelect');
        severityName = 'road blockage';
    }

    if (severitySelect && !severitySelect.value) {
        e.preventDefault();
        alert(`Please select a ${severityName} severity level.`);
        severitySelect.focus();
        severitySelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});

// ── Geolocation ───────────────────────────────────────────────────────────────
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        position => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
            document.getElementById('editLat').value = lat;
            document.getElementById('editLng').value = lng;
            document.getElementById('locationStatus').innerHTML = 
                `<i class="fas fa-check-circle" style="color:var(--success);margin-right:8px;"></i><strong>Location acquired!</strong><br>Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(r => r.json())
                .then(data => {
                    const addr = data.display_name || "Location detected";
                    document.getElementById('addressInput').value = addr;
                    document.getElementById('editAddress').value = addr;
                })
                .catch(() => {
                    document.getElementById('locationStatus').innerHTML += "<br>Could not get address, but GPS coordinates saved.";
                });
        },
        error => {
            document.getElementById('locationStatus').innerHTML = 
                '<i class="fas fa-times-circle" style="color:var(--error);margin-right:8px;"></i><strong>Location access denied.</strong><br>Please manually enter your address below.';
        },
        { timeout: 15000, enableHighAccuracy: true }
    );
}

// ── Photo preview ─────────────────────────────────────────────────────────────
document.getElementById('photoInput')?.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photoPreview').innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        };
        reader.readAsDataURL(file);
    }
});

// ── Edit report modal ─────────────────────────────────────────────────────────
function editReport(emergencyId) {
    const reports = <?= json_encode($reports) ?>;
    const report = reports.find(r => r.id == emergencyId);
    if (report) {
        document.getElementById('editEmergencyId').value = report.id;
        document.getElementById('editDesc').value        = report.description;
        document.getElementById('editLat').value         = report.latitude;
        document.getElementById('editLng').value         = report.longitude;
        document.getElementById('editAddress').value     = report.address;

        // Detect existing type and severity
        const rawType = report.type || '';
        let baseType = rawType;
        let severity = '';

        // Check if type contains severity in parentheses
        const match = rawType.match(/^(.+?)\s*\(([^)]+)\)$/);
        if (match) {
            baseType = match[1].trim();
            severity = match[2].trim();
        }

        // Set the type and handle severity display
        document.getElementById('editType').value = baseType;
        handleEditTypeChange(baseType);

        // Pre-select severity if it exists
        if (severity) {
            if (baseType === 'Flood') {
                document.getElementById('editFloodSeveritySelect').value = severity;
            } else if (baseType === 'Fire') {
                document.getElementById('editFireSeveritySelect').value = severity;
            } else if (baseType === 'Earthquake') {
                document.getElementById('editEarthquakeSeveritySelect').value = severity;
            } else if (baseType === 'Landslide') {
                document.getElementById('editLandslideSeveritySelect').value = severity;
            } else if (baseType === 'Road Blockage') {
                document.getElementById('editRoadblockSeveritySelect').value = severity;
            }
        }

        document.getElementById('editModal').classList.add('active');
    }
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}

document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>

<script src="../assets/js/chatbot.js"></script>
</body>
</html>