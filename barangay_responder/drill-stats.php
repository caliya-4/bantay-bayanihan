<?php 
session_start(); 
require '../db_connect.php';
include '../includes/header.php';

// Check authentication
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'responder'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// ── Resolve user's barangay with multiple fallbacks ─────────────────────────
$stmt = $pdo->prepare("SELECT address, barangay, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$user_barangay = null;

// Priority 1: Check users.barangay column
if (!empty($user['barangay']) && trim($user['barangay']) !== '') {
    $user_barangay = trim($user['barangay']);
}

// Priority 2: Extract from address field
if (!$user_barangay && !empty($user['address'])) {
    $patterns = [
        '/Brgy\.\s+([^,]+)/i',
        '/Barangay\s+([^,]+)/i',
        '/([^,\n]+?)\s*,\s*Baguio/i',
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $user['address'], $matches)) {
            $user_barangay = trim($matches[1]);
            break;
        }
    }
}

// Priority 3: Check drill_participants table by email
if (!$user_barangay) {
    $stmt = $pdo->prepare("SELECT barangay FROM drill_participants WHERE email = ? AND barangay IS NOT NULL AND barangay != '' LIMIT 1");
    $stmt->execute([$user['email']]);
    $brgy = $stmt->fetchColumn();
    if ($brgy) {
        $user_barangay = $brgy;
    }
}

// ── ENFORCE ROLE-BASED ACCESS ───────────────────────────────────────────────
if ($user_role === 'responder') {
    // Responders can ONLY see their assigned barangay
    $selected_barangay = $user_barangay;
    $can_select_barangay = false;
} else {
    // Admins can select any barangay via GET parameter
    $selected_barangay = isset($_GET['barangay']) ? trim($_GET['barangay']) : $user_barangay;
    $can_select_barangay = true;
}

