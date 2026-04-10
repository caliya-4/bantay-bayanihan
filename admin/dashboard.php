<?php 
session_start(); 
require '../db_connect.php';
include '../includes/header.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ── Global stats ──────────────────────────────────────────────────────────────
$activeEmergencies = $pdo->query("SELECT COUNT(*) FROM emergencies WHERE status IN ('pending','responding')")->fetchColumn();
$approvedResponders = $pdo->query("SELECT COUNT(*) FROM users WHERE role='responder' AND is_approved=1")->fetchColumn();
$pendingRegs = $pdo->query("SELECT COUNT(*) FROM users WHERE is_approved=0")->fetchColumn();
$totalDrills = $pdo->query("SELECT COUNT(*) FROM drills WHERE status='published'")->fetchColumn();

// ── All barangays with live stats ─────────────────────────────────────────────
// We derive stats from live tables rather than the cached barangay_stats table
$barangayQuery = $pdo->query("
    SELECT 
        bs.barangay,
        bs.id,
        -- Drill participants from this barangay across all drills
        (SELECT COUNT(*) FROM drill_participants dp WHERE dp.barangay = bs.barangay) AS total_drill_participants,
        -- Count of distinct drills this barangay has participated in
        (SELECT COUNT(DISTINCT dp2.drill_id) FROM drill_participants dp2 WHERE dp2.barangay = bs.barangay) AS drills_joined,
        -- Emergency counts by extracting barangay from address field (approximate)
        bs.drill_participation_rate,
        bs.preparedness_score,
        bs.rank_position
    FROM barangay_stats bs
    ORDER BY bs.barangay ASC
");
$barangays = $barangayQuery->fetchAll();

// ── Emergency breakdown by type (city-wide) ───────────────────────────────────
$emergencyTypes = $pdo->query("
    SELECT type, COUNT(*) as count 
    FROM emergencies 
    GROUP BY type 
    ORDER BY count DESC
")->fetchAll();

// ── Barangay breakdown per emergency type ────────────────────────────────────
$barangayByType = [];
foreach ($emergencyTypes as $et) {
    $stmt = $pdo->prepare("
        SELECT 
            TRIM(address) as barangay,
            COUNT(*) as cnt
        FROM emergencies
        WHERE type = ?
        AND address IS NOT NULL AND address != ''
        GROUP BY TRIM(address)
        ORDER BY cnt DESC
        LIMIT 20
    ");
    $stmt->execute([$et['type']]);
    $barangayByType[$et['type']] = $stmt->fetchAll();
}

// ── Notifications ─────────────────────────────────────────────────────────────
$notifStmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
$notifStmt->execute([$_SESSION['user_id']]);
$notes = $notifStmt->fetchAll();
$unreadStmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
$unreadStmt->execute([$_SESSION['user_id']]);
$unread = $unreadStmt->fetchColumn();

// ── Selected barangay detail (AJAX target or initial load) ───────────────────
$selectedBarangay = $_GET['barangay'] ?? null;
$brgyDetail = null;
if ($selectedBarangay) {
    // Drill participation for selected barangay
    $drillRows = $pdo->prepare("
        SELECT d.title, d.drill_date, d.drill_place,
               COUNT(dp.id) AS participant_count
        FROM drills d
        LEFT JOIN drill_participants dp ON dp.drill_id = d.id AND dp.barangay = ?
        WHERE d.status = 'published'
        GROUP BY d.id
        ORDER BY d.created_at DESC
        LIMIT 10
    ");
    $drillRows->execute([$selectedBarangay]);
    $brgyDrills = $drillRows->fetchAll();

    // Emergency types for this barangay (address LIKE match — best we can do without a strict barangay column on emergencies)
    $emgRows = $pdo->prepare("
        SELECT type, COUNT(*) as cnt 
        FROM emergencies 
        WHERE address LIKE ?
        GROUP BY type 
        ORDER BY cnt DESC
    ");
    $emgRows->execute(['%' . $selectedBarangay . '%']);
    $brgyEmergencies = $emgRows->fetchAll();

    // Total participants
    $totalParticipants = $pdo->prepare("SELECT COUNT(*) FROM drill_participants WHERE barangay = ?");
    $totalParticipants->execute([$selectedBarangay]);
    $brgyTotalParticipants = $totalParticipants->fetchColumn();
}

// ── Emergency type icon map ───────────────────────────────────────────────────
function emergencyIcon($type) {
    $map = [
        'Landslide' => '⛰️',
        'Fire'      => '🔥',
        'Flood'     => '🌊',
        'Earthquake'=> '🏚️',
        'Medical'   => '🚑',
        'Other'     => '⚠️',
    ];
    return $map[$type] ?? '🚨';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Bantay Bayanihan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/design-system.css">
    <style>
        /* ── Layout ──────────────────────────────────────────────────── */
        .dashboard-body {
            display: flex;
            gap: 28px;
            align-items: flex-start;
        }

        /* ── Left column: barangay list ──────────────────────────────── */
        .brgy-panel {
            flex: 0 0 380px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(97,97,255,.10);
            border: 1px solid rgba(97,97,255,.08);
            overflow: hidden;
            position: sticky;
            top: 20px;
        }

        .brgy-panel-header {
            background: linear-gradient(135deg, #00167a 0%, #6161ff 100%);
            padding: 24px 28px;
            color: #fff;
        }

        .brgy-panel-header h2 {
            margin: 0 0 4px;
            font-size: 18px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brgy-panel-header p {
            margin: 0;
            font-size: 13px;
            opacity: .75;
        }

        .brgy-search {
            padding: 16px 18px;
            border-bottom: 1px solid #f0f4ff;
        }

        .brgy-search input {
            width: 100%;
            padding: 10px 16px;
            border: 2px solid #e8eeff;
            border-radius: 12px;
            font-size: 14px;
            outline: none;
            transition: border-color .2s;
            box-sizing: border-box;
        }

        .brgy-search input:focus {
            border-color: #6161ff;
        }

        .brgy-list {
            list-style: none;
            padding: 10px 0;
            margin: 0;
            max-height: 560px;
            overflow-y: auto;
        }

        .brgy-list::-webkit-scrollbar { width: 5px; }
        .brgy-list::-webkit-scrollbar-thumb { background: rgba(97,97,255,.25); border-radius: 10px; }

        .brgy-item {
            padding: 0;
        }

        .brgy-item a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 13px 20px;
            text-decoration: none;
            color: #1e293b;
            font-weight: 600;
            font-size: 14px;
            transition: all .2s;
            border-left: 4px solid transparent;
            gap: 10px;
        }

        .brgy-item a:hover {
            background: #f0f4ff;
            border-left-color: #6161ff;
            color: #6161ff;
            padding-left: 26px;
        }

        .brgy-item a.active {
            background: linear-gradient(90deg, rgba(97,97,255,.12), rgba(97,97,255,.04));
            border-left-color: #ff0065;
            color: #6161ff;
        }

        .brgy-badges {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-shrink: 0;
        }

        .brgy-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 20px;
        }

        .brgy-badge.drills {
            background: rgba(97,97,255,.12);
            color: #6161ff;
        }

        .brgy-badge.active-em {
            background: rgba(255,0,101,.12);
            color: #ff0065;
        }

        /* ── Right column: content ───────────────────────────────────── */
        .content-col {
            flex: 1;
            min-width: 0;
        }

        /* ── Top stat cards ──────────────────────────────────────────── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 28px;
        }

        .stat-mini {
            background: #fff;
            border-radius: 16px;
            padding: 22px 20px;
            box-shadow: 0 6px 25px rgba(97,97,255,.09);
            border: 1px solid rgba(97,97,255,.07);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform .2s, box-shadow .2s;
            cursor: pointer;
        }

        .stat-mini:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(97,97,255,.18);
        }

        .stat-mini .icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .stat-mini .icon.red    { background: rgba(255,0,101,.12); color: #ff0065; }
        .stat-mini .icon.blue   { background: rgba(97,97,255,.12); color: #6161ff; }
        .stat-mini .icon.green  { background: rgba(16,185,129,.12); color: #10b981; }
        .stat-mini .icon.orange { background: rgba(245,158,11,.12); color: #f59e0b; }

        .stat-mini .info h3 {
            margin: 0 0 2px;
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
            line-height: 1;
        }

        .stat-mini .info p {
            margin: 0;
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
        }

        /* ── Barangay detail panel ───────────────────────────────────── */
        .detail-panel {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(97,97,255,.10);
            border: 1px solid rgba(97,97,255,.08);
            overflow: hidden;
            margin-bottom: 28px;
        }

        .detail-header {
            background: linear-gradient(135deg, #ff0065 0%, #6161ff 100%);
            padding: 26px 32px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .detail-header h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
        }

        .detail-header p {
            margin: 6px 0 0;
            font-size: 13px;
            opacity: .8;
        }

        .detail-header .header-stat {
            text-align: center;
            background: rgba(255,255,255,.15);
            border-radius: 14px;
            padding: 12px 20px;
            backdrop-filter: blur(10px);
        }

        .detail-header .header-stat .num {
            font-size: 28px;
            font-weight: 900;
            line-height: 1;
        }

        .detail-header .header-stat .lbl {
            font-size: 11px;
            opacity: .8;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .detail-body {
            padding: 28px 32px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        /* ── Section cards ───────────────────────────────────────────── */
        .section-card {
            background: #f8faff;
            border-radius: 16px;
            padding: 22px;
            border: 1px solid #e8eeff;
        }

        .section-card h4 {
            margin: 0 0 18px;
            color: #0f172a;
            font-size: 15px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-card h4 i {
            color: #6161ff;
        }

        /* ── Drill table ─────────────────────────────────────────────── */
        .drill-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .drill-table th {
            text-align: left;
            padding: 8px 12px;
            color: #64748b;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 2px solid #e8eeff;
        }

        .drill-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f0f4ff;
            color: #374151;
            vertical-align: middle;
        }

        .drill-table tr:last-child td { border-bottom: none; }

        .drill-table tr:hover td { background: rgba(97,97,255,.04); }

        .participant-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(97,97,255,.1);
            color: #6161ff;
            border-radius: 20px;
            padding: 4px 12px;
            font-weight: 700;
            font-size: 13px;
        }

        /* ── Emergency breakdown ─────────────────────────────────────── */
        .em-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .em-list li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e8eeff;
            font-size: 14px;
        }

        .em-list li:last-child { border-bottom: none; }

        .em-type {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #1e293b;
        }

        .em-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(255,0,101,.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .em-count {
            font-weight: 800;
            color: #ff0065;
            font-size: 18px;
        }

        .em-bar-wrap {
            flex: 1;
            margin: 0 14px;
            height: 6px;
            background: #e8eeff;
            border-radius: 10px;
            overflow: hidden;
        }

        .em-bar {
            height: 100%;
            background: linear-gradient(90deg, #ff0065, #6161ff);
            border-radius: 10px;
            transition: width .6s ease;
        }

        /* ── Welcome state (no barangay selected) ────────────────────── */
        .welcome-state {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(97,97,255,.10);
            border: 1px solid rgba(97,97,255,.08);
            padding: 60px 40px;
            text-align: center;
            margin-bottom: 28px;
        }

        .welcome-state .big-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .welcome-state h2 {
            color: #0f172a;
            font-size: 24px;
            font-weight: 800;
            margin: 0 0 10px;
        }

        .welcome-state p {
            color: #64748b;
            font-size: 15px;
            max-width: 400px;
            margin: 0 auto;
        }

        /* ── City-wide emergency overview (shown before selection) ───── */
        .citywide-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(97,97,255,.10);
            border: 1px solid rgba(97,97,255,.08);
            overflow: hidden;
            margin-bottom: 28px;
        }

        .citywide-header {
            background: linear-gradient(135deg, #00167a 0%, #6161ff 100%);
            padding: 22px 28px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .citywide-header h3 {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
        }

        .citywide-header p {
            margin: 4px 0 0;
            font-size: 12px;
            opacity: .75;
        }

        .citywide-body {
            padding: 24px 28px;
        }

        .em-type-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 14px;
        }

        .em-type-card {
            background: #f8faff;
            border: 2px solid #e8eeff;
            border-radius: 14px;
            padding: 18px;
            text-align: center;
            transition: all .2s;
            cursor: default;
        }

        .em-type-card:hover {
            border-color: #6161ff;
            background: rgba(97,97,255,.05);
            transform: translateY(-3px);
        }

        .em-type-card .tc-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .em-type-card .tc-count {
            font-size: 28px;
            font-weight: 900;
            color: #0f172a;
            line-height: 1;
        }

        .em-type-card .tc-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            margin-top: 4px;
        }

        /* ── Empty states ────────────────────────────────────────────── */
        .empty-box {
            text-align: center;
            padding: 30px 20px;
            color: #94a3b8;
        }

        .empty-box i { font-size: 36px; margin-bottom: 10px; opacity: .4; }

        /* ── Responsive ──────────────────────────────────────────────── */
        @media (max-width: 1200px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 1024px) {
            .dashboard-body { flex-direction: column; }
            .brgy-panel { flex: none; width: 100%; position: static; }
            .brgy-list { max-height: 300px; }
        }

        @media (max-width: 768px) {
            .detail-body { grid-template-columns: 1fr; }
            .detail-header { flex-direction: column; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <div class="page-header-content">
            <h1>👋 Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>!</h1>
            <p>City-wide disaster preparedness overview — click a barangay to drill down</p>
        </div>
    </div>

    <!-- ── Top KPI strip ──────────────────────────────────────────────── -->
    <div class="stats-row">

        <!-- 1. Active Emergencies → Manage Emergencies -->
        <a href="manage-emergencies.php" class="stat-mini" style="text-decoration:none; color:inherit;">
            <div class="icon red"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="info">
                <h3><?= $activeEmergencies ?></h3>
                <p>Active Emergencies</p>
            </div>
        </a>

        <!-- 2. Pending Registrations → Pending Registrations -->
        <a href="pending-registrations.php" class="stat-mini" style="text-decoration:none; color:inherit;">
            <div class="icon orange"><i class="fas fa-user-clock"></i></div>
            <div class="info">
                <h3><?= $pendingRegs ?></h3>
                <p>Pending Registrations</p>
            </div>
        </a>

        <!-- 3. Published Drills → Drill Management -->
        <a href="drill-management.php" class="stat-mini" style="text-decoration:none; color:inherit;">
            <div class="icon green"><i class="fas fa-clipboard-list"></i></div>
            <div class="info">
                <h3><?= $totalDrills ?></h3>
                <p>Published Drills</p>
            </div>
        </a>

        <!-- 4. Approved Responders → Responders -->
        <a href="responder.php" class="stat-mini" style="text-decoration:none; color:inherit;">
            <div class="icon blue"><i class="fas fa-users"></i></div>
            <div class="info">
                <h3><?= $approvedResponders ?></h3>
                <p>Approved Responders</p>
            </div>
        </a>

    </div>

    <!-- ── Main two-column layout ─────────────────────────────────────── -->
    <div class="dashboard-body">

        <!-- LEFT: Barangay list ─────────────────────────────────────── -->
        <div class="brgy-panel">
            <div class="brgy-panel-header">
                <h2><i class="fas fa-map-marker-alt"></i> Baguio Barangays</h2>
                <p><?= count($barangays) ?> barangays tracked</p>
            </div>
            <div class="brgy-search">
                <input type="text" id="brgySearch" placeholder="🔍 Search barangay..." oninput="filterBarangays(this.value)">
            </div>
            <ul class="brgy-list" id="brgyList">
                <?php foreach($barangays as $b): 
                    $isActive = ($selectedBarangay === $b['barangay']);
                ?>
                <li class="brgy-item">
                    <a href="?barangay=<?= urlencode($b['barangay']) ?>" 
                       class="<?= $isActive ? 'active' : '' ?>"
                       title="<?= htmlspecialchars($b['barangay']) ?>">
                        <span><?= htmlspecialchars($b['barangay']) ?></span>
                        <span class="brgy-badges">
                            <?php if($b['total_drill_participants'] > 0): ?>
                                <span class="brgy-badge drills">
                                    <i class="fas fa-users" style="font-size:9px"></i>
                                    <?= $b['total_drill_participants'] ?>
                                </span>
                            <?php endif; ?>
                        </span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- RIGHT: Detail or welcome ───────────────────────────────── -->
        <div class="content-col">

            <?php if($selectedBarangay): ?>
            <!-- ── BARANGAY DETAIL ─────────────────────────────────── -->
            <div class="detail-panel">
                <div class="detail-header">
                    <div>
                        <h2>📍 <?= htmlspecialchars($selectedBarangay) ?></h2>
                        <p>Drill participation & emergency reports breakdown</p>
                    </div>
                    <div class="header-stat">
                        <div class="num"><?= $brgyTotalParticipants ?></div>
                        <div class="lbl">Total Drill<br>Participants</div>
                    </div>
                </div>

                <div class="detail-body">
                    <!-- Drill Participation ───────────────────────── -->
                    <div class="section-card" style="grid-column: 1 / -1;">
                        <h4><i class="fas fa-clipboard-list"></i> Drill Participation History</h4>

                        <?php if(count($brgyDrills) > 0): ?>
                        <table class="drill-table">
                            <thead>
                                <tr>
                                    <th>Drill Name</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Participants from Barangay</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($brgyDrills as $drill): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($drill['title']) ?></strong></td>
                                    <td><?= $drill['drill_date'] ? date('M j, Y', strtotime($drill['drill_date'])) : '<span style="color:#94a3b8">TBD</span>' ?></td>
                                    <td><?= htmlspecialchars($drill['drill_place'] ?: '—') ?></td>
                                    <td>
                                        <span class="participant-pill">
                                            <i class="fas fa-user"></i>
                                            <?= $drill['participant_count'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="empty-box">
                            <i class="fas fa-clipboard-check"></i>
                            <p>No drill participation data for this barangay yet.</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Emergency Categories ──────────────────────── -->
                    <div class="section-card" style="grid-column: 1 / -1;">
                        <h4><i class="fas fa-exclamation-triangle"></i> Emergencies Reported by Category</h4>

                        <?php if(count($brgyEmergencies) > 0): 
                            $maxCount = max(array_column($brgyEmergencies, 'cnt'));
                        ?>
                        <ul class="em-list">
                            <?php foreach($brgyEmergencies as $em): ?>
                            <li>
                                <div class="em-type">
                                    <div class="em-icon"><?= emergencyIcon($em['type']) ?></div>
                                    <?= htmlspecialchars($em['type']) ?>
                                </div>
                                <div class="em-bar-wrap">
                                    <div class="em-bar" style="width: <?= $maxCount > 0 ? round(($em['cnt'] / $maxCount) * 100) : 0 ?>%"></div>
                                </div>
                                <span class="em-count"><?= $em['cnt'] ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>

                        <?php else: ?>
                        <div class="empty-box">
                            <i class="fas fa-check-circle"></i>
                            <p>No emergencies recorded for this barangay.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <!-- ── WELCOME + CITY-WIDE VIEW ───────────────────────── -->
            <div class="welcome-state">
                <div class="big-icon">🗺️</div>
                <h2>Select a Barangay</h2>
                <p>Click any barangay on the left to view its drill participation and emergency report breakdown.</p>
            </div>
            <?php endif; ?>

            <!-- City-wide emergency overview (always visible) ───────── -->
            <div class="citywide-card">
                <div class="citywide-header">
                    <div>
                        <h3><i class="fas fa-city"></i> City-wide Emergency Overview</h3>
                        <p>All emergencies reported across Baguio City by category</p>
                    </div>
                </div>
                <div class="citywide-body">
                    <?php if(count($emergencyTypes) > 0): ?>
                    <div class="em-type-grid">
                        <?php foreach($emergencyTypes as $et): ?>
                        <div class="em-type-card" onclick="showBarangayModal('<?= htmlspecialchars($et['type'], ENT_QUOTES) ?>')" style="cursor:pointer;" title="Click to see barangay breakdown">
                            <div class="tc-icon"><?= emergencyIcon($et['type']) ?></div>
                            <div class="tc-count"><?= $et['count'] ?></div>
                            <div class="tc-label"><?= htmlspecialchars($et['type']) ?></div>
                            <div style="font-size:11px;color:#6161ff;margin-top:6px;font-weight:700;">View Barangays →</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-box">
                        <i class="fas fa-check-shield"></i>
                        <p>No emergencies recorded yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notifications ───────────────────────────────────────── -->
            <div class="citywide-card">
                <div class="citywide-header">
                    <div>
                        <h3>
                            <i class="fas fa-bell"></i> Notifications
                            <?php if($unread > 0): ?>
                                <span style="background:#ff0065;color:#fff;padding:3px 10px;border-radius:20px;font-size:12px;margin-left:8px;"><?= $unread ?> new</span>
                            <?php endif; ?>
                        </h3>
                    </div>
                </div>
                <div class="citywide-body" style="padding: 18px 28px;">
                    <?php if(count($notes) === 0): ?>
                    <div class="empty-box"><i class="fas fa-inbox"></i><p>No notifications.</p></div>
                    <?php else: ?>
                    <ul style="list-style:none;padding:0;margin:0;">
                        <?php foreach($notes as $n): ?>
                        <li style="padding:14px 0;border-bottom:1px solid #f0f4ff;">
                            <a href="<?= htmlspecialchars($n['url'] ?? '#') ?>" style="color:#1e293b;text-decoration:none;font-weight:600;font-size:14px;">
                                <?= htmlspecialchars($n['message']) ?>
                            </a>
                            <div style="font-size:12px;color:#94a3b8;margin-top:4px;">
                                <?= date('M j, Y g:i A', strtotime($n['created_at'])) ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="mark_notifications_read.php" style="display:inline-flex;align-items:center;gap:8px;margin-top:16px;padding:10px 20px;background:linear-gradient(135deg,#6161ff,#00167a);color:#fff;text-decoration:none;border-radius:10px;font-weight:700;font-size:13px;">
                        <i class="fas fa-check-double"></i> Mark all as read
                    </a>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /content-col -->
    </div><!-- /dashboard-body -->
</div><!-- /main-content -->

<!-- ── Barangay Breakdown Modal ───────────────────────────────────────────── -->
<div id="brgyModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:20000;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:20px;padding:0;max-width:600px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.4);position:relative;max-height:85vh;overflow:hidden;display:flex;flex-direction:column;">
        <!-- Modal Header -->
        <div id="brgyModalHeader" style="padding:24px 28px;color:#fff;border-radius:20px 20px 0 0;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <div>
                <div style="font-size:28px;margin-bottom:4px;" id="brgyModalIcon"></div>
                <h2 id="brgyModalTitle" style="margin:0;font-size:20px;font-weight:900;"></h2>
                <p style="margin:4px 0 0;font-size:13px;opacity:.8;">Barangays ranked by number of reports</p>
            </div>
            <button onclick="closeBrgyModal()" style="background:rgba(255,255,255,.2);border:none;color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:18px;font-weight:900;display:flex;align-items:center;justify-content:center;">×</button>
        </div>
        <!-- Modal Body -->
        <div style="overflow-y:auto;padding:24px 28px;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead>
                    <tr>
                        <th style="text-align:center;padding:10px 12px;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:2px solid #e8eeff;width:50px;">Rank</th>
                        <th style="text-align:left;padding:10px 12px;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:2px solid #e8eeff;">Barangay / Address</th>
                        <th style="text-align:center;padding:10px 12px;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:2px solid #e8eeff;">Reports</th>
                    </tr>
                </thead>
                <tbody id="brgyModalBody"></tbody>
            </table>
            <div id="brgyModalEmpty" style="display:none;text-align:center;padding:40px 20px;color:#94a3b8;">
                <i class="fas fa-check-circle" style="font-size:36px;margin-bottom:10px;opacity:.4;display:block;"></i>
                <p>No location data available for this emergency type.</p>
            </div>
        </div>
    </div>
</div>

<?php
// Output barangay data as JSON for JS
$brgyByTypeJson = json_encode($barangayByType);
?>

<script>
const brgyByType = <?= $brgyByTypeJson ?>;

const typeColors = {
    'Fire':       'linear-gradient(135deg,#ff6b35,#f7c59f)',
    'Flood':      'linear-gradient(135deg,#0077b6,#00b4d8)',
    'Landslide':  'linear-gradient(135deg,#6d4c41,#a1887f)',
    'Earthquake': 'linear-gradient(135deg,#6a0572,#ab47bc)',
    'Medical':    'linear-gradient(135deg,#d32f2f,#ef9a9a)',
    'Other':      'linear-gradient(135deg,#455a64,#90a4ae)',
};

const typeIcons = {
    'Fire': '🔥', 'Flood': '🌊', 'Landslide': '⛰️',
    'Earthquake': '🏚️', 'Medical': '🚑', 'Other': '⚠️'
};

function showBarangayModal(type) {
    const data = brgyByType[type] || [];
    const header = document.getElementById('brgyModalHeader');
    header.style.background = typeColors[type] || 'linear-gradient(135deg,#6161ff,#00167a)';
    document.getElementById('brgyModalIcon').textContent = typeIcons[type] || '🚨';
    document.getElementById('brgyModalTitle').textContent = type + ' Reports by Location';

    const tbody = document.getElementById('brgyModalBody');
    const empty = document.getElementById('brgyModalEmpty');
    tbody.innerHTML = '';

    if (data.length === 0) {
        empty.style.display = 'block';
        tbody.style.display = 'none';
    } else {
        empty.style.display = 'none';
        tbody.style.display = '';
        const maxCount = data[0].cnt;
        data.forEach((row, i) => {
            const pct = Math.round((row.cnt / maxCount) * 100);
            const medal = i === 0 ? '🥇' : i === 1 ? '🥈' : i === 2 ? '🥉' : `#${i+1}`;
            tbody.innerHTML += `
                <tr style="border-bottom:1px solid #f0f4ff;">
                    <td style="text-align:center;padding:12px;font-weight:800;font-size:15px;">${medal}</td>
                    <td style="padding:12px;">
                        <div style="font-weight:700;color:#0f172a;margin-bottom:5px;">${row.barangay}</div>
                        <div style="height:6px;background:#e8eeff;border-radius:10px;overflow:hidden;">
                            <div style="height:100%;width:${pct}%;background:${typeColors[type] || 'linear-gradient(90deg,#6161ff,#00167a)'};border-radius:10px;transition:width .6s;"></div>
                        </div>
                    </td>
                    <td style="text-align:center;padding:12px;font-weight:900;font-size:18px;color:#ff0065;">${row.cnt}</td>
                </tr>`;
        });
    }

    document.getElementById('brgyModal').style.display = 'flex';
}

function closeBrgyModal() {
    document.getElementById('brgyModal').style.display = 'none';
}

document.getElementById('brgyModal').addEventListener('click', function(e) {
    if (e.target === this) closeBrgyModal();
});
</script>

<script>
function filterBarangays(q) {
    const items = document.querySelectorAll('#brgyList .brgy-item');
    q = q.toLowerCase().trim();
    items.forEach(item => {
        const link = item.querySelector('a');
        const name = link ? link.getAttribute('title').toLowerCase() : '';
        item.style.display = (q === '' || name.includes(q)) ? '' : 'none';
    });
}
</script>
<script src="../assets/js/chatbot.js"></script>
</body>
</html>