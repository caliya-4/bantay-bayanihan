<?php 
session_start(); 
require '../db_connect.php';
include '../includes/header.php';
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'],['admin','responder'])) {
    header("Location: ../login.php");
    exit;
}

// Approve / Reject logic
if(isset($_GET['approve'])){
    $id = (int)$_GET['approve'];
    $pdo->prepare("UPDATE users SET is_approved=1 WHERE id=?")->execute([$id]);
}
if(isset($_GET['reject'])){
    $id = (int)$_GET['reject'];
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Registrations | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/design-system.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .table-container {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .action-buttons .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .action-buttons .btn-approve {
            background-color: #28a745;
            color: #fff;
        }
        .action-buttons .btn-approve:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }
        .action-buttons .btn-reject {
            background-color: #dc3545;
            color: #fff;
        }
        .action-buttons .btn-reject:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <h1>Pending Registrations</h1>
    <p class="subtitle">Review and approve new users before they can access the system</p>

    <?php if(isset($_GET['approve'])): ?>
        <div class="msg msg-success">User successfully approved and activated!</div>
    <?php endif; ?>
    <?php if(isset($_GET['reject'])): ?>
        <div class="msg msg-danger">Registration rejected and permanently deleted.</div>
    <?php endif; ?>

    <?php
    $stmt = $pdo->query("SELECT * FROM users WHERE role IN ('resident','responder') AND is_approved=0 ORDER BY created_at DESC");
    if($stmt->rowCount() == 0): ?>
        <div class="empty-state">
            <i class="fas fa-check-circle"></i><br><br>
            <strong>No pending registrations</strong><br>
            <small>All new accounts have been reviewed.</small>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Action</th>
                </tr>
                <?php while($row = $stmt->fetch()): ?>
                <tr>
                    <td><strong><?=htmlspecialchars($row['name'])?></strong></td>
                    <td><?=htmlspecialchars($row['email'])?></td>
                    <td><?=htmlspecialchars($row['contact'] ?? '—')?></td>
                    <td><?=htmlspecialchars($row['address'] ?? '—')?></td>
                    <td>
                        <div class="action-buttons">
                        <a href="?approve=<?=$row['id']?>" class="btn btn-approve"
                           onclick="return confirm('Approve this user?')">Approve</a>
                        <a href="?reject=<?=$row['id']?>" class="btn btn-reject"
                           onclick="return confirm('PERMANENTLY delete this registration?')">Reject</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php endif; ?>
</div>

</body>
</html>