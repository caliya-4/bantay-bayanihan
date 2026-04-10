<?php 
session_start(); 
require '../db_connect.php';
include '../includes/header.php';
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'],['admin','responder'])) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drill Performance | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/design-system.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
            text-shadow: 0 3px 8px rgba(0,0,0,0.2);
        }
        .main-content h1::after {
            content: '';
            display: block;
            width: 140px;
            height: 7px;
            background: #CC0000;
            margin: 14px auto 0;
            border-radius: 4px;
        }

        .subtitle {
            text-align: center;
            color: #555;
            font-size: 18px;
            margin-bottom: 40px;
        }

        /* SCARLET RED TABLE */
        table {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            border-collapse: collapse;
            background: white;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(204,0,0,0.25);
            border: 4px solid #CC0000;
        }
        th {
            background: linear-gradient(135deg, #CC0000, #B30000);
            color: white;
            padding: 20px 15px;
            text-align: center;
            font-weight: 800;
            font-size: 17px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        td {
            padding: 18px 15px;
            text-align: center;
            border-bottom: 1px solid #eee;
            font-size: 16px;
        }
        tr:hover {
            background: #fff5f5;
        }
        tr:last-child td {
            border-bottom: none;
        }

        /* Success Rate Highlight */
        td:nth-child(5) b {
            font-size: 20px;
            font-weight: 900;
        }
        /* 90%+ = Gold, 70%+ = Green, below = Red */
        td:nth-child(5) b {
            color: #CC0000;
        }
        tr td:nth-child(5) b {
            color: #16a34a;
        }
        tr td:nth-child(5) b[style*="100"] { color: #f59e0b; } /* 100% gold */

        /* Empty state */
        .no-data {
            text-align: center;
            padding: 80px;
            color: #999;
            font-size: 19px;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <h1>Drill Performance Report</h1>
    <p class="subtitle">Resident participation and success rate across all safety drills</p>

    <table>
        <thead>
            <tr>
                <th>Resident Name</th>
                <th>Total Drills</th>
                <th>Completed</th>
                <th>Failed</th>
                <th>Success Rate</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("SELECT u.name,
                   COUNT(dp.id) as total,
                   SUM(CASE WHEN dp.status='completed' THEN 1 ELSE 0 END) as completed,
                   SUM(CASE WHEN dp.status='failed' THEN 1 ELSE 0 END) as failed
                   FROM users u
                   LEFT JOIN drill_participants dp ON u.id = dp.user_id
                   WHERE u.role='resident' AND u.is_approved=1
                   GROUP BY u.id 
                   ORDER BY completed DESC, name ASC");

            if ($stmt->rowCount() == 0):
                echo '<tr><td colspan="5" class="no-data">No drill data available yet.</td></tr>';
            else:
                while($r = $stmt->fetch()):
                    $rate = $r['total'] ? round($r['completed'] / $r['total'] * 100) : 0;
            ?>
                <tr>
                    <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
                    <td><?= $r['total'] ?></td>
                    <td style="color:#16a34a;font-weight:900;"><?= $r['completed'] ?></td>
                    <td style="color:#dc2626;font-weight:900;"><?= $r['failed'] ?></td>
                    <td>
                        <b style="color: <?= $rate >= 90 ? '#f59e0b' : ($rate >= 70 ? '#16a34a' : '#CC0000') ?>;">
                            <?= $rate ?>%
                        </b>
                    </td>
                </tr>
            <?php 
                endwhile;
            endif;
            ?>
        </tbody>
    </table>
</div>

</body>
</html>