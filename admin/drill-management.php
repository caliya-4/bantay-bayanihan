<?php 
session_start(); 
require '../db_connect.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','responder'])) {
    header("Location: ../login.php"); 
    exit;
}

// ── Full Baguio barangay list ─────────────────────────────────────────────────
$allBarangays = [
    "A. Bonifacio-Caguioa-Rimando (ABCR)",
    "Abanao-Zandueta-Kayong-Chugum-Otek (AZKCO)",
    "Alfonso Tabora", "Ambiong", "Andres Bonifacio (Lower Bokawkan)",
    "Apugan-Loakan", "Asin Road", "Atok Trail",
    "Aurora Hill Proper (Malvar-Sgt. Floresca)",
    "Aurora Hill, North Central", "Aurora Hill, South Central",
    "Bagong Lipunan (Market Area)", "Bakakeng Central", "Bakakeng North",
    "Bal-Marcoville (Marcoville)", "Balsigan", "Bayan Park East",
    "Bayan Park Village", "Bayan Park West (Bayan Park, Leonila Hill)",
    "BGH Compound", "Brookside", "Brookspoint",
    "Cabinet Hill-Teacher's Camp", "Camdas Subdivision",
    "Camp 7", "Camp 8", "Camp Allen", "Campo Filipino",
    "City Camp Central", "City Camp Proper", "Country Club Village",
    "Cresencia Village", "Dagsian, Lower", "Dagsian, Upper",
    "Dizon Subdivision", "Dominican Hill-Mirador", "Dontogan", "DPS Compound",
    "Engineers' Hill", "Fairview Village",
    "Ferdinand (Happy Homes-Campo Sioco)", "Fort del Pilar",
    "Gabriela Silang",
    "General Emilio F. Aguinaldo (Quirino-Magsaysay, Lower)",
    "General Luna, Upper", "General Luna, Lower", "Gibraltar",
    "Greenwater Village", "Guisad Central", "Guisad Sorong",
    "Happy Hollow", "Happy Homes (Happy Homes-Lucban)",
    "Harrison-Claudio Carantes", "Hillside",
    "Holy Ghost Extension", "Holy Ghost Proper",
    "Honeymoon (Honeymoon-Holy Ghost)",
    "Imelda R. Marcos (La Salle)", "Imelda Village", "Irisan",
    "Kabayanihan", "Kagitingan", "Kayang Extension", "Kayang-Hilltop", "Kias",
    "Legarda-Burnham-Kisad", "Liwanag-Loakan", "Loakan Proper", "Lopez Jaena",
    "Lourdes Subdivision Extension", "Lourdes Subdivision, Lower",
    "Lourdes Subdivision, Proper", "Lualhati", "Lucnab",
    "Magsaysay Private Road", "Magsaysay, Lower", "Magsaysay, Upper",
    "Malcolm Square-Perfecto (Jose Abad Santos)", "Manuel A. Roxas",
    "Market Subdivision, Upper",
    "Middle Quezon Hill Subdivision (Quezon Hill Middle)",
    "Military Cut-off", "Mines View Park",
    "Modern Site, East", "Modern Site, West", "MRR-Queen of Peace",
    "New Lucban", "Outlook Drive", "Pacdal", "Padre Burgos", "Padre Zamora",
    "Palma-Urbano (Cariño-Palma)", "Phil-Am", "Pinget",
    "Pinsao Pilot Project", "Pinsao Proper", "Poliwes", "Pucsusan",
    "Quezon Hill Proper", "Quezon Hill, Upper",
    "Quirino Hill, East", "Quirino Hill, Lower",
    "Quirino Hill, Middle", "Quirino Hill, West",
    "Quirino-Magsaysay, Upper (Upper QM)", "Rizal Monument Area",
    "Rock Quarry, Lower", "Rock Quarry, Middle", "Rock Quarry, Upper",
    "Saint Joseph Village", "Salud Mitra", "San Antonio Village",
    "San Luis Village", "San Roque Village", "San Vicente",
    "Sanitary Camp, North", "Sanitary Camp, South",
    "Santa Escolastica", "Santo Rosario", "Santo Tomas Proper",
    "Santo Tomas School Area", "Scout Barrio", "Session Road Area",
    "Slaughter House Area (Santo Niño Slaughter)",
    "SLU-SVP Housing Village", "South Drive",
    "Teodora Alonzo", "Trancoville", "Victoria Village"
];
sort($allBarangays);

$alertMsg = '';

