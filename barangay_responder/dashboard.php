<?php 
session_start(); 
require '../db_connect.php';
include '../includes/header.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'responder') {
    header("Location: ../login.php");
    exit;
}

// ── Resolve the responder's barangay ─────────────────────────────────────────
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$currentUser = $userStmt->fetch();

$myBarangay = null;

// Priority 1: Check users.barangay column directly
if (!empty($currentUser['barangay']) && trim($currentUser['barangay']) !== '') {
    $myBarangay = trim($currentUser['barangay']);
}

// Priority 2: Check session (admin can override)
if (!$myBarangay && isset($_SESSION['barangay']) && trim($_SESSION['barangay']) !== '') {
    $myBarangay = trim($_SESSION['barangay']);
}

// Priority 3: Extract from address field with better patterns
if (!$myBarangay && !empty($currentUser['address'])) {
    $address = $currentUser['address'];
    
    // Try different patterns in order of specificity
    $patterns = [
        '/Brgy\.\s+([^,]+)/i',                    // "Brgy. Dizon Subdivision"
        '/Barangay\s+([^,]+)/i',                  // "Barangay Dizon Subdivision"
        '/(\d+\s+)?([A-Z][a-z]+(?:\s+[A-Z][a-z]+)?)\s+(?:Subdivision|Village|Park),/i', // "123 Dizon Subdivision,"
        '/([^,\n]+?)(?:\s*,\s*Baguio)/i',         // Fallback: everything before ", Baguio"
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $address, $matches)) {
            // Get the last non-empty match group
            for ($i = count($matches) - 1; $i > 0; $i--) {
                if (!empty(trim($matches[$i]))) {
                    $myBarangay = trim($matches[$i]);
                    break 2; // Break out of both loops
                }
            }
        }
    }
}

