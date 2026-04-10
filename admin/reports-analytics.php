<?php session_start(); require '../db_connect.php';
include '../includes/header.php';
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'],['admin','responder'])) header("Location: ../login.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/design-system.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head><body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <h1>Reports & Analytics</h1>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;">
        <div class="card">
            <h3>Total Emergencies</h3><h2><?=$pdo->query("SELECT COUNT(*) FROM emergencies")->fetchColumn()?></h2>
        </div>
        <div class="card">
            <h3>Pending</h3><h2 style="color:#CC0000"><?=$pdo->query("SELECT COUNT(*) FROM emergencies WHERE status='pending'")->fetchColumn()?></h2>
        </div>
        <div class="card">
            <h3>Resolved/Handled Today</h3><h2 style="color:#CC0000"><?=$pdo->query("SELECT COUNT(*) FROM emergencies WHERE status IN ('resolved','handled') AND DATE(created_at)=CURDATE()")->fetchColumn()?></h2>
        </div>
        <div class="card">
            <h3>Active Residents</h3><h2><?=$pdo->query("SELECT COUNT(*) FROM users WHERE role='resident' AND is_approved=1")->fetchColumn()?></h2>
        </div>
    </div>

    <h2 style="margin-top:50px;">Emergency Types</h2>
    <table>
        <tr><th>Type</th><th>Count</th></tr>
        <?php
        $stmt = $pdo->query("SELECT type, COUNT(*) c FROM emergencies GROUP BY type");
        while($r=$stmt->fetch()) echo "<tr><td>{$r['type']}</td><td><b>{$r['c']}</b></td></tr>";
        ?>
    </table>
</div>
</body></html>