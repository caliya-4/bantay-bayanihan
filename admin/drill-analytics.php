<?php 
session_start(); 
require '../db_connect.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ── Overall stats ─────────────────────────────────────────────────────────────
$totalDrills      = $pdo->query("SELECT COUNT(*) FROM drills")->fetchColumn();
$publishedDrills  = $pdo->query("SELECT COUNT(*) FROM drills WHERE status='published'")->fetchColumn();
$draftDrills      = $pdo->query("SELECT COUNT(*) FROM drills WHERE status='draft'")->fetchColumn();

$totalParticipants = $pdo->query("
    SELECT COUNT(*) FROM (
        SELECT id FROM drill_participants
        UNION ALL
        SELECT id FROM drill_participations
    ) combined
")->fetchColumn();

$totalBarangays = $pdo->query("
    SELECT COUNT(DISTINCT barangay) FROM (
        SELECT barangay FROM drill_participants WHERE barangay IS NOT NULL AND barangay != ''
        UNION
        SELECT barangay FROM drill_participations WHERE barangay IS NOT NULL AND barangay != ''
    ) combined
")->fetchColumn();

$totalCompleted = $pdo->query("
    SELECT COUNT(*) FROM drill_participations WHERE status = 'completed'
")->fetchColumn();

$avgRate = ($totalParticipants > 0 && $totalDrills > 0)
    ? round(($totalCompleted / $totalParticipants) * 100, 1)
    : 0;

// ── Barangay participation summary ────────────────────────────────────────────
$barangayStats = $pdo->query("
    SELECT barangay,
           SUM(total_participants) AS total_participants,
           SUM(completed) AS completed,
           SUM(in_progress) AS in_progress
    FROM (
        SELECT barangay,
               COUNT(*) AS total_participants,
               0 AS completed,
               0 AS in_progress
        FROM drill_participants
        WHERE barangay IS NOT NULL AND barangay != ''
        GROUP BY barangay
        UNION ALL
        SELECT barangay,
               COUNT(*) AS total_participants,
               SUM(status = 'completed') AS completed,
               SUM(status = 'in_progress') AS in_progress
        FROM drill_participations
        WHERE barangay IS NOT NULL AND barangay != ''
        GROUP BY barangay
    ) AS combined
    GROUP BY barangay
    ORDER BY total_participants DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ── Drill breakdown per barangay ──────────────────────────────────────────────
$drills = $pdo->query("
    SELECT d.id, d.title, d.drill_date, d.drill_place, d.barangay, u.name AS creator
    FROM drills d
    LEFT JOIN users u ON d.created_by = u.id
    WHERE d.status = 'published'
    ORDER BY d.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// For each drill, get participant counts per barangay
$drillBreakdown = [];
foreach ($drills as $drill) {
    $stmt = $pdo->prepare("
        SELECT 
            barangay,
            COUNT(*) AS total_participants
        FROM drill_participants
        WHERE drill_id = ? AND barangay IS NOT NULL AND barangay != ''
        GROUP BY barangay
        ORDER BY total_participants DESC
    ");
    $stmt->execute([$drill['id']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Also get registered user participations
    $stmt2 = $pdo->prepare("
        SELECT 
            dp.barangay AS barangay,
            COUNT(*) AS total,
            SUM(CASE WHEN dp.status='completed' THEN 1 ELSE 0 END) AS completed,
            SUM(CASE WHEN dp.status='in_progress' THEN 1 ELSE 0 END) AS in_progress
        FROM drill_participations dp
        WHERE dp.drill_id = ? AND dp.barangay IS NOT NULL AND dp.barangay != ''
        GROUP BY dp.barangay
    ");
    $stmt2->execute([$drill['id']]);
    $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $drillBreakdown[] = [
        'title'      => $drill['title'],
        'drill_date' => $drill['drill_date'],
        'drill_place'=> $drill['drill_place'],
        'barangay'   => $drill['barangay'] ?: 'All Barangays',
        'creator'    => $drill['creator'],
        'public_participants' => $rows,
        'registered_participants' => $rows2,
        'total_public' => array_sum(array_column($rows, 'total_participants')),
        'total_registered' => array_sum(array_column($rows2, 'total')),
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drill Analytics by Barangay | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/design-system.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 15px rgba(0,22,122,.08);
            border-left: 4px solid #6161ff;
        }

        .stat-card.published {
            border-left-color: #10b981;
        }

        .stat-card.drafts {
            border-left-color: #f59e0b;
        }

        .stat-card h3 {
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            margin: 0 0 8px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 900;
            color: #00167a;
            margin: 0;
            line-height: 1;
        }

        .stat-card.published .stat-value { color: #065f46; }
        .stat-card.drafts   .stat-value { color: #92400e; }

        .analytics-section {
            background: white;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 4px 15px rgba(0,22,122,.08);
            margin-bottom: 28px;
        }

        .section-title {
            color: #00167a;
            font-size: 20px;
            font-weight: 900;
            margin: 0 0 22px;
            padding-bottom: 14px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-section {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-section label { font-weight: 700; color: #475569; font-size: 14px; }

        .filter-section select {
            padding: 8px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            outline: none;
        }

        .filter-section select:focus { border-color: #6161ff; }

        /* Table */
        .ana-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .ana-table th {
            background: #f1f5f9;
            padding: 12px 14px;
            text-align: left;
            font-weight: 700;
            color: #475569;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 2px solid #e2e8f0;
        }
        .ana-table td { padding: 13px 14px; border-bottom: 1px solid #f0f4ff; color: #374151; vertical-align: middle; }
        .ana-table tr:hover td { background: #f8faff; }
        .ana-table tr:last-child td { border-bottom: none; }

        .barangay-name { font-weight: 700; color: #00167a; }

        .progress-container { width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; min-width: 80px; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #6161ff, #ff0065); border-radius: 4px; }

        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; white-space: nowrap; }
        .badge-high   { background: #d1fae5; color: #065f46; }
        .badge-medium { background: #fef3c7; color: #92400e; }
        .badge-low    { background: #fee2e2; color: #7f1d1d; }

        /* Drill cards */
        .drill-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 14px;
            transition: box-shadow .2s;
        }
        .drill-card:hover { box-shadow: 0 6px 20px rgba(97,97,255,.12); }

        .drill-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; flex-wrap: wrap; gap: 10px; }
        .drill-title { font-weight: 800; color: #00167a; font-size: 15px; margin: 0; }
        .drill-meta { font-size: 12px; color: #94a3b8; margin: 4px 0 0; }

        .brgy-tag {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(97,97,255,.1); color: #6161ff;
            border-radius: 20px; padding: 3px 10px;
            font-size: 12px; font-weight: 700;
        }

        .participation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .participation-row {
            background: white;
            border: 1px solid #e8eeff;
            border-radius: 10px;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .participation-row .brgy { font-weight: 700; color: #1e293b; font-size: 13px; }
        .participation-row .count { font-weight: 900; color: #6161ff; font-size: 16px; }

        .empty-state { text-align: center; padding: 50px 20px; color: #94a3b8; }
        .empty-state i { font-size: 40px; margin-bottom: 12px; opacity: .4; display: block; }

        @media (max-width: 768px) {
            .analytics-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="page-header" style="margin-bottom:28px;">
        <div class="page-header-content">
            <h1>📊 Drill Analytics by Barangay</h1>
            <p>Participation summary and drill breakdown across Baguio City barangays</p>
        </div>
    </div>

    <!-- ── Overall Stats ─────────────────────────────────────────────── -->
    <div class="analytics-grid">

        <!-- NEW: Total Drills (All) -->
        <div class="stat-card">
            <h3>Total Drills (All)</h3>
            <p class="stat-value"><?= $totalDrills ?></p>
        </div>

        <!-- NEW: Published -->
        <div class="stat-card published">
            <h3>Published</h3>
            <p class="stat-value"><?= $publishedDrills ?></p>
        </div>

        <!-- NEW: Drafts -->
        <div class="stat-card drafts">
            <h3>Drafts</h3>
            <p class="stat-value"><?= $draftDrills ?></p>
        </div>

        <!-- Existing cards below — unchanged -->
        <div class="stat-card">
            <h3>Barangays Participating</h3>
            <p class="stat-value"><?= $totalBarangays ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Participants</h3>
            <p class="stat-value"><?= $totalParticipants ?></p>
        </div>
        <div class="stat-card">
            <h3>Avg Completion Rate</h3>
            <p class="stat-value"><?= $avgRate ?>%</p>
        </div>
    </div>

    <!-- ── Barangay Summary Table ─────────────────────────────────────── -->
    <div class="analytics-section">
        <h2 class="section-title"><i class="fas fa-table" style="color:#6161ff"></i> Barangay Drill Participation Summary</h2>

        <div class="filter-section">
            <label>Sort by:</label>
            <select id="sort-by" onchange="sortTable()">
                <option value="participants-desc">Most Participants</option>
                <option value="completed-desc">Most Completed</option>
                <option value="name-asc">Barangay Name (A-Z)</option>
            </select>
        </div>

        <?php if (empty($barangayStats)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>No participation data yet. Data will appear once residents join drills.</p>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="ana-table" id="brgy-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Barangay</th>
                        <th>Total Participants</th>
                        <th>Completed</th>
                        <th>In Progress</th>
                        <th>Completion Rate</th>
                    </tr>
                </thead>
                <tbody id="brgy-tbody">
                <?php 
                $maxPart = max(array_column($barangayStats, 'total_participants')) ?: 1;
                foreach ($barangayStats as $i => $b):
                    $rate = $b['total_participants'] > 0
                        ? round(($b['completed'] / $b['total_participants']) * 100, 1)
                        : 0;
                    $badgeClass = $rate >= 70 ? 'badge-high' : ($rate >= 40 ? 'badge-medium' : 'badge-low');
                    $pct = round(($b['total_participants'] / $maxPart) * 100);
                ?>
                <tr data-participants="<?= $b['total_participants'] ?>"
                    data-completed="<?= $b['completed'] ?>"
                    data-name="<?= htmlspecialchars($b['barangay']) ?>">
                    <td style="color:#94a3b8;font-weight:700;"><?= $i + 1 ?></td>
                    <td class="barangay-name"><?= htmlspecialchars($b['barangay']) ?></td>
                    <td><strong><?= $b['total_participants'] ?></strong></td>
                    <td><?= $b['completed'] ?></td>
                    <td><?= $b['in_progress'] ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="progress-container">
                                <div class="progress-bar" style="width:<?= $rate ?>%"></div>
                            </div>
                            <span class="badge <?= $badgeClass ?>"><?= $rate ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── Drill Breakdown ────────────────────────────────────────────── -->
    <div class="analytics-section">
        <h2 class="section-title"><i class="fas fa-clipboard-list" style="color:#6161ff"></i> Drill Breakdown by Barangay</h2>

        <?php if (empty($drillBreakdown)): ?>
        <div class="empty-state">
            <i class="fas fa-clipboard"></i>
            <p>No published drills found.</p>
        </div>
        <?php else: ?>
        <?php foreach ($drillBreakdown as $d): 
            $totalForDrill = $d['total_public'] + $d['total_registered'];
        ?>
        <div class="drill-card">
            <div class="drill-card-header">
                <div>
                    <p class="drill-title"><?= htmlspecialchars($d['title']) ?></p>
                    <p class="drill-meta">
                        <?php if($d['drill_date']): ?>
                            <i class="fas fa-calendar-alt"></i> <?= date('M j, Y', strtotime($d['drill_date'])) ?> &nbsp;
                        <?php endif; ?>
                        <?php if($d['drill_place']): ?>
                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($d['drill_place']) ?> &nbsp;
                        <?php endif; ?>
                        <i class="fas fa-user"></i> <?= htmlspecialchars($d['creator']) ?>
                    </p>
                </div>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <span class="brgy-tag"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($d['barangay']) ?></span>
                    <span style="background:rgba(16,185,129,.1);color:#059669;border-radius:20px;padding:3px 10px;font-size:12px;font-weight:700;">
                        <i class="fas fa-users"></i> <?= $totalForDrill ?> total
                    </span>
                </div>
            </div>

            <?php if (!empty($d['public_participants'])): ?>
            <p style="font-size:12px;font-weight:700;color:#64748b;margin:8px 0 6px;text-transform:uppercase;letter-spacing:.5px;">Participants by Barangay</p>
            <div class="participation-grid">
                <?php foreach ($d['public_participants'] as $p): ?>
                <div class="participation-row">
                    <span class="brgy"><?= htmlspecialchars($p['barangay']) ?></span>
                    <span class="count"><?= $p['total_participants'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p style="color:#94a3b8;font-size:13px;margin:8px 0 0;">No barangay participation data for this drill yet.</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<script>
function sortTable() {
    const sortBy = document.getElementById('sort-by').value;
    const tbody = document.getElementById('brgy-tbody');
    if (!tbody) return;
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        if (sortBy === 'participants-desc') {
            return parseInt(b.dataset.participants) - parseInt(a.dataset.participants);
        } else if (sortBy === 'completed-desc') {
            return parseInt(b.dataset.completed) - parseInt(a.dataset.completed);
        } else {
            return a.dataset.name.localeCompare(b.dataset.name);
        }
    });

    // Re-number after sort
    rows.forEach((row, i) => {
        row.cells[0].textContent = i + 1;
        tbody.appendChild(row);
    });
}
</script>

</body>
</html>