// ── CREATE DRILL ──────────────────────────────────────────────────────────────
if (isset($_POST['create'])) {
    $title      = trim($_POST['title'] ?? '');
    $desc       = trim($_POST['desc']  ?? '');
    $inst       = trim($_POST['inst']  ?? '');
    $dur        = max(5, min(240, (int)($_POST['dur'] ?? 30)));
    $status     = ($_POST['status'] === 'published') ? 'published' : 'draft';
    $barangay   = trim($_POST['barangay']   ?? '');
    $drill_date = trim($_POST['drill_date'] ?? '');
    $drill_place= trim($_POST['drill_place']?? '');

    if ($title === '') {
        $alertMsg = "<div class='alert alert-error'>✖ Title is required.</div>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO drills 
            (title, description, instructions, duration_minutes, status, created_by, barangay, drill_date, drill_place) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $title, $desc, $inst, $dur, $status,
            $_SESSION['user_id'],
            $barangay ?: null,
            $drill_date ?: null,
            $drill_place ?: null
        ]);
        $alertMsg = "<div class='alert alert-success'>✓ Mission created successfully!</div>";
    }
}

// ── UPDATE DRILL ──────────────────────────────────────────────────────────────
if (isset($_POST['update'])) {
    $id         = (int)($_POST['edit_id'] ?? 0);
    $title      = trim($_POST['title']      ?? '');
    $desc       = trim($_POST['desc']       ?? '');
    $inst       = trim($_POST['inst']       ?? '');
    $dur        = max(5, min(240, (int)($_POST['dur'] ?? 30)));
    $status     = ($_POST['status'] === 'published') ? 'published' : 'draft';
    $barangay   = trim($_POST['barangay']   ?? '');
    $drill_date = trim($_POST['drill_date'] ?? '');
    $drill_place= trim($_POST['drill_place']?? '');

    if ($title === '') {
        $alertMsg = "<div class='alert alert-error'>✖ Title is required.</div>";
    } else {
        $stmt = $pdo->prepare("UPDATE drills SET 
            title=?, description=?, instructions=?, duration_minutes=?,
            status=?, barangay=?, drill_date=?, drill_place=?
            WHERE id=? AND created_by=?");
        $stmt->execute([
            $title, $desc, $inst, $dur, $status,
            $barangay ?: null,
            $drill_date ?: null,
            $drill_place ?: null,
            $id, $_SESSION['user_id']
        ]);
        $alertMsg = "<div class='alert alert-success'>✓ Mission updated successfully!</div>";
        // Redirect to avoid re-submission, but keep alert via session
        header("Location: drill-management.php?updated=1");
        exit;
    }
}

// ── DELETE DRILL ──────────────────────────────────────────────────────────────
if (isset($_GET['del']) && is_numeric($_GET['del'])) {
    $id = (int)$_GET['del'];
    $pdo->prepare("DELETE FROM drills WHERE id = ? AND created_by = ?")
        ->execute([$id, $_SESSION['user_id']]);
    $alertMsg = "<div class='alert alert-success'>✓ Mission permanently deleted.</div>";
}

if (isset($_GET['updated'])) {
    $alertMsg = "<div class='alert alert-success'>✓ Mission updated successfully!</div>";
}

// ── FETCH DRILL FOR EDITING ───────────────────────────────────────────────────
$editDrill = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM drills WHERE id = ? AND created_by = ?");
    $stmt->execute([(int)$_GET['edit'], $_SESSION['user_id']]);
    $editDrill = $stmt->fetch(PDO::FETCH_ASSOC);
}

