<?php
session_start();
require '../../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    // ── SUMMARY STATS ──────────────────────────────────────────────
    $summaryQuery = $conn->query("
        SELECT
            COUNT(DISTINCT d.id)                                                        AS total_drills,
            COUNT(DISTINCT dp.barangay)                                                 AS total_barangays,
            COUNT(dp.id)                                                                AS total_participants,
            IFNULL(
                COUNT(CASE WHEN dp2.status = 'completed' THEN 1 END) * 100.0
                / NULLIF(COUNT(dp.id), 0), 0
            )                                                                           AS avg_participation
        FROM drills d
        LEFT JOIN drill_participants dp ON dp.drill_id = d.id
        LEFT JOIN drill_participants dp2 ON dp2.drill_id = dp.drill_id AND dp2.user_id = (
            SELECT u.id FROM users u WHERE u.name = dp.name LIMIT 1
        )
    ");
    $summary = $summaryQuery->fetch_assoc();

    // ── SIMPLER SUMMARY (more reliable) ────────────────────────────
    $totalDrills     = $conn->query("SELECT COUNT(*) AS c FROM drills")->fetch_assoc()['c'];
    $totalBarangays  = $conn->query("SELECT COUNT(DISTINCT barangay) AS c FROM drill_participants WHERE barangay IS NOT NULL AND barangay != ''")->fetch_assoc()['c'];
    $totalParticipants = $conn->query("SELECT COUNT(*) AS c FROM drill_participants")->fetch_assoc()['c'];
    $totalCompleted  = $conn->query("SELECT COUNT(*) AS c FROM drill_participants WHERE status = 'completed'")->fetch_assoc()['c'];
    $avgParticipation = $totalParticipants > 0 ? ($totalCompleted / $totalParticipants) * 100 : 0;

    // ── PER-BARANGAY STATS ─────────────────────────────────────────
    // Uses drill_participants (has barangay column directly)
    $barangayQuery = $conn->query("
        SELECT
            dp.barangay,
            COUNT(dp.id)                                                                AS total_participants,
            COUNT(dpt.id)                                                               AS completed,
            COUNT(CASE WHEN dpt.status = 'in_progress' THEN 1 END)                    AS in_progress,
            IFNULL(
                COUNT(dpt.id) * 100.0 / NULLIF(COUNT(dp.id), 0), 0
            )                                                                           AS participation_rate
        FROM drill_participants dp
        LEFT JOIN drill_participants dpt 
            ON dpt.drill_id = dp.drill_id 
            AND dpt.status = 'completed'
            AND dpt.user_id IN (
                SELECT u.id FROM users u WHERE u.name = dp.name
            )
        WHERE dp.barangay IS NOT NULL AND dp.barangay != ''
        GROUP BY dp.barangay
        ORDER BY total_participants DESC
    ");

    $barangayStats = [];
    while ($row = $barangayQuery->fetch_assoc()) {
        $barangayStats[] = [
            'barangay'          => $row['barangay'],
            'total_participants'=> (int)$row['total_participants'],
            'completed'         => (int)$row['completed'],
            'in_progress'       => (int)$row['in_progress'],
            'participation_rate'=> round((float)$row['participation_rate'], 1),
        ];
    }

    // ── DRILL BREAKDOWN PER BARANGAY ───────────────────────────────
    $drillQuery = $conn->query("
        SELECT
            dp.barangay,
            d.id            AS drill_id,
            d.title,
            d.drill_date,
            d.status        AS drill_status,
            u.name          AS creator,
            COUNT(dp.id)    AS total_participants
        FROM drills d
        JOIN drill_participants dp ON dp.drill_id = d.id
        LEFT JOIN users u ON u.id = d.created_by
        WHERE dp.barangay IS NOT NULL AND dp.barangay != ''
        GROUP BY dp.barangay, d.id
        ORDER BY dp.barangay, d.created_at DESC
    ");

    $drillBreakdown = [];
    while ($row = $drillQuery->fetch_assoc()) {
        // Count completed for this drill+barangay
        $drillId   = (int)$row['drill_id'];
        $barangay  = $row['barangay'];

        $compQ = $conn->query("
            SELECT COUNT(*) AS c
            FROM drill_participants dpt
            JOIN drill_participants dp ON dp.drill_id = dpt.drill_id
            WHERE dpt.drill_id = $drillId
              AND dpt.status = 'completed'
              AND dp.barangay = '" . $conn->real_escape_string($barangay) . "'
        ");
        $completed   = $compQ ? (int)$compQ->fetch_assoc()['c'] : 0;
        $inProgress  = max(0, (int)$row['total_participants'] - $completed);

        $drillBreakdown[$barangay][] = [
            'drill_id'          => $drillId,
            'title'             => $row['title'],
            'drill_date'        => $row['drill_date'],
            'creator'           => $row['creator'] ?? 'Admin',
            'total_participants'=> (int)$row['total_participants'],
            'completed'         => $completed,
            'in_progress'       => $inProgress,
        ];
    }

    echo json_encode([
        'success'         => true,
        'summary'         => [
            'total_drills'       => (int)$totalDrills,
            'total_barangays'    => (int)$totalBarangays,
            'total_participants' => (int)$totalParticipants,
            'avg_participation'  => round($avgParticipation, 1),
        ],
        'barangay_stats'  => $barangayStats,
        'drill_breakdown' => $drillBreakdown,
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>