// Priority 4: Check drill_participants table (as registered user)
if (!$myBarangay) {
    $stmt = $pdo->prepare("
        SELECT barangay FROM drill_participants 
        WHERE user_id = ? AND barangay IS NOT NULL AND barangay != ''
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $brgy = $stmt->fetchColumn();
    if ($brgy) {
        $myBarangay = $brgy;
    }
}

// Priority 5: Check drill_participants table (as walk-in by email)
if (!$myBarangay) {
    $stmt = $pdo->prepare("
        SELECT barangay FROM drill_participants 
        WHERE email = ? AND barangay IS NOT NULL AND barangay != ''
        LIMIT 1
    ");
    $stmt->execute([$currentUser['email']]);
    $brgy = $stmt->fetchColumn();
    if ($brgy) {
        $myBarangay = $brgy;
    }
}

// Debug (remove in production)
if (!$myBarangay) {
    error_log("WARNING: Could not resolve barangay for user " . $_SESSION['user_id']);
}
// ── Stats for this barangay ───────────────────────────────────────────────────
$brgyStats = null;
$brgyDrills = [];
$brgyEmergencies = [];
$brgyTotalParticipants = 0;

if ($myBarangay) {
    // Barangay stats row
    $statsStmt = $pdo->prepare("SELECT * FROM barangay_stats WHERE barangay = ? LIMIT 1");
    $statsStmt->execute([$myBarangay]);
    $brgyStats = $statsStmt->fetch();

    // Drill participation per drill
    $drillStmt = $pdo->prepare("
    SELECT 
        d.title, 
        d.drill_date, 
        d.drill_time, 
        d.drill_place, 
        d.duration_minutes,
        (
            SELECT COUNT(DISTINCT dp1.id) 
            FROM drill_participants dp1 
            WHERE dp1.drill_id = d.id AND LOWER(dp1.barangay) = LOWER(?)
        )
        +
        (
            SELECT COUNT(DISTINCT dp2.id) 
            FROM drill_participants dp2 
            WHERE dp2.drill_id = d.id AND LOWER(dp2.barangay) = LOWER(?)
        )
        AS participant_count
    FROM drills d
    WHERE d.status = 'published'
    AND (
        -- Show drills that have participants from this barangay
        EXISTS (
            SELECT 1 FROM drill_participants dp3 
            WHERE dp3.drill_id = d.id AND LOWER(dp3.barangay) = LOWER(?)
        )
        OR
        EXISTS (
            SELECT 1 FROM drill_participants dp4 
            WHERE dp4.drill_id = d.id AND LOWER(dp4.barangay) = LOWER(?)
        )
        OR
        -- OR show drills with no barangay filter (open to all)
        NOT EXISTS (
            SELECT 1 FROM drill_participants dp5 WHERE dp5.drill_id = d.id
        )
    )
    ORDER BY d.created_at DESC
    LIMIT 10
");
$drillStmt->execute([$myBarangay, $myBarangay, $myBarangay, $myBarangay]);
$brgyDrills = $drillStmt->fetchAll();

        // Count total participants from this barangay
        $totalStmt = $pdo->prepare("
    SELECT 
        COALESCE((SELECT COUNT(DISTINCT id) FROM drill_participants WHERE LOWER(barangay) = LOWER(?)), 0)
        +
        COALESCE((SELECT COUNT(DISTINCT id) FROM drill_participants WHERE LOWER(barangay) = LOWER(?)), 0)
        AS total
");
    $totalStmt->execute([$myBarangay, $myBarangay]);
    $brgyTotalParticipants = $totalStmt->fetchColumn();
    if ($brgyTotalParticipants === null) {
        $brgyTotalParticipants = 0;
    }

    // Emergency breakdown by type (address LIKE)
    $emgStmt = $pdo->prepare("
        SELECT type, COUNT(*) as cnt, 
            SUM(CASE WHEN status IN ('pending','responding') THEN 1 ELSE 0 END) AS active_cnt
        FROM emergencies 
        WHERE address LIKE ?
        GROUP BY type 
        ORDER BY cnt DESC
    ");
    $emgStmt->execute(['%' . $myBarangay . '%']);
    $brgyEmergencies = $emgStmt->fetchAll();

    // Active emergencies for this barangay
    $activeEmgStmt = $pdo->prepare("
        SELECT COUNT(*) FROM emergencies 
        WHERE address LIKE ? AND status IN ('pending','responding')
    ");
    $activeEmgStmt->execute(['%' . $myBarangay . '%']);
    $activeEmgCount = $activeEmgStmt->fetchColumn();
}

// ── Notifications ─────────────────────────────────────────────────────────────
$notifStmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
$notifStmt->execute([$_SESSION['user_id']]);
$notes = $notifStmt->fetchAll();
$unreadStmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
$unreadStmt->execute([$_SESSION['user_id']]);
$unread = $unreadStmt->fetchColumn();

// ── Announcements ─────────────────────────────────────────────────────────────
$annStmt = $pdo->query("
    SELECT a.*, u.name AS author_name 
    FROM announcements a 
    JOIN users u ON a.created_by = u.id 
    ORDER BY a.created_at DESC LIMIT 3
");
$announcements = $annStmt->fetchAll();

// ── Helper ────────────────────────────────────────────────────────────────────
function emergencyIcon($type) {
    $map = [
        'Landslide' => '⛰️', 'Fire' => '🔥', 'Flood' => '🌊',
        'Earthquake' => '🏚️', 'Medical' => '🚑', 'Other' => '⚠️',
    ];
    return $map[$type] ?? '🚨';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responder Dashboard | Bantay Bayanihan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/design-system.css">
    <link rel="stylesheet" href="../assets/css/responder.css">
    <style>
        /* ── Page header override ────────────────────────────────── */
        .page-header { background: var(--gradient-accent) !important; }

        /* ── Barangay banner ─────────────────────────────────────── */
        .brgy-banner {
            background: linear-gradient(135deg, #00167a 0%, #6161ff 100%);
            border-radius: 20px;
            padding: 28px 36px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 28px;
            flex-wrap: wrap;
            box-shadow: 0 12px 40px rgba(97,97,255,.3);
        }

        .brgy-banner .brgy-name {
            font-size: 28px;
            font-weight: 900;
            margin: 0 0 6px;
        }

        .brgy-banner .brgy-sub {
            font-size: 14px;
            opacity: .75;
            margin: 0;
        }

        .brgy-banner .stat-pills {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .stat-pill {
            background: rgba(255,255,255,.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 14px;
            padding: 14px 22px;
            text-align: center;
            min-width: 90px;
        }

        .stat-pill .sp-num {
            font-size: 26px;
            font-weight: 900;
            line-height: 1;
        }

        .stat-pill .sp-lbl {
            font-size: 11px;
            opacity: .8;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        /* ── No-barangay warning ─────────────────────────────────── */
        .no-brgy-warning {
            background: linear-gradient(135deg, #fff5f9, #fff);
            border: 2px solid #ff0065;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            margin-bottom: 28px;
        }

        .no-brgy-warning i { font-size: 40px; color: #ff0065; margin-bottom: 12px; }
        .no-brgy-warning h3 { color: #ff0065; margin: 0 0 8px; font-size: 18px; }
        .no-brgy-warning p  { color: #64748b; margin: 0; font-size: 14px; }

        /* ── Quick actions ───────────────────────────────────────── */
        .actions-card {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 35px rgba(97,97,255,.1);
            border: 1px solid rgba(97,97,255,.08);
            margin-bottom: 28px;
        }

        .actions-card h3 {
            color: #0f172a;
            font-size: 20px;
            font-weight: 800;
            margin: 0 0 22px;
        }

        .quick-btns {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .emergency-btn {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: var(--gradient-primary);
            color: #fff;
            padding: 20px 40px;
            font-size: 17px;
            font-weight: 900;
            text-decoration: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(97,97,255,.35);
            transition: all .3s;
            animation: pulse-glow 3s infinite;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .emergency-btn:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(97,97,255,.45); }

        .evac-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            padding: 18px 32px;
            font-size: 15px;
            font-weight: 800;
            text-decoration: none;
            border-radius: 14px;
            box-shadow: 0 8px 22px rgba(16,185,129,.3);
            transition: all .3s;
        }

        .evac-btn:hover { transform: translateY(-3px); }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 10px 30px rgba(97,97,255,.35); }
            50% { box-shadow: 0 10px 40px rgba(255,0,101,.45); }
        }

        /* ── Stats grid ──────────────────────────────────────────── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 28px;
        }

        .stat-card-r {
            background: #fff;
            border-radius: 16px;
            padding: 22px 20px;
            box-shadow: 0 6px 25px rgba(97,97,255,.09);
            border: 1px solid rgba(97,97,255,.07);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-card-r .icon {
            width: 50px; height: 50px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; flex-shrink: 0;
        }

        .stat-card-r .icon.red    { background: rgba(255,0,101,.12); color: #ff0065; }
        .stat-card-r .icon.blue   { background: rgba(97,97,255,.12); color: #6161ff; }
        .stat-card-r .icon.green  { background: rgba(16,185,129,.12); color: #10b981; }

        .stat-card-r .info h3 { margin: 0 0 3px; font-size: 26px; font-weight: 900; color: #0f172a; }
        .stat-card-r .info p  { margin: 0; font-size: 12px; color: #64748b; font-weight: 600; }

        /* ── Data cards ──────────────────────────────────────────── */
        .data-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
            margin-bottom: 28px;
        }

        .data-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 35px rgba(97,97,255,.1);
            border: 1px solid rgba(97,97,255,.08);
            overflow: hidden;
        }

        .data-card-header {
            background: linear-gradient(135deg, #00167a 0%, #6161ff 100%);
            padding: 18px 24px;
            color: #fff;
        }

        .data-card-header h4 {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .data-card-body { padding: 20px 24px; }

        /* ── Drill table ─────────────────────────────────────────── */
        .drill-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .drill-table th {
            text-align: left;
            padding: 8px 10px;
            color: #64748b;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .4px;
            border-bottom: 2px solid #f0f4ff;
        }

        .drill-table td {
            padding: 10px 10px;
            border-bottom: 1px solid #f0f4ff;
            color: #374151;
        }

        .drill-table tr:last-child td { border-bottom: none; }
        .drill-table tr:hover td { background: rgba(97,97,255,.03); }

        .part-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(97,97,255,.1); color: #6161ff;
            border-radius: 20px; padding: 4px 12px; font-weight: 700; font-size: 13px;
        }

        /* ── Emergency list ──────────────────────────────────────── */
        .em-list { list-style: none; padding: 0; margin: 0; }

        .em-list li {
            display: flex; align-items: center;
            justify-content: space-between;
            padding: 12px 0; border-bottom: 1px solid #f0f4ff;
        }

        .em-list li:last-child { border-bottom: none; }

        .em-type { display: flex; align-items: center; gap: 10px; font-weight: 600; color: #1e293b; font-size: 14px; }
        .em-icon { width: 36px; height: 36px; border-radius: 10px; background: rgba(255,0,101,.08); display: flex; align-items: center; justify-content: center; font-size: 16px; }

        .em-bar-wrap { flex: 1; margin: 0 14px; height: 6px; background: #f0f4ff; border-radius: 10px; overflow: hidden; }
        .em-bar { height: 100%; background: linear-gradient(90deg, #ff0065, #6161ff); border-radius: 10px; }

        .em-count { font-weight: 900; color: #ff0065; font-size: 18px; }

        /* ── Announcements ───────────────────────────────────────── */
        .ann-list { display: flex; flex-direction: column; gap: 12px; }

        .ann-item {
            background: linear-gradient(135deg, #f8faff, #fff5f9);
            border-left: 4px solid #6161ff;
            border-radius: 12px;
            padding: 16px;
        }

        .ann-item h5 { margin: 0 0 6px; color: #0f172a; font-size: 14px; font-weight: 700; }
        .ann-item p  { margin: 0 0 8px; color: #64748b; font-size: 13px; line-height: 1.5; }
        .ann-meta    { font-size: 11px; color: #94a3b8; display: flex; gap: 10px; flex-wrap: wrap; }

        /* ── Notifications ───────────────────────────────────────── */
        .notif-list { list-style: none; padding: 0; margin: 0; }

        .notif-list li { padding: 14px 0; border-bottom: 1px solid #f0f4ff; }
        .notif-list li:last-child { border-bottom: none; }

        .notif-msg { font-weight: 600; color: #0f172a; font-size: 14px; margin-bottom: 5px; }
        .notif-time { font-size: 12px; color: #94a3b8; }

        /* ── Empty state ─────────────────────────────────────────── */
        .empty-box { text-align: center; padding: 30px 20px; color: #94a3b8; }
        .empty-box i { font-size: 36px; margin-bottom: 10px; opacity: .4; }

        /* ── Full-width card ─────────────────────────────────────── */
        .full-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 35px rgba(97,97,255,.1);
            border: 1px solid rgba(97,97,255,.08);
            overflow: hidden;
            margin-bottom: 28px;
        }

        .full-card-header {
            background: linear-gradient(135deg, #00167a 0%, #6161ff 100%);
            padding: 18px 26px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .full-card-header h3 { margin: 0; font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 10px; }

        .full-card-header .badge {
            background: #ff0065; color: #fff;
            padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 800;
        }

        .full-card-body { padding: 22px 26px; }

        /* ── Responsive ──────────────────────────────────────────── */
        @media (max-width: 900px) {
            .data-grid { grid-template-columns: 1fr; }
            .stats-row { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 600px) {
            .stats-row { grid-template-columns: 1fr; }
            .quick-btns { flex-direction: column; }
            .emergency-btn, .evac-btn { justify-content: center; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <div class="page-header-content">
            <h1>🚨 Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>!</h1>
            <p>Stay vigilant. Stay prepared. Your community relies on your readiness.</p>
        </div>
    </div>

    <?php if($myBarangay): ?>
    <!-- ── BARANGAY BANNER ───────────────────────────────────────────── -->
    <div class="brgy-banner">
        <div>
            <p class="brgy-name">📍 <?= htmlspecialchars($myBarangay) ?></p>
            <p class="brgy-sub">Your assigned barangay · <?= htmlspecialchars($myBarangay) ?>, Baguio City</p>
        </div>
        <div class="stat-pills">
            <div class="stat-pill">
                <div class="sp-num"><?= $brgyTotalParticipants ?></div>
                <div class="sp-lbl">Drill<br>Participants</div>
            </div>
            <div class="stat-pill">
                <div class="sp-num"><?= array_sum(array_column($brgyEmergencies, 'cnt')) ?></div>
                <div class="sp-lbl">Emergencies<br>Recorded</div>
            </div>
            <?php if(isset($activeEmgCount)): ?>
            <div class="stat-pill">
                <div class="sp-num" style="color:#ff0065"><?= $activeEmgCount ?></div>
                <div class="sp-lbl">Currently<br>Active</div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php else: ?>
    <!-- ── NO BARANGAY ASSIGNED ────────────────────────────────────── -->
    <div class="no-brgy-warning">
        <i class="fas fa-map-marker-alt"></i>
        <h3>No Barangay Assigned</h3>
        <p>Your account hasn't been linked to a specific barangay yet.<br>
        Please contact your administrator to assign your barangay.</p>
    </div>
    <?php endif; ?>

    <!-- ── Quick Actions ──────────────────────────────────────────────── -->
    <div class="actions-card">
        <h3>⚡ Quick Actions</h3>
        <div class="quick-btns">
            <a href="report-emergency.php" class="emergency-btn">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Report Emergency Now</span>
            </a>
            <a href="evacuation-mode.php" class="evac-btn">
                <i class="fas fa-map-marked-alt"></i>
                <span>Evacuation Mode</span>
            </a>
        </div>
    </div>

    <?php if($myBarangay): ?>

    <!-- ── KPI Strip ──────────────────────────────────────────────────── -->
    <div class="stats-row">
        <div class="stat-card-r">
            <div class="icon blue"><i class="fas fa-clipboard-list"></i></div>
            <div class="info">
                <h3><?= count($brgyDrills) ?></h3>
                <p>Drills Available</p>
            </div>
        </div>
        <div class="stat-card-r">
            <div class="icon green"><i class="fas fa-users"></i></div>
            <div class="info">
                <h3><?= $brgyTotalParticipants ?></h3>
                <p>Total Drill Participants</p>
            </div>
        </div>
        <div class="stat-card-r">
            <div class="icon red"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="info">
                <h3><?= isset($activeEmgCount) ? $activeEmgCount : 0 ?></h3>
                <p>Active Emergencies</p>
            </div>
        </div>
    </div>

    <!-- ── Drill + Emergency split ───────────────────────────────────── -->
    <div class="data-grid">

        <!-- Drill Participation ──────────────────────────────────────── -->
        <div class="data-card">
            <div class="data-card-header">
                <h4><i class="fas fa-clipboard-list"></i> Drill Participation</h4>
            </div>
            <div class="data-card-body">
                <?php if(count($brgyDrills) > 0): ?>
                <table class="drill-table">
                    <thead>
                        <tr>
                            <th>Drill</th>
                            <th>Date</th>
                            <th>Participants</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($brgyDrills as $drill): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($drill['title']) ?></strong>
                                <?php if($drill['drill_place']): ?>
                                    <br><small style="color:#94a3b8"><?= htmlspecialchars($drill['drill_place']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td style="white-space:nowrap;">
                                <?= $drill['drill_date'] 
                                    ? date('M j, Y', strtotime($drill['drill_date'])) 
                                    : '<span style="color:#94a3b8">TBD</span>' ?>
                            </td>
                            <td>
                                <span class="part-badge">
                                    <i class="fas fa-user"></i> <?= $drill['participant_count'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-box">
                    <i class="fas fa-clipboard-check"></i>
                    <p>No drill data for <?= htmlspecialchars($myBarangay) ?> yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Emergency by Category ──────────────────────────────────── -->
        <div class="data-card">
            <div class="data-card-header">
                <h4><i class="fas fa-exclamation-triangle"></i> Emergencies by Category</h4>
            </div>
            <div class="data-card-body">
                <?php if(count($brgyEmergencies) > 0): 
                    $maxCnt = max(array_column($brgyEmergencies, 'cnt'));
                ?>
                <ul class="em-list">
                    <?php foreach($brgyEmergencies as $em): ?>
                    <li>
                        <div class="em-type">
                            <div class="em-icon"><?= emergencyIcon($em['type']) ?></div>
                            <?= htmlspecialchars($em['type']) ?>
                        </div>
                        <div class="em-bar-wrap">
                            <div class="em-bar" style="width: <?= $maxCnt > 0 ? round(($em['cnt'] / $maxCnt) * 100) : 0 ?>%"></div>
                        </div>
                        <span class="em-count"><?= $em['cnt'] ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="empty-box">
                    <i class="fas fa-check-circle"></i>
                    <p>No emergencies recorded for <?= htmlspecialchars($myBarangay) ?>.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /data-grid -->

    <?php endif; // end $myBarangay check ?>

    <!-- ── Announcements ─────────────────────────────────────────────── -->
    <?php if(count($announcements) > 0): ?>
    <div class="full-card">
        <div class="full-card-header">
            <h3><i class="fas fa-bullhorn"></i> Latest Announcements</h3>
            <a href="announcements.php" style="color:rgba(255,255,255,.8);font-size:13px;text-decoration:none;">
                View all <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="full-card-body">
            <div class="ann-list">
                <?php foreach($announcements as $a): ?>
                <div class="ann-item">
                    <h5><?= htmlspecialchars($a['title']) ?></h5>
                    <p><?= htmlspecialchars(substr($a['message'], 0, 160)) ?><?= strlen($a['message']) > 160 ? '…' : '' ?></p>
                    <div class="ann-meta">
                        <span><i class="fas fa-user-circle"></i> <?= htmlspecialchars($a['author_name']) ?></span>
                        <span><i class="far fa-calendar"></i> <?= date('M j, Y', strtotime($a['created_at'])) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Notifications ─────────────────────────────────────────────── -->
    <div class="full-card">
        <div class="full-card-header">
            <h3>
                <i class="fas fa-bell"></i> Notifications
                <?php if($unread > 0): ?>
                    <span class="badge"><?= $unread ?> new</span>
                <?php endif; ?>
            </h3>
        </div>
        <div class="full-card-body">
            <?php if(count($notes) === 0): ?>
            <div class="empty-box"><i class="fas fa-inbox"></i><p>No notifications yet.</p></div>
            <?php else: ?>
            <ul class="notif-list">
                <?php foreach($notes as $n): ?>
                <li>
                    <div class="notif-msg"><?= htmlspecialchars($n['message']) ?></div>
                    <div class="notif-time"><i class="far fa-clock"></i> <?= date('M j, Y g:i A', strtotime($n['created_at'])) ?></div>
                </li>
                <?php endforeach; ?>
            </ul>
            <a href="mark_notifications_read.php" style="display:inline-flex;align-items:center;gap:8px;margin-top:16px;padding:11px 22px;background:linear-gradient(135deg,#6161ff,#00167a);color:#fff;text-decoration:none;border-radius:12px;font-weight:700;font-size:13px;float:right;">
                <i class="fas fa-check-double"></i> Mark all as read
            </a>
            <div style="clear:both;"></div>
            <?php endif; ?>
        </div>
    </div>

</div><!-- /main-content -->

<script src="../assets/js/chatbot.js"></script>
</body>
</html>