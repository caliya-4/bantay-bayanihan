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
    <title>Responders | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/design-system.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <h1>Approved Responders</h1>
    <p class="subtitle">Complete list of all verified and active residents in the system</p>

    <?php
    $stmt = $pdo->query("SELECT * FROM users WHERE role='responder' AND is_approved=1 ORDER BY created_at DESC");
    $total = $stmt->rowCount();
    ?>

    <?php if ($total > 0): ?>
        <table class="residents-table">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>Contact Number</th>
                    <th>Complete Address</th>
                    <th>Date Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['contact'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($row['address'] ?? '—') ?></td>
                        <td><strong><?= date('M d, Y', strtotime($row['created_at'])) ?></strong></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div style="text-align:center;margin-top:30px;font-size:20px;color:#CC0000;font-weight:900;">
            Total Approved Responders: <strong><?= $total ?></strong>
        </div>

    <?php else: ?>
        <div class="no-residents">
            <i class="fas fa-users-slash"></i><br><br>
            <strong>No approved responders yet.</strong><br>
            <span style="color:#777;font-size:18px;">Responders will appear here once they register and are approved.</span>
        </div>
    <?php endif; ?>
</div>

</body>
</html>