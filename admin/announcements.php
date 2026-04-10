<?php 
session_start(); 
require '../db_connect.php';

if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'],['admin','responder'])) {
    header("Location: ../login.php");
    exit;
}

// Create announcement
if(isset($_POST['post'])){
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    if($title && $message){
        $stmt = $pdo->prepare("INSERT INTO announcements (title, message, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$title, $message, $_SESSION['user_id']]);
        $_SESSION['success_message'] = "Announcement posted successfully!";
        header("Location: announcements.php");
        exit;
    }
}

// Delete announcement
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM announcements WHERE id=?")->execute([$id]);
    $_SESSION['delete_message'] = "Announcement deleted";
    header("Location: announcements.php");
    exit;
}

include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/design-system.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <div class="page-header">
        <div class="page-header-content">
            <h1><i class="fas fa-bullhorn"></i> Announcements</h1>
            <p>Create and manage public announcements</p>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['delete_message'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-trash"></i> <?php echo $_SESSION['delete_message']; unset($_SESSION['delete_message']); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-pen-fancy"></i> Post New Announcement</h2>
        </div>
        <form method="POST">
            <div class="form-group">
                <label><strong>Announcement Title</strong></label>
                <input type="text" name="title" placeholder="Enter title" required>
            </div>

            <div class="form-group">
                <label><strong>Message</strong></label>
                <textarea name="message" placeholder="Write your announcement here..." rows="6" required></textarea>
            </div>

            <button type="submit" name="post" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> POST ANNOUNCEMENT
            </button>
        </form>
    </div>

    <h2 style="margin-top: 50px; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 3px solid #ddd;">
        <i class="fas fa-history"></i> Previous Announcements
    </h2>
    <?php
    $stmt = $pdo->query("SELECT a.*, u.name FROM announcements a JOIN users u ON a.created_by=u.id ORDER BY a.created_at DESC");
    
    if($stmt->rowCount() == 0){
        echo "<div class='empty-state'>
                <i class='fas fa-inbox'></i>
                <p>No announcements yet. Post your first announcement above!</p>
              </div>";
    }
    
    while($a = $stmt->fetch()){
        echo "<div class='card'>
                <div class='card-content'>
                    <h3>".htmlspecialchars($a['title'])."</h3>
                    <div style='color: #666; font-size: 14px; margin-bottom: 15px;'>
                        <i class='fas fa-user'></i> Posted by <strong>{$a['name']}</strong> on " . date('M d, Y \a\t h:i A', strtotime($a['created_at'])) . "
                    </div>
                    <p style='line-height: 1.6;'>".nl2br(htmlspecialchars($a['message']))."</p>
                    <div style='margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee;'>
                        <a href='?delete={$a['id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Delete this announcement permanently?')\">
                            <i class='fas fa-trash'></i> Delete
                        </a>
                    </div>
                </div>
            </div>";
    }
    ?>
</div>
</body>
</html>