// helper to render barangay <select>
function barangaySelect(array $list, string $name, string $selected = '', string $extraStyle = ''): string {
    $html  = "<select name=\"{$name}\" class=\"form-control\" style=\"{$extraStyle}\">";
    $html .= "<option value=\"\">— All Barangays —</option>";
    foreach ($list as $b) {
        $sel   = ($selected === $b) ? ' selected' : '';
        $esc   = htmlspecialchars($b, ENT_QUOTES);
        $html .= "<option value=\"{$esc}\"{$sel}>{$esc}</option>";
    }
    $html .= "</select>";
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drills & Trainings | Bantay Bayanihan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/design-system.css">
    <style>
        .page-header h1 { font-size: clamp(28px, 5vw, 42px); }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        /* ── Mission card ──────────────────────────────────────── */
        .mission-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 35px rgba(97,97,255,.1);
            padding: 32px;
            margin-bottom: 25px;
            border: 2px solid rgba(97,97,255,.08);
            position: relative;
            transition: all .3s ease;
            overflow: hidden;
        }

        .mission-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 50px rgba(97,97,255,.18);
        }

        .mission-card h3 {
            color: var(--navy);
            font-size: 22px;
            margin: 0 0 14px 0;
            font-weight: 800;
        }

        .mission-card p { color: var(--gray-700); margin: 6px 0; line-height: 1.6; }

        /* LIVE ribbon */
        .ribbon {
            position: absolute;
            top: 18px; right: -55px;
            background: var(--gradient-accent);
            color: white;
            padding: 12px 70px;
            transform: rotate(45deg);
            font-weight: 900;
            font-size: 13px;
            box-shadow: 0 4px 16px rgba(255,0,101,.4);
            z-index: 10;
        }

        /* Barangay tag pill */
        .brgy-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(97,97,255,.1);
            color: #6161ff;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .brgy-tag.all {
            background: rgba(16,185,129,.1);
            color: #10b981;
        }

        /* Participant badge */
        .participant-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #6161ff15, #00167a10);
            border: 1.5px solid #6161ff40;
            color: #00167a;
            font-size: 13px;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 20px;
            margin-bottom: 10px;
        }

        .participant-badge i { font-size: 12px; }

        .participant-status {
            font-size: 11px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 10px;
        }

        .participant-status.completed { background: rgba(16,185,129,.2); color: #059669; }
        .participant-status.in-progress { background: rgba(251,191,36,.2); color: #d97706; }
        .participant-status.not-started { background: rgba(100,116,139,.2); color: #64748b; }

        /* Ownership tag */
        .owner-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 10px;
        }
        .owner-tag.mine   { background: rgba(16,185,129,.12); color: #059669; }
        .owner-tag.others { background: rgba(100,116,139,.1);  color: #64748b; }

        /* Date/place meta row */
        .meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin: 10px 0;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #64748b;
            font-weight: 600;
        }

        .meta-item i { color: #6161ff; font-size: 12px; }

        .mission-actions {
            margin-top: 18px;
            padding-top: 14px;
            border-top: 2px solid var(--gray-100);
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .mission-actions a {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            transition: all .2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .mission-actions a.view-leaderboard { color: var(--purple); border: 2px solid var(--purple); }
        .mission-actions a.view-leaderboard:hover { background: var(--purple); color: white; }
        .mission-actions a.edit { color: var(--info); border: 2px solid var(--info); }
        .mission-actions a.edit:hover { background: var(--info); color: white; }
        .mission-actions a.delete { color: var(--error); border: 2px solid var(--error); }
        .mission-actions a.delete:hover { background: var(--error); color: white; }

        /* ── Edit panel ────────────────────────────────────────── */
        .edit-panel {
            background: #fff8f0;
            border: 2px solid #f59e0b;
            border-radius: 20px;
            padding: 28px 32px;
            margin-bottom: 28px;
        }

        .edit-panel h3 {
            margin: 0 0 20px;
            color: #92400e;
            font-size: 18px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Form helpers ──────────────────────────────────────── */
        .mission-card label,
        .edit-panel label { display: block; margin-bottom: 6px; font-weight: 700; font-size: 13px; color: #0f172a; }
        .form-control { width: 100%; padding: 10px 14px; border-radius: 10px; border: 2px solid rgba(15,23,42,.08); font-size: 14px; box-sizing: border-box; transition: border-color .2s; }
        .form-control:focus { outline: none; border-color: #6161ff; box-shadow: 0 0 0 3px rgba(97,97,255,.1); }

        /* ── Leaderboard ───────────────────────────────────────── */
        .leaderboard-header {
            background: linear-gradient(135deg, #6161ff15, #00167a08);
            border-radius: 24px;
            padding: 48px;
            margin: 40px 0;
            border: 2px solid #6161ff30;
            position: relative;
        }

        .leaderboard-header::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6161ff, #00167a);
            border-radius: 24px 24px 0 0;
        }

        .leaderboard-header::after {
            content: '';
            position: absolute;
            bottom: 24px; right: 24px;
            width: 120px; height: 120px;
            background: rgba(97,97,255,.05);
            border-radius: 50%;
            z-index: 0;
        }

        .leaderboard-header h1 {
            color: var(--navy);
            font-size: 36px;
            font-weight: 900;
            margin: 0 0 12px;
            position: relative;
            z-index: 1;
        }

        .drill-subtitle {
            color: #64748b;
            font-size: 16px;
            margin: 0 0 20px;
            position: relative;
            z-index: 1;
        }

        .drill-detail-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .drill-detail-pill {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .drill-detail-pill i {
            color: #6161ff;
            font-size: 12px;
        }

        .participant-count-hero {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 16px;
            margin-top: 25px;
            position: relative;
            z-index: 1;
        }

        .count-box {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 18px;
            text-align: center;
        }

        .count-num {
            display: block;
            font-size: 32px;
            font-weight: 900;
            color: #6161ff;
            line-height: 1;
            margin-bottom: 6px;
        }

        .count-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-container {
            background: white;
            border-radius: 16px;
            overflow-x: auto;
            box-shadow: 0 4px 12px rgba(0,0,0,.08);
            margin: 30px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        table thead {
            background: linear-gradient(135deg, #f8f9ff, #fef3f8);
            border-bottom: 2px solid #e2e8f0;
        }

        table th {
            padding: 16px;
            text-align: left;
            font-weight: 700;
            color: #00167a;
        }

        table tbody tr:hover {
            background: #f8f9ff;
        }

        table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f0f1f5;
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .leaderboard-header { padding: 24px 20px; }
            .leaderboard-header h1 { font-size: 22px; }
            .participant-count-hero { grid-template-columns: repeat(2, 1fr); }
        }

        .empty-state {
            text-align: center;
            padding: 60px 30px;
            background: white;
            border-radius: 16px;
            color: #94a3b8;
            border: 2px dashed #e2e8f0;
            margin: 30px 0;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            display: block;
            color: #cbd5e1;
        }

        .empty-state p {
            font-size: 15px;
            font-weight: 600;
            color: #475569;
            margin: 0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all .2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6161ff, #00167a);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(97,97,255,.3);
        }

        .btn-secondary {
            background: white;
            border: 2px solid #e2e8f0;
            color: #00167a;
        }

        .btn-secondary:hover {
            background: #f8f9ff;
            border-color: #cbd5e1;
        }

        @media (max-width: 992px) {
            .form-grid { grid-template-columns: 1fr; }
            .mission-actions { flex-direction: column; }
            .mission-actions a { width: 100%; text-align: center; justify-content: center; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">

<?php echo $alertMsg; ?>

<?php
// ════════════════════════════════════════════════════════
//  LEADERBOARD / PARTICIPANTS VIEW
// ════════════════════════════════════════════════════════
if (isset($_GET['leaderboard']) && is_numeric($_GET['leaderboard'])):
    $drill_id = (int)$_GET['leaderboard'];

    // Fetch full drill details for the header
    $stmt = $pdo->prepare("
        SELECT d.*, u.name AS creator_name
        FROM drills d
        LEFT JOIN users u ON d.created_by = u.id
        WHERE d.id = ?
    ");
    $stmt->execute([$drill_id]);
    $drill = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($drill):

    // Count participants by status
    $countStmt = $pdo->prepare("
        SELECT
            COUNT(*) as total,
            COALESCE(SUM(CASE WHEN status = 'completed'   THEN 1 ELSE 0 END), 0) as completed,
            COALESCE(SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END), 0) as in_progress,
            COALESCE(SUM(CASE WHEN status = 'not_started' THEN 1 ELSE 0 END), 0) as not_started
        FROM drill_participants WHERE drill_id = ?
    ");
    $countStmt->execute([$drill_id]);
    $counts = $countStmt->fetch(PDO::FETCH_ASSOC);

    $totalAll = (int)($counts['total'] ?? 0);
?>

    <!-- Leaderboard header with drill details & participant counts -->
    <div class="leaderboard-header">
        <h1>👥 Participants — <?= htmlspecialchars($drill['title']) ?></h1>
        <p class="drill-subtitle">
            <?= !empty($drill['description']) ? htmlspecialchars($drill['description']) : 'No description provided.' ?>
        </p>

        <div class="drill-detail-pills">
            <span class="drill-detail-pill">
                <i class="fas fa-user-shield"></i> Created by: <?= htmlspecialchars($drill['creator_name'] ?? 'Unknown') ?>
            </span>
            <span class="drill-detail-pill">
                <i class="fas fa-clock"></i> <?= (int)$drill['duration_minutes'] ?> minutes
            </span>
            <span class="drill-detail-pill">
                <i class="fas fa-calendar-alt"></i> <?= date('M d, Y', strtotime($drill['created_at'])) ?>
            </span>
            <?php if(!empty($drill['barangay'])): ?>
            <span class="drill-detail-pill">
                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($drill['barangay']) ?>
            </span>
            <?php endif; ?>
            <?php if(!empty($drill['drill_date'])): ?>
            <span class="drill-detail-pill">
                <i class="fas fa-calendar-check"></i> <?= date('M d, Y', strtotime($drill['drill_date'])) ?>
            </span>
            <?php endif; ?>
            <?php if(!empty($drill['drill_place'])): ?>
            <span class="drill-detail-pill">
                <i class="fas fa-location-dot"></i> <?= htmlspecialchars($drill['drill_place']) ?>
            </span>
            <?php endif; ?>
            <span class="drill-detail-pill">
                <i class="fas fa-circle" style="font-size:9px; color: <?= $drill['status'] === 'published' ? '#4ade80' : '#fbbf24' ?>"></i>
                <?= ucfirst($drill['status']) ?>
            </span>
        </div>

        <div class="participant-count-hero">
            <div class="count-box">
                <span class="count-num"><?= $totalAll ?></span>
                <span class="count-label">Total</span>
            </div>
            <div class="count-box" style="background:rgba(74,222,128,0.2); border-color:rgba(74,222,128,0.4);">
                <span class="count-num"><?= (int)($counts['completed'] ?? 0) ?></span>
                <span class="count-label">Completed</span>
            </div>
            <div class="count-box" style="background:rgba(251,191,36,0.2); border-color:rgba(251,191,36,0.4);">
                <span class="count-num"><?= (int)($counts['in_progress'] ?? 0) ?></span>
                <span class="count-label">In Progress</span>
            </div>
            <div class="count-box" style="background:rgba(248,113,113,0.2); border-color:rgba(248,113,113,0.4);">
                <span class="count-num"><?= (int)($counts['not_started'] ?? 0) ?></span>
                <span class="count-label">Not Started</span>
            </div>
        </div>
    </div>

    <?php
    // Fetch participants - single clean query
    $stmt = $pdo->prepare("
        SELECT * FROM drill_participants 
        WHERE drill_id = ? 
        ORDER BY joined_at DESC
    ");
    $stmt->execute([$drill_id]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ensure all fields exist
    foreach ($participants as &$p) {
        $p['status'] = $p['status'] ?? 'not_started';
        $p['started_at'] = $p['started_at'] ?? null;
        $p['finished_at'] = $p['finished_at'] ?? null;
        $p['entered'] = $p['entered'] ?? 0;
    }

    if (empty($participants)):
    ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p><strong>No participants yet</strong></p>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="text-align:center;">#</th>
                        <th>Participant</th>
                        <th>Email</th>
                        <th style="text-align:center;">Status</th>
                        <th style="text-align:center;">Started</th>
                        <th style="text-align:center;">Finished</th>
                        <th style="text-align:center;">Entered</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rank = 1; foreach ($participants as $p):
                        // Prepare variables
                        $status = $p['status'] ?? 'not_started';
                        $isEntered = (bool)$p['entered'];
                        $hasStarted = !empty($p['started_at']);
                        $isCompleted = ($status === 'completed');
                        $finishedTime = $p['finished_at'] ?? null;
                        
                        // Format dates
                        $started = $p['started_at'] ? date('M d, Y h:i A', strtotime($p['started_at'])) : '—';
                        $finished = $finishedTime ? date('M d, Y h:i A', strtotime($finishedTime)) : '—';
                        
                        // Status badge styling
                        $badgeClass = $isCompleted ? 'badge-success' : ($hasStarted ? 'badge-warning' : 'badge-secondary');
                        $badgeLabel = $isCompleted ? '✓ Completed' : ($hasStarted ? '⏳ In Progress' : '⏸ Not Started');
                        $badgeIcon = $isCompleted ? 'fa-check-circle' : ($hasStarted ? 'fa-spinner fa-spin' : 'fa-hourglass-start');
                    ?>
                    <tr>
                        <!-- Rank -->
                        <td style="text-align:center; font-weight:bold;">#<?= $rank++ ?></td>
                        
                        <!-- Name -->
                        <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                        
                        <!-- Email -->
                        <td><?= htmlspecialchars($p['email']) ?></td>
                        
                        <!-- Status Badge -->
                        <td style="text-align:center;">
                            <span class="badge <?= $badgeClass ?>" style="padding:6px 12px; font-size:11px; font-weight:700;">
                                <i class="fas <?= $badgeIcon ?>"></i> <?= $badgeLabel ?>
                            </span>
                        </td>
                        
                        <!-- Started Time -->
                        <td style="text-align:center; font-size:12px;"><?= $started ?></td>
                        
                        <!-- Finished Time (shows when completed) -->
                        <td style="text-align:center; font-size:12px;">
                            <?php if ($finishedTime): ?>
                                <span style="color:#059669; font-weight:600;">
                                    <i class="fas fa-check-circle"></i> <?= $finished ?>
                                </span>
                            <?php else: ?>
                                <span style="color:#94a3b8;">—</span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Entered Badge -->
                        <td style="text-align:center;">
                            <?php if ($isEntered): ?>
                                <span class="badge badge-success" style="font-size:11px;">
                                    <i class="fas fa-check"></i> Yes
                                </span>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size:11px;">
                                    <i class="fas fa-times"></i> No
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div style="text-align:center;margin-top:30px;">
        <a href="drill-management.php" class="btn btn-secondary">← Back to Drill Management</a>
    </div>

    <?php else: ?>
        <div class="empty-state"><p>Drill not found.</p></div>
    <?php endif; ?>

<?php
// ════════════════════════════════════════════════════════
//  MAIN LIST VIEW
// ════════════════════════════════════════════════════════
else:
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>🎯 Drill Management</h1>
        <p>Create, publish, and manage disaster preparedness drills for Baguio City barangays</p>
    </div>
</div>

<!-- ── EDIT PANEL (shown when ?edit=ID) ──────────────────────────────────── -->
<?php if ($editDrill): ?>
<div class="edit-panel">
    <h3><i class="fas fa-edit"></i> Editing: <?= htmlspecialchars($editDrill['title']) ?></h3>
    <form method="POST">
        <input type="hidden" name="edit_id" value="<?= $editDrill['id'] ?>">

        <div class="form-grid">
            <div>
                <label>Title *</label>
                <input name="title" required class="form-control"
                       value="<?= htmlspecialchars($editDrill['title'], ENT_QUOTES) ?>">
            </div>
            <div>
                <label>Duration (minutes)</label>
                <input type="number" min="5" max="240" name="dur" class="form-control"
                       value="<?= (int)$editDrill['duration_minutes'] ?>">
            </div>
            <div>
                <label><i class="fas fa-map-marker-alt" style="color:#6161ff"></i> Target Barangay</label>
                <?= barangaySelect($allBarangays, 'barangay', $editDrill['barangay'] ?? '') ?>
            </div>
            <div>
                <label><i class="fas fa-calendar-alt" style="color:#6161ff"></i> Drill Date</label>
                <input type="date" name="drill_date" class="form-control"
                       value="<?= htmlspecialchars($editDrill['drill_date'] ?? '', ENT_QUOTES) ?>">
            </div>
            <div>
                <label><i class="fas fa-location-dot" style="color:#6161ff"></i> Drill Place / Venue</label>
                <input type="text" name="drill_place" class="form-control"
                       placeholder="e.g., Burnham Park, Baguio City"
                       value="<?= htmlspecialchars($editDrill['drill_place'] ?? '', ENT_QUOTES) ?>">
            </div>
        </div>

        <div style="margin-top:14px;">
            <label>Description</label>
            <textarea name="desc" class="form-control" rows="3"><?= htmlspecialchars($editDrill['description'] ?? '', ENT_QUOTES) ?></textarea>
        </div>
        <div style="margin-top:12px;">
            <label>Instructions</label>
            <textarea name="inst" class="form-control" rows="2"><?= htmlspecialchars($editDrill['instructions'] ?? '', ENT_QUOTES) ?></textarea>
        </div>

        <div style="margin-top:14px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
            <select name="status" class="form-control" style="max-width:220px;">
                <option value="published" <?= $editDrill['status']==='published' ? 'selected' : '' ?>>Publish Now</option>
                <option value="draft"     <?= $editDrill['status']==='draft'     ? 'selected' : '' ?>>Save as Draft</option>
            </select>
            <button type="submit" name="update" class="btn btn-primary" style="margin-left:auto;">
                <i class="fas fa-save"></i> Save Changes
            </button>
            <a href="drill-management.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- ── CREATE FORM ────────────────────────────────────────────────────────── -->
<?php if (in_array($_SESSION['role'], ['admin','responder'])): ?>
<div class="mission-card card">
    <div class="card-header">
        <h3><i class="fas fa-plus-circle" style="color:#6161ff"></i> Create / Publish Mission</h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-grid">
                <div>
                    <label>Title *</label>
                    <input name="title" required class="form-control"
                           placeholder="e.g., Earthquake Preparedness Drill"
                           value="<?= htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES) ?>">
                </div>
                <div>
                    <label>Duration (minutes)</label>
                    <input type="number" min="5" max="240" name="dur" class="form-control"
                           value="<?= (int)($_POST['dur'] ?? 30) ?>">
                </div>
                <div>
                    <label><i class="fas fa-map-marker-alt" style="color:#6161ff"></i> Target Barangay</label>
                    <?= barangaySelect($allBarangays, 'barangay', $_POST['barangay'] ?? '') ?>
                    <small style="color:#64748b;font-size:11px;margin-top:4px;display:block;">Leave blank to target all barangays</small>
                </div>
                <div>
                    <label><i class="fas fa-calendar-alt" style="color:#6161ff"></i> Drill Date</label>
                    <input type="date" name="drill_date" class="form-control"
                           value="<?= htmlspecialchars($_POST['drill_date'] ?? '', ENT_QUOTES) ?>">
                </div>
                <div>
                    <label><i class="fas fa-location-dot" style="color:#6161ff"></i> Drill Place / Venue</label>
                    <input type="text" name="drill_place" class="form-control"
                           placeholder="e.g., Burnham Park, Baguio City"
                           value="<?= htmlspecialchars($_POST['drill_place'] ?? '', ENT_QUOTES) ?>">
                </div>
            </div>

            <div style="margin-top:14px;">
                <label>Description</label>
                <textarea name="desc" class="form-control" rows="3"
                          placeholder="Brief overview of this drill..."><?= htmlspecialchars($_POST['desc'] ?? '', ENT_QUOTES) ?></textarea>
            </div>
            <div style="margin-top:12px;">
                <label>Instructions</label>
                <textarea name="inst" class="form-control" rows="2"
                          placeholder="Step-by-step instructions for participants..."><?= htmlspecialchars($_POST['inst'] ?? '', ENT_QUOTES) ?></textarea>
            </div>

            <div style="margin-top:14px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                <select name="status" class="form-control" style="max-width:220px;">
                    <option value="published">Publish Now</option>
                    <option value="draft">Save as Draft</option>
                </select>
                <button type="submit" name="create" class="btn btn-primary" style="margin-left:auto;">
                    <i class="fas fa-rocket"></i> Create Mission
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ── FILTERS ─────────────────────────────────────────────────────────────── -->
<div class="mission-card" style="margin-top: 20px;">
    <div class="card-header">
        <h3><i class="fas fa-filter" style="color:#6161ff"></i> Filter Drills</h3>
    </div>
    <div class="card-body">
        <form method="GET" id="filterForm">
            <div class="form-grid">
                <div>
                    <label>Month</label>
                    <select name="month" class="form-control" onchange="this.form.submit()">
                        <option value="">All Months</option>
                        <?php
                        $months = [
                            '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
                            '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
                            '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
                        ];
                        $selectedMonth = $_GET['month'] ?? '';
                        foreach ($months as $num => $name) {
                            $sel = ($selectedMonth === $num) ? 'selected' : '';
                            echo "<option value=\"$num\" $sel>$name</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label>Drill Type</label>
                    <select name="type" class="form-control" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="earthquake" <?= ($_GET['type'] ?? '') === 'earthquake' ? 'selected' : '' ?>>Earthquake</option>
                        <option value="fire" <?= ($_GET['type'] ?? '') === 'fire' ? 'selected' : '' ?>>Fire</option>
                        <option value="flood" <?= ($_GET['type'] ?? '') === 'flood' ? 'selected' : '' ?>>Flood</option>
                        <option value="typhoon" <?= ($_GET['type'] ?? '') === 'typhoon' ? 'selected' : '' ?>>Typhoon</option>
                        <option value="landslide" <?= ($_GET['type'] ?? '') === 'landslide' ? 'selected' : '' ?>>Landslide</option>
                        <option value="general" <?= ($_GET['type'] ?? '') === 'general' ? 'selected' : '' ?>>General</option>
                    </select>
                </div>
                <div>
                    <label>Status</label>
                    <select name="status_filter" class="form-control" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="published" <?= ($_GET['status_filter'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= ($_GET['status_filter'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="archived" <?= ($_GET['status_filter'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
                <div>
                    <label>Barangay</label>
                    <select name="barangay_filter" class="form-control" onchange="this.form.submit()">
                        <option value="">All Barangays</option>
                        <?php
                        $selectedBarangay = $_GET['barangay_filter'] ?? '';
                        foreach ($allBarangays as $brgy) {
                            $sel = ($selectedBarangay === $brgy) ? 'selected' : '';
                            echo "<option value=\"$brgy\" $sel>$brgy</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ── MISSIONS LIST ──────────────────────────────────────────────────────── -->
<?php
// Fetch drills WITH participant counts
$query = "
    SELECT 
        d.*, 
        u.name AS creator,
        COUNT(DISTINCT dp.id) AS total_participants,
        COALESCE(SUM(CASE WHEN dp.status = 'completed'   THEN 1 ELSE 0 END), 0) AS completed_count,
        COALESCE(SUM(CASE WHEN dp.status = 'in_progress' THEN 1 ELSE 0 END), 0) AS in_progress_count,
        COALESCE(SUM(CASE WHEN dp.status = 'not_started' THEN 1 ELSE 0 END), 0) AS not_started_count,
        CASE WHEN d.drill_date < CURDATE() OR (d.drill_date IS NULL AND d.created_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY)) THEN 1 ELSE 0 END AS is_archived
    FROM drills d 
    JOIN users u ON d.created_by = u.id 
    LEFT JOIN drill_participants dp ON dp.drill_id = d.id
    WHERE 1=1
";

$params = [];

// For responders, restrict to their barangay unless admin
if ($_SESSION['role'] === 'responder' && $myBarangay) {
    $query .= " AND (d.barangay = ? OR d.barangay IS NULL OR d.barangay = '')";
    $params[] = $myBarangay;
}

// Apply filters
$month = $_GET['month'] ?? '';
$type = $_GET['type'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';
$barangay_filter = $_GET['barangay_filter'] ?? '';

if ($month) {
    $query .= " AND MONTH(d.created_at) = ?";
    $params[] = $month;
}

if ($type) {
    $query .= " AND LOWER(d.title) LIKE ?";
    $params[] = '%' . strtolower($type) . '%';
}

if ($status_filter) {
    if ($status_filter === 'archived') {
        $query .= " AND (d.drill_date < CURDATE() OR (d.drill_date IS NULL AND d.created_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY)))";
    } else {
        $query .= " AND d.status = ?";
        $params[] = $status_filter;
    }
}

if ($barangay_filter) {
    $query .= " AND d.barangay = ?";
    $params[] = $barangay_filter;
}

$query .= " GROUP BY d.id ORDER BY CASE WHEN d.status='published' THEN 0 ELSE 1 END, d.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$drills = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($drills)):
?>
    <div class="empty-state" style="max-width:100%;">
        <i class="fas fa-clipboard-list"></i>
        <p><strong>No missions created yet</strong></p>
        <p>Use the form above to create your first drill!</p>
    </div>
<?php else: ?>
    <h2 style="color:#0f172a;font-size:18px;font-weight:800;margin:28px 0 16px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-list" style="color:#6161ff;"></i> All Missions
        <span style="background:rgba(97,97,255,.1);color:#6161ff;border-radius:20px;padding:2px 12px;font-size:13px;"><?= count($drills) ?></span>
    </h2>

    <?php foreach($drills as $d):
        $isOwner = ($d['created_by'] == $_SESSION['user_id']);
        $canEdit = $isOwner || $_SESSION['role'] === 'admin';
        $isArchived = (int)($d['is_archived'] ?? 0);
        $statusBadge = $isArchived ? 'ARCHIVED' : ($d['status'] === 'published' ? 'LIVE' : ucfirst($d['status']));
        $statusColor = $isArchived ? 'badge-secondary' : ($d['status'] === 'published' ? 'badge-danger' : 'badge-info');

        $totalP     = (int)$d['total_participants'];
        $completedP = (int)($d['completed_count'] ?? 0);
        $inProgressP= (int)($d['in_progress_count'] ?? 0);
        $notStartedP= (int)($d['not_started_count'] ?? 0);
    ?>
    <div class="mission-card">
        <?php if ($d['status'] === 'published' && !$isArchived): ?>
            <div class="ribbon">LIVE</div>
        <?php elseif ($isArchived): ?>
            <div class="ribbon" style="background: #6b7280;">ARCHIVED</div>
        <?php endif; ?>

        <!-- Ownership tag -->
        <?php if ($isOwner): ?>
            <span class="owner-tag mine"><i class="fas fa-user-check"></i> Your Drill</span>
        <?php else: ?>
            <span class="owner-tag others"><i class="fas fa-user"></i> By <?= htmlspecialchars($d['creator']) ?></span>
        <?php endif; ?>

        <!-- Barangay pill -->
        <?php if(!empty($d['barangay'])): ?>
            <span class="brgy-tag"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($d['barangay']) ?></span>
        <?php else: ?>
            <span class="brgy-tag all"><i class="fas fa-globe"></i> All Barangays</span>
        <?php endif; ?>

        <h3>
            <?= htmlspecialchars($d['title']) ?>
            <small class="<?= $statusColor ?>" style="display:inline-block;margin-left:6px;"><?= $statusBadge ?></small>
        </h3>

        <!-- Participant badge -->
        <?php if ($totalP > 0): ?>
            <div class="participant-badge">
                <i class="fas fa-users"></i>
                <span class="participant-count"><?= $totalP ?> participant<?= $totalP !== 1 ? 's' : '' ?></span>
                <?php if ($completedP > 0): ?>
                    <span class="participant-status completed">✓ <?= $completedP ?> completed</span>
                <?php endif; ?>
                <?php if ($inProgressP > 0): ?>
                    <span class="participant-status in-progress">⟳ <?= $inProgressP ?> in progress</span>
                <?php endif; ?>
                <?php if ($notStartedP > 0): ?>
                    <span class="participant-status not-started">○ <?= $notStartedP ?> not started</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Meta row: date, venue, duration -->
        <div class="meta-row">
            <?php if(!empty($d['drill_date'])): ?>
            <span class="meta-item">
                <i class="fas fa-calendar-alt"></i>
                <?= date('M j, Y', strtotime($d['drill_date'])) ?>
            </span>
            <?php endif; ?>
            <?php if(!empty($d['drill_place'])): ?>
            <span class="meta-item">
                <i class="fas fa-location-dot"></i>
                <?= htmlspecialchars($d['drill_place']) ?>
            </span>
            <?php endif; ?>
            <span class="meta-item">
                <i class="fas fa-clock"></i>
                <?= (int)$d['duration_minutes'] ?> minutes
            </span>
            <span class="meta-item">
                <i class="fas fa-user"></i>
                <?= htmlspecialchars($d['creator']) ?>
            </span>
        </div>

        <?php if(!empty($d['description'])): ?>
        <p style="margin-top:8px;color:#64748b;font-size:14px;"><?= htmlspecialchars($d['description']) ?></p>
        <?php endif; ?>

        <div class="mission-actions">
            <a href="?leaderboard=<?= $d['id'] ?>" class="view-leaderboard">
                <i class="fas fa-users"></i> View Participants
            </a>
            <a href="?edit=<?= $d['id'] ?>" class="edit">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="?del=<?= $d['id'] ?>" class="delete"
               onclick="return confirm('Delete this mission forever?')">
                <i class="fas fa-trash"></i> Delete
            </a>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php endif; // end main list vs leaderboard ?>

</div><!-- /main-content -->
</body>
</html>