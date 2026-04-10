<?php 
session_start(); 
require '../db_connect.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','responder'])) {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo "<div class='main-content'><h1 style='color:#CC0000;'>Error</h1><p>Invalid drill ID.</p></div>";
    exit;
}

$stmt = $pdo->prepare("SELECT title FROM drills WHERE id = ?");
$stmt->execute([$id]);
$drill = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$drill) {
    echo "<div class='main-content'><h1 style='color:#CC0000;'>Error</h1><p>Drill not found.</p></div>";
    exit;
}
$title = $drill['title'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Drill - <?= htmlspecialchars($title) ?> | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/design-system.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
        }
        .main-content {
            margin-left: 270px;
            padding: 40px 35px;
            min-height: calc(100vh - 70px);
        }

        /* SCARLET RED TITLE */
        .main-content h1 {
            color: #CC0000;
            font-size: 36px;
            font-weight: 900;
            text-align: center;
            margin: 0 0 12px 0;
            text-shadow: 0 3px 8px rgba(0,0,0,0.2);
        }
        .main-content h1::after {
            content: '';
            display: block;
            width: 160px;
            height: 7px;
            background: #CC0000;
            margin: 14px auto 0;
            border-radius: 4px;
        }

        .back-link {
            display: inline-block;
            margin: 20px 0;
            color: #CC0000;
            font-weight: bold;
            font-size: 17px;
            text-decoration: none;
            border-bottom: 2px solid #CC0000;
            padding-bottom: 4px;
        }
        .back-link:hover { color: #B30000; }

        /* SCARLET RED TABLE */
        table {
            width: 100%;
            max-width: 1200px;
            margin: 30px auto;
            border-collapse: collapse;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 45px rgba(204,0,0,0.25);
            border: 5px solid #CC0000;
        }
        th {
            background: linear-gradient(135deg, #CC0000, #B30000);
            color: white;
            padding: 22px 15px;
            text-align: center;
            font-weight: 900;
            font-size: 17px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        td {
            padding: 18px 15px;
            text-align: center;
            border-bottom: 1px solid #ffe6e6;
            font-size: 16px;
        }
        tr:hover {
            background: #fff5f5;
            transition: background 0.3s;
        }

        /* STATUS COLORS - SCARLET THEME */
        .status-completed     { color: #16a34a; font-weight: 900; }
        .status-in_progress   { color: #f97316; font-weight: 900; }
        .status-failed        { color: #dc2626; font-weight: 900; }
        .status-not_started   { color: #94a3b8; font-weight: 900; }

        /* SUMMARY CARD */
        .summary-card {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 20px;
            border: 4px solid #CC0000;
            box-shadow: 0 15px 40px rgba(204,0,0,0.2);
            text-align: center;
        }
        .summary-card h3 {
            color: #CC0000;
            font-size: 28px;
            margin: 0 0 20px 0;
            font-weight: 900;
        }
        .summary-card p {
            font-size: 18px;
            margin: 12px 0;
        }
        .summary-card strong {
            font-size: 24px;
        }

        .no-data {
            text-align: center;
            padding: 80px 20px;
            background: #fff5f5;
            border-radius: 20px;
            border: 3px dashed #CC0000;
            color: #999;
            font-size: 20px;
            margin: 40px auto;
            max-width: 700px;
        }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; padding: 25px 15px; }
            table { font-size: 14px; }
            th, td { padding: 12px 8px; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <h1>Monitoring: <?= htmlspecialchars($title) ?></h1>
    <p style="text-align:center;font-size:18px;color:#555;">
        <strong>Drill ID:</strong> #<?= $id ?> | 
        <a href="drill-management.php" class="back-link">Back to Mission Control</a>
    </p>
    <?php
    // Get registered user participants
    $stmt = $pdo->prepare("SELECT u.name, u.email, dp.status, dp.started_at, dp.finished_at, 'registered' as type FROM drill_participants dp JOIN users u ON dp.user_id = u.id WHERE dp.drill_id = ? ORDER BY dp.started_at DESC");
    $stmt->execute([$id]);
    $registeredParticipants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get public participants (from join drill form)
    $stmt = $pdo->prepare("SELECT name, email, NULL as status, joined_at as started_at, NULL as finished_at, 'public' as type FROM drill_participants WHERE drill_id = ? ORDER BY joined_at DESC");
    $stmt->execute([$id]);
    $publicParticipants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Combine participants
    $participants = array_merge($registeredParticipants, $publicParticipants);

    if (empty($participants)) {
        echo "<div class='no-data'>\
                No one has participated in this mission yet.<br><br>\
                <small>Participation will appear here in real-time as residents complete the drill.</small>\
              </div>";
    } else {
        echo "<table>\
                <thead>\
                    <tr>\
                        <th>Resident</th>\
                        <th>Email</th>\
                        <th>Status</th>\
                        <th>Started</th>\
                        <th>Finished</th>\
                        <th>Type</th>\
                    </tr>\
                </thead>\
                <tbody>";

        foreach ($participants as $r) {
            $statusClass = $r['status'] ? 'status-' . str_replace('_', '-', $r['status']) : 'status-not-started';
            $started = $r['started_at'] ? date('M d, Y h:i A', strtotime($r['started_at'])) : '—';
            $finished = $r['finished_at'] ? date('M d, Y h:i A', strtotime($r['finished_at'])) : '—';
            $typeLabel = $r['type'] === 'registered' ? '👤 Registered' : '👥 Public';
            $statusLabel = $r['status'] ? ucfirst(str_replace('_', ' ', $r['status'])) : 'Joined';

            echo "<tr>\
                <td><strong>" . htmlspecialchars($r['name']) . "</strong></td>\
                <td>" . htmlspecialchars($r['email']) . "</td>\
                <td class='$statusClass'>" . $statusLabel . "</td>\
                <td>$started</td>\
                <td>$finished</td>\
                <td>$typeLabel</td>\
            </tr>";
        }
        echo "</tbody></table>";

        // Summary
        $total = count($participants);
        $registered = count($registeredParticipants);
        $public = count($publicParticipants);
        $completed = count(array_filter($registeredParticipants, fn($p) => $p['status'] === 'completed'));
        $inProgress = count(array_filter($registeredParticipants, fn($p) => $p['status'] === 'in_progress'));
        $failed = count(array_filter($registeredParticipants, fn($p) => $p['status'] === 'failed'));
        $successRate = $registered > 0 ? round(($completed / $registered) * 100) : 0;

        echo "<div class='summary-card'>\
                <h3>Drill Performance Summary</h3>\
                <p><strong>Total Participants:</strong> <strong style='font-size:28px;color:#CC0000;'>$total</strong></p>\
                <p><strong>Registered Users:</strong> <strong>$registered</strong> | <strong>Public Participants:</strong> <strong>$public</strong></p>\
                <p><strong>Status Breakdown (Registered):</strong> <span style='color:#16a34a;font-size:18px;'>✓ $completed Completed</span> | \
                   <span style='color:#f97316;font-size:18px;'>↻ $inProgress In Progress</span> | \
                   <span style='color:#dc2626;font-size:18px;'>✕ $failed Failed</span></p>\
                <p><strong>Overall Success Rate (Registered):</strong> \
                   <span style='color:" . ($successRate >= 80 ? '#16a34a' : ($successRate >= 60 ? '#f97316' : '#CC0000')) . ";font-size:32px;font-weight:900;'>\
                       $successRate%\
                   </span>\
                </p>\
              </div>";
    }
    ?>
</div>

</body>
</html>