// ── Get list of barangays for dropdown (admins only) ────────────────────────
$barangays = [];
if ($can_select_barangay) {
    $barangay_query = "
        SELECT DISTINCT barangay FROM (
            SELECT barangay FROM drills WHERE barangay IS NOT NULL AND barangay != ''
            UNION
            SELECT barangay FROM drill_participants WHERE barangay IS NOT NULL AND barangay != ''
        ) AS b
        ORDER BY barangay
    ";
    $barangay_stmt = $pdo->query($barangay_query);
    $barangays = $barangay_stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Barangay Drill Statistics | Barangay Responder</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/design-system.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #f1f5f9 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .main-content {
            margin-left: 270px;
            padding: 80px 30px 40px 30px;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
        }

        .page-header {
            background: linear-gradient(135deg, #00167a 0%, #6161ff 100%);
            color: white;
            border-radius: 20px;
            padding: 40px 40px;
            margin-bottom: 30px;
            box-shadow: 0 20px 60px rgba(0, 22, 122, 0.2);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            right: -60px;
            top: -60px;
            width: 220px;
            height: 220px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
        }

        .page-header::after {
            content: '';
            position: absolute;
            right: 80px;
            bottom: -80px;
            width: 180px;
            height: 180px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
        }

        .page-header h1 {
            margin: 0 0 10px 0;
            font-size: 32px;
            font-weight: 900;
            position: relative;
            z-index: 1;
        }

        .page-header p {
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .barangay-selector {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 22, 122, 0.08);
        }

        .barangay-selector-content {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .barangay-selector label {
            font-weight: 700;
            color: #00167a;
            margin: 0;
        }

        .barangay-selector select {
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            cursor: pointer;
            min-width: 250px;
        }

        .barangay-selector select:focus {
            outline: none;
            border-color: #6161ff;
            box-shadow: 0 0 0 3px rgba(97, 97, 255, 0.1);
        }

        .assigned-barangay-badge {
            background: linear-gradient(135deg, #6161ff15, #00167a10);
            border: 2px solid #6161ff40;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 700;
            color: #00167a;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 22, 122, 0.08);
            border-left: 4px solid #6161ff;
        }

        .stat-label {
            color: #94a3b8;
            font-size: 13px;
            font-weight: 700;
            margin: 0;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 900;
            color: #00167a;
            margin: 8px 0 0 0;
        }

        .stat-detail {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 5px;
        }

        .content-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 22, 122, 0.08);
            margin-bottom: 30px;
        }

        .section-title {
            color: #00167a;
            font-size: 22px;
            font-weight: 900;
            margin: 0 0 20px 0;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th {
            background: #f8fafc;
            padding: 15px;
            text-align: left;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #475569;
        }

        tr:hover {
            background: #f9fafb;
        }

        .drill-title {
            font-weight: 700;
            color: #00167a;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-in-progress {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-not-started {
            background: #fee2e2;
            color: #7f1d1d;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .empty-title {
            color: #00167a;
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }

        .empty-text {
            color: #94a3b8;
            margin: 0;
        }

        .progress-container {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 5px;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #6161ff, #ff0065);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .drill-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
        }

        .drill-card h4 {
            margin: 0 0 10px 0;
            color: #00167a;
            font-size: 16px;
        }

        .drill-stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 15px;
        }

        .drill-stat {
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .drill-stat-value {
            font-size: 18px;
            font-weight: 900;
            color: #6161ff;
        }

        .drill-stat-label {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 3px;
        }

        .no-access-warning {
            background: linear-gradient(135deg, #fff5f9, #fff);
            border: 2px solid #ff0065;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            margin-bottom: 28px;
        }

        .no-access-warning i { 
            font-size: 40px; 
            color: #ff0065; 
            margin-bottom: 12px; 
        }
        .no-access-warning h3 { 
            color: #ff0065; 
            margin: 0 0 8px; 
            font-size: 18px; 
        }
        .no-access-warning p { 
            color: #64748b; 
            margin: 0; 
            font-size: 14px; 
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 70px 15px 30px 15px;
            }

            .page-header {
                padding: 25px 20px;
            }

            .page-header h1 {
                font-size: 22px;
            }

            .page-header p {
                font-size: 14px;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .barangay-selector-content {
                flex-direction: column;
            }

            .barangay-selector select {
                width: 100%;
            }

            .drill-stats-row {
                grid-template-columns: 1fr 1fr;
            }

            .table-responsive {
                font-size: 12px;
            }

            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container">

            <div class="page-header">
                <h1>📊 My Barangay Drill Statistics</h1>
                <p>View drill participation and progress for your barangay</p>
            </div>

            <?php if (!$selected_barangay): ?>
                <!-- No barangay assigned -->
                <div class="no-access-warning">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>No Barangay Assigned</h3>
                    <p>Your account hasn't been linked to a specific barangay yet.<br>
                    Please contact your administrator to assign your barangay.</p>
                </div>
            <?php else: ?>
                
                <!-- Barangay Selector (Admins only) -->
                <?php if ($can_select_barangay && count($barangays) > 0): ?>
                    <div class="barangay-selector">
                        <div class="barangay-selector-content">
                            <label for="barangay-select">Select Barangay:</label>
                            <select id="barangay-select" onchange="window.location.href = 'drill-stats.php?barangay=' + encodeURIComponent(this.value)">
                                <option value="">-- Choose a barangay --</option>
                                <?php foreach ($barangays as $barangay): ?>
                                    <option value="<?php echo htmlspecialchars($barangay); ?>" <?php echo $selected_barangay === $barangay ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($barangay); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Responder: Show assigned barangay badge -->
                    <div class="barangay-selector">
                        <div class="barangay-selector-content">
                            <label>Assigned Barangay:</label>
                            <div class="assigned-barangay-badge">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($selected_barangay); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($selected_barangay): ?>
                    <?php
                    // Stats query for drills owned by the selected barangay
                    $stats_query = "
                        SELECT 
                            COUNT(DISTINCT dp.id) as total_participants,
                            COALESCE(SUM(CASE WHEN dp.status = 'completed' THEN 1 ELSE 0 END), 0) as completed,
                            COALESCE(SUM(CASE WHEN dp.status = 'in_progress' THEN 1 ELSE 0 END), 0) as in_progress,
                            COALESCE(SUM(CASE WHEN dp.status = 'not_started' THEN 1 ELSE 0 END), 0) as not_started
                        FROM drill_participants dp
                        JOIN drills d ON dp.drill_id = d.id
                        WHERE LOWER(d.barangay) = LOWER(?)
                    ";
                    $stats_stmt = $pdo->prepare($stats_query);
                    $stats_stmt->execute([$selected_barangay]);
                    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

                    $total = $stats['total_participants'] ?? 0;
                    $completed = $stats['completed'] ?? 0;
                    $in_progress = $stats['in_progress'] ?? 0;
                    $not_started = $stats['not_started'] ?? 0;
                    $completion_rate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
                    ?>

                    <div class="stats-grid">
                        <div class="stat-card">
                            <p class="stat-label">Total Participants</p>
                            <p class="stat-value"><?php echo $total; ?></p>
                        </div>
                        <div class="stat-card">
                            <p class="stat-label">Completed</p>
                            <p class="stat-value" style="color: #10b981;"><?php echo $completed; ?></p>
                            <p class="stat-detail"><?php echo $completion_rate; ?>% completion rate</p>
                        </div>
                        <div class="stat-card">
                            <p class="stat-label">In Progress</p>
                            <p class="stat-value" style="color: #f59e0b;"><?php echo $in_progress; ?></p>
                        </div>
                        <div class="stat-card">
                            <p class="stat-label">Not Started</p>
                            <p class="stat-value" style="color: #ef4444;"><?php echo $not_started; ?></p>
                        </div>
                    </div>

                    <div class="content-section">
                        <h2 class="section-title">Drills Participated by Residents of <?php echo htmlspecialchars($selected_barangay); ?></h2>
                        
                        <?php
                        // Drills query for drills assigned to this barangay or drills with participants from this barangay
                        $drills_query = "
                            SELECT 
                                d.id,
                                d.title,
                                d.created_at,
                                d.drill_date,
                                d.duration_minutes,
                                u.name as creator,
                                COUNT(DISTINCT dp.id) as total_participants,
                                COALESCE(SUM(CASE WHEN dp.status = 'completed' THEN 1 ELSE 0 END), 0) as completed,
                                COALESCE(SUM(CASE WHEN dp.status = 'in_progress' THEN 1 ELSE 0 END), 0) as in_progress,
                                COALESCE(SUM(CASE WHEN dp.status = 'not_started' THEN 1 ELSE 0 END), 0) as not_started
                            FROM drills d
                            LEFT JOIN drill_participants dp ON d.id = dp.drill_id
                                AND LOWER(dp.barangay) = LOWER(?)
                            LEFT JOIN users u ON d.created_by = u.id
                            WHERE d.status = 'published'
                              AND (
                                  LOWER(d.barangay) = LOWER(?)
                                  OR EXISTS (
                                      SELECT 1 FROM drill_participants dp2
                                      WHERE dp2.drill_id = d.id
                                        AND LOWER(dp2.barangay) = LOWER(?)
                                  )
                              )
                            GROUP BY d.id
                            HAVING total_participants > 0
                            ORDER BY d.created_at DESC
                        ";
                        
                        $drills_stmt = $pdo->prepare($drills_query);
                        $drills_stmt->execute([$selected_barangay, $selected_barangay, $selected_barangay]);
                        $drills = $drills_stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (count($drills) > 0):
                        ?>
                            <?php foreach ($drills as $drill): ?>
                                <div class="drill-card">
                                    <h4><?php echo htmlspecialchars($drill['title']); ?></h4>
                                    <p style="margin: 5px 0; color: #94a3b8; font-size: 13px;">
                                        Created by: <strong><?php echo htmlspecialchars($drill['creator'] ?? 'Unknown'); ?></strong> | 
                                        Duration: <?php echo (int)$drill['duration_minutes']; ?> min |
                                        Date: <?php echo $drill['drill_date'] ? date('M d, Y', strtotime($drill['drill_date'])) : 'Not scheduled'; ?>
                                    </p>
                                    
                                    <div class="drill-stats-row">
                                        <div class="drill-stat">
                                            <div class="drill-stat-value"><?php echo $drill['total_participants'] ?? 0; ?></div>
                                            <div class="drill-stat-label">Total</div>
                                        </div>
                                        <div class="drill-stat">
                                            <div class="drill-stat-value" style="color: #10b981;"><?php echo $drill['completed'] ?? 0; ?></div>
                                            <div class="drill-stat-label">Completed</div>
                                        </div>
                                        <div class="drill-stat">
                                            <div class="drill-stat-value" style="color: #f59e0b;"><?php echo $drill['in_progress'] ?? 0; ?></div>
                                            <div class="drill-stat-label">In Progress</div>
                                        </div>
                                        <div class="drill-stat">
                                            <div class="drill-stat-value" style="color: #ef4444;"><?php echo $drill['not_started'] ?? 0; ?></div>
                                            <div class="drill-stat-label">Not Started</div>
                                        </div>
                                    </div>

                                    <?php 
                                    $drill_total = $drill['total_participants'] ?? 0;
                                    $drill_completion = $drill_total > 0 ? round(($drill['completed'] / $drill_total) * 100, 1) : 0;
                                    ?>
                                    <div style="margin-top: 15px;">
                                        <div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 5px;">
                                            Completion Rate: <?php echo $drill_completion; ?>%
                                        </div>
                                        <div class="progress-container">
                                            <div class="progress-bar" style="width: <?php echo $drill_completion; ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">📋</div>
                                <p class="empty-title">No drill data</p>
                                <p class="empty-text">There are no drills with participants from <?php echo htmlspecialchars($selected_barangay); ?> yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php endif; ?>

            <?php endif; ?>

        </div>
    </div>

</body>
</html>