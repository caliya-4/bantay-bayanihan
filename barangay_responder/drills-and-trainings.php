<?php 
session_start(); 
require '../db_connect.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','responder'])) {
    header("Location: ../login.php"); 
    exit;
}

$currentUserId = $_SESSION['user_id'];
$isAdmin = $_SESSION['role'] === 'admin';

// ── Resolve user's barangay ─────────────────────────────────────────────────
$user_barangay = null;
if (!$isAdmin) {
    $stmt = $pdo->prepare("SELECT address, barangay, email FROM users WHERE id = ?");
    $stmt->execute([$currentUserId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

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
}

// ── CREATE DRILL ──────────────────────────────────────────────────────────────
if (isset($_POST['create'])) {
    $title  = trim($_POST['title'] ?? '');
    $desc   = trim($_POST['desc']  ?? '');
    $inst   = trim($_POST['inst']  ?? '');
    $dur    = max(5, min(120, (int)($_POST['dur'] ?? 30)));
    $status = ($_POST['status'] === 'published') ? 'published' : 'draft';
    $drill_date = trim($_POST['drill_date'] ?? '');

    if ($title === '') {
        $alertMsg = "<div class='alert alert-error'>✖ Title is required.</div>";
    } else {
        $pdo->prepare("INSERT INTO drills (title, description, instructions, duration_minutes, status, created_by, barangay, drill_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$title, $desc, $inst, $dur, $status, $currentUserId, $user_barangay, $drill_date ?: null]);
        $alertMsg = "<div class='alert alert-success'>✓ Mission created successfully!</div>";
    }
}

// ── UPDATE DRILL ──────────────────────────────────────────────────────────────
if (isset($_POST['update'])) {
    $id    = (int)($_POST['edit_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $desc  = trim($_POST['desc']  ?? '');
    $inst  = trim($_POST['inst']  ?? '');
    $dur   = max(5, min(120, (int)($_POST['dur'] ?? 30)));
    $status = ($_POST['status'] === 'published') ? 'published' : 'draft';
    $drill_date = trim($_POST['drill_date'] ?? '');

    $owner = $pdo->prepare("SELECT created_by FROM drills WHERE id = ?");
    $owner->execute([$id]);
    $ownerId = $owner->fetchColumn();

    if ($ownerId != $currentUserId && !$isAdmin) {
        $alertMsg = "<div class='alert alert-error'>✖ You can only edit your own drills.</div>";
    } elseif ($title === '') {
        $alertMsg = "<div class='alert alert-error'>✖ Title is required.</div>";
    } else {
        $pdo->prepare("UPDATE drills SET title=?, description=?, instructions=?, duration_minutes=?, status=?, drill_date=? WHERE id=?")
            ->execute([$title, $desc, $inst, $dur, $status, $drill_date ?: null, $id]);
        header("Location: drills-and-trainings.php?updated=1");
        exit;
    }
}

// ── DELETE DRILL ──────────────────────────────────────────────────────────────
if (isset($_GET['del']) && is_numeric($_GET['del'])) {
    $id = (int)$_GET['del'];
    $owner = $pdo->prepare("SELECT created_by FROM drills WHERE id = ?");
    $owner->execute([$id]);
    $ownerId = $owner->fetchColumn();

    if ($ownerId == $currentUserId || $isAdmin) {
        $pdo->prepare("DELETE FROM drills WHERE id = ?")->execute([$id]);
        $alertMsg = "<div class='alert alert-success'>✓ Mission permanently deleted.</div>";
    } else {
        $alertMsg = "<div class='alert alert-error'>✖ You can only delete your own drills.</div>";
    }
}

if (isset($_GET['updated'])) {
    $alertMsg = "<div class='alert alert-success'>✓ Mission updated successfully!</div>";
}

// ── FETCH DRILL FOR EDITING ───────────────────────────────────────────────────
$editDrill = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM drills WHERE id = ?");
    $stmt->execute([$id]);
    $editDrill = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($editDrill && $editDrill['created_by'] != $currentUserId && !$isAdmin) {
        $editDrill = null;
        $alertMsg = "<div class='alert alert-error'>✖ You can only edit drills you created.</div>";
    }
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
            margin: 25px 0;
        }

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

        .mission-card h3 { color: var(--navy); font-size: 22px; margin: 0 0 14px; font-weight: 800; }
        .mission-card p  { color: var(--gray-700); margin: 6px 0; line-height: 1.6; }

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

        .participant-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #6161ff15, #00167a10);
            border: 1.5px solid #6161ff40;
            color: #00167a;
            font-size: 13px;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 20px;
            margin-bottom: 12px;
        }
        .participant-badge i { color: #6161ff; font-size: 13px; }

        .drill-meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 10px 0 6px;
        }
        .meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
        }
        .meta-pill.completed  { background: #d1fae5; color: #065f46; }
        .meta-pill.in-progress { background: #fef3c7; color: #92400e; }
        .meta-pill.not-started { background: #fee2e2; color: #991b1b; }

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
        .mission-actions a.edit  { color: var(--info);  border: 2px solid var(--info); }
        .mission-actions a.edit:hover  { background: var(--info);  color: white; }
        .mission-actions a.delete { color: var(--error); border: 2px solid var(--error); }
        .mission-actions a.delete:hover { background: var(--error); color: white; }

        .edit-panel {
            background: #fff8f0;
            border: 2px solid #f59e0b;
            border-radius: 20px;
            padding: 28px 32px;
            margin-bottom: 28px;
        }
        .edit-panel h3 { margin: 0 0 20px; color: #92400e; font-size: 18px; font-weight: 800; display: flex; align-items: center; gap: 8px; }

        .mission-card label, .edit-panel label { display: block; margin-bottom: 6px; font-weight: 700; font-size: 13px; color: #0f172a; }
        .form-control { width: 100%; padding: 10px 14px; border-radius: 10px; border: 2px solid rgba(15,23,42,.08); font-size: 14px; box-sizing: border-box; transition: border-color .2s; }
        .form-control:focus { outline: none; border-color: #6161ff; box-shadow: 0 0 0 3px rgba(97,97,255,.1); }

        .leaderboard-header {
            background: linear-gradient(135deg, #00167a 0%, #6161ff 100%);
            border-radius: 20px;
            padding: 36px 40px;
            margin-bottom: 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .leaderboard-header::before {
            content: '';
            position: absolute;
            right: -60px; top: -60px;
            width: 220px; height: 220px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
        }
        .leaderboard-header::after {
            content: '';
            position: absolute;
            right: 80px; bottom: -80px;
            width: 180px; height: 180px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
        }
        .leaderboard-header h1 {
            font-size: 30px;
            font-weight: 900;
            margin: 0 0 6px;
            position: relative;
            z-index: 1;
        }
        .leaderboard-header .drill-subtitle {
            font-size: 16px;
            opacity: 0.85;
            margin: 0 0 20px;
            position: relative;
            z-index: 1;
        }
        .drill-detail-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            position: relative;
            z-index: 1;
        }
        .drill-detail-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.25);
            color: white;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 20px;
        }
        .participant-count-hero {
            display: flex;
            gap: 16px;
            margin-top: 20px;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }
        .count-box {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 12px;
            padding: 12px 20px;
            text-align: center;
            min-width: 90px;
        }
        .count-box .count-num {
            font-size: 26px;
            font-weight: 900;
            display: block;
        }
        .count-box .count-label {
            font-size: 11px;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .empty-state {
            text-align: center; padding: 80px 20px;
            background: white; border-radius: 20px;
            color: var(--gray-500); font-size: 18px;
            border: 3px dashed var(--gray-200);
            margin: 40px auto; max-width: 700px;
        }
        .empty-state i { font-size: 48px; margin-bottom: 15px; opacity: .5; display: block; }

        @media (max-width: 992px) {
            .form-grid { grid-template-columns: 1fr; }
            .mission-actions { flex-direction: column; }
            .mission-actions a { width: 100%; text-align: center; justify-content: center; }
            .leaderboard-header { padding: 24px 20px; }
            .leaderboard-header h1 { font-size: 22px; }
            .count-box { min-width: 70px; padding: 10px 14px; }
        }
        
        .btn-outline-success {
            background: transparent;
            border: 2px solid #10b981;
            color: #10b981;
        }
        .btn-outline-success:hover {
            background: #10b981;
            color: white;
        }
        .btn-outline-primary {
            background: transparent;
            border: 2px solid #6161ff;
            color: #6161ff;
        }
        .btn-outline-primary:hover {
            background: #6161ff;
            color: white;
        }

        .badge-secondary {
            background: #f1f5f9;
            color: #64748b;
            border-radius: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">

<?php echo $alertMsg ?? ''; ?>

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

    $publicCount = (int)($counts['total'] ?? 0);
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
                        <th style="text-align:center;">Actions</th>
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
                        
                        <!-- Actions Column - Clean workflow -->
                        <td style="text-align:center;">
                            <div style="display:flex; flex-direction:column; gap:8px; align-items:center;">
                                
                                <?php if (!$isEntered): ?>
                                    <!-- Step 1: Check-in -->
                                    <button class="btn btn-sm btn-success" 
                                            onclick="updateParticipant(<?= $drill_id ?>, 'email', '<?= htmlspecialchars($p['email'], ENT_QUOTES) ?>', 'entered', 1)"
                                            style="padding:8px 16px; font-size:12px; font-weight:700; width:100%;">
                                        <i class="fas fa-door-open"></i> Check-in
                                    </button>
                                    
                                <?php elseif (!$hasStarted): ?>
                                    <!-- Step 2: Start Drill -->
                                    <button class="btn btn-sm btn-primary" 
                                            onclick="updateParticipant(<?= $drill_id ?>, 'email', '<?= htmlspecialchars($p['email'], ENT_QUOTES) ?>', 'start', 1)"
                                            style="padding:8px 16px; font-size:12px; font-weight:700; width:100%;">
                                        <i class="fas fa-play"></i> Start Drill
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" 
                                            onclick="updateParticipant(<?= $drill_id ?>, 'email', '<?= htmlspecialchars($p['email'], ENT_QUOTES) ?>', 'entered', 0)"
                                            style="padding:6px 12px; font-size:11px;">
                                        <i class="fas fa-undo"></i> Undo Check-in
                                    </button>
                                    
                                <?php elseif (!$isCompleted): ?>
                                    <!-- Step 3: Mark Complete -->
                                    <button class="btn btn-sm btn-success" 
                                            onclick="updateParticipant(<?= $drill_id ?>, 'email', '<?= htmlspecialchars($p['email'], ENT_QUOTES) ?>', 'complete', 1)"
                                            style="padding:8px 16px; font-size:12px; font-weight:700; width:100%;">
                                        <i class="fas fa-check-double"></i> Mark Complete
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="updateParticipant(<?= $drill_id ?>, 'email', '<?= htmlspecialchars($p['email'], ENT_QUOTES) ?>', 'start', 0)"
                                            style="padding:6px 12px; font-size:11px;">
                                        <i class="fas fa-undo"></i> Reset Start
                                    </button>
                                    
                                <?php else: ?>
                                    <!-- Completed -->
                                    <span class="badge badge-success" style="padding:8px 16px; font-size:12px; font-weight:700;">
                                        <i class="fas fa-trophy"></i> Completed
                                    </span>
                                    <button class="btn btn-sm btn-outline-secondary" 
                                            onclick="updateParticipant(<?= $drill_id ?>, 'email', '<?= htmlspecialchars($p['email'], ENT_QUOTES) ?>', 'entered', 0)"
                                            style="padding:6px 12px; font-size:11px; margin-top:4px;">
                                        <i class="fas fa-undo"></i> Undo Entry
                                    </button>
                                <?php endif; ?>
                                
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div style="text-align:center;margin-top:30px;">
        <a href="drills-and-trainings.php" class="btn btn-secondary">← Back to Drills & Trainings</a>
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
        <h1>🎯 Drills & Trainings</h1>
        <p>Manage disaster preparedness drills</p>
    </div>
</div>

<!-- ── EDIT PANEL ─────────────────────────────────────────────────────────── -->
<?php if ($editDrill): ?>
<div class="edit-panel">
    <h3><i class="fas fa-edit"></i> Editing: <?= htmlspecialchars($editDrill['title']) ?></h3>
    <form method="POST">
        <input type="hidden" name="edit_id" value="<?= $editDrill['id'] ?>">
        <div class="form-grid">
            <div>
                <label>Title *</label>
                <input name="title" required class="form-control" value="<?= htmlspecialchars($editDrill['title'], ENT_QUOTES) ?>">
            </div>
            <div>
                <label>Duration (minutes)</label>
                <input type="number" min="5" max="240" name="dur" class="form-control" value="<?= (int)$editDrill['duration_minutes'] ?>">
            </div>
            <div>
                <label>Drill Date</label>
                <input type="date" name="drill_date" class="form-control" value="<?= $editDrill['drill_date'] ?? '' ?>">
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
            <a href="drills-and-trainings.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- ── CREATE FORM ─────────────────────────────────────────────────────────── -->
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
                    <label>Drill Date</label>
                    <input type="date" name="drill_date" class="form-control"
                           value="<?= htmlspecialchars($_POST['drill_date'] ?? '', ENT_QUOTES) ?>">
                </div>
            </div>
            <div style="margin-top:14px;">
                <label>Description</label>
                <textarea name="desc" class="form-control" rows="3"
                          placeholder="Brief overview..."><?= htmlspecialchars($_POST['desc'] ?? '', ENT_QUOTES) ?></textarea>
            </div>
            <div style="margin-top:12px;">
                <label>Instructions</label>
                <textarea name="inst" class="form-control" rows="2"
                          placeholder="Step-by-step instructions..."><?= htmlspecialchars($_POST['inst'] ?? '', ENT_QUOTES) ?></textarea>
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
if (!$isAdmin && $user_barangay) {
    $query .= " AND d.barangay = ?";
    $params[] = $user_barangay;
}

// Apply filters
$month = $_GET['month'] ?? '';
$type = $_GET['type'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';

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
        // Show only archived drills
        $query .= " AND COALESCE(d.is_archived, 0) = 1";
    } elseif ($status_filter === 'active') {
        // Show only non-archived drills
        $query .= " AND COALESCE(d.is_archived, 0) = 0";
    } else {
        // Show by status (published/draft)
        $query .= " AND d.status = ?";
        $params[] = $status_filter;
    }
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
<?php else:
    foreach ($drills as $d):
        $isOwner = ($d['created_by'] == $currentUserId);
        $canEdit = $isOwner || $isAdmin;
        $isArchived = (int)($d['is_archived'] ?? 0);
        $statusBadge = $isArchived ? '📦 Archived' : ($d['status'] === 'published' ? '🔴 LIVE' : ucfirst($d['status']));
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

        <h3>
            <?= htmlspecialchars($d['title']) ?>
            <small class="<?= $statusColor ?>" style="display:inline-block;margin-left:6px;"><?= $statusBadge ?></small>
        </h3>

        <!-- Participant count badge -->
        <div>
            <span class="participant-badge">
                <i class="fas fa-users"></i>
                <?= $totalP ?> Participant<?= $totalP !== 1 ? 's' : '' ?>
            </span>
        </div>

        <!-- Status breakdown pills -->
        <?php if ($totalP > 0): ?>
        <div class="drill-meta-row">
            <?php if ($completedP > 0): ?>
                <span class="meta-pill completed"><i class="fas fa-check-circle"></i> <?= $completedP ?> Completed</span>
            <?php endif; ?>
            <?php if ($inProgressP > 0): ?>
                <span class="meta-pill in-progress"><i class="fas fa-spinner"></i> <?= $inProgressP ?> In Progress</span>
            <?php endif; ?>
            <?php if ($notStartedP > 0): ?>
                <span class="meta-pill not-started"><i class="fas fa-hourglass-start"></i> <?= $notStartedP ?> Not Started</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <p><strong>Duration:</strong> <?= (int)$d['duration_minutes'] ?> minutes</p>
        <p><strong>Created on:</strong> <?= date('M d, Y', strtotime($d['created_at'])) ?></p>
        <?php if ($d['drill_date']): ?>
            <p><strong>Drill Date:</strong> <?= date('M d, Y', strtotime($d['drill_date'])) ?><?php if ($isArchived): ?> <small style="color:#ef4444;">(Overdue)</small><?php endif; ?></p>
        <?php endif; ?>

        <div class="mission-actions">
            <a href="?leaderboard=<?= $d['id'] ?>" class="view-leaderboard">
                <i class="fas fa-users"></i> View Participants
            </a>

            <?php if ($canEdit): ?>
                <a href="?edit=<?= $d['id'] ?>" class="edit">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="?del=<?= $d['id'] ?>" class="delete"
                   onclick="return confirm('Delete this mission forever?')">
                    <i class="fas fa-trash"></i> Delete
                </a>
            <?php else: ?>
                <span style="color:#94a3b8;font-size:13px;font-weight:600;display:flex;align-items:center;gap:5px;">
                    <i class="fas fa-lock"></i> View only — not your drill
                </span>
            <?php endif; ?>
        </div>
    </div>
<?php
    endforeach;
endif;
?>

<?php endif; // end main list vs leaderboard ?>

</div>

<script>
function updateParticipant(drillId, type, identifier, action, value) {
    console.log('Updating participant:', {drillId, type, identifier, action, value});
    
    fetch('../api/drills/update-participant-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            drill_id: drillId,
            type: type,
            identifier: identifier,
            action: action,
            value: value
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred: ' + error.message);
    });
}
</script>

</body>
</html>