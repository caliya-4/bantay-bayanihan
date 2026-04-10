<?php 
session_start(); 
require '../db_connect.php';
include '../includes/header.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'responder') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | Bantay Bayanihan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --pink: #ff0065;
            --purple: #6161ff;
            --navy: #00167a;
            --gradient-primary: linear-gradient(135deg, #6161ff, #ff0065);
            --gradient-secondary: linear-gradient(135deg, #00167a, #6161ff);
            --gradient-accent: linear-gradient(135deg, #ff0065, #ff1a75);
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f0f4ff 0%, #fef3f8 100%);
            margin: 0;
            min-height: 100vh;
        }

        .main-content {
            margin-left: 280px;
            padding: 40px 35px;
            min-height: calc(100vh - 75px);
            transition: margin-left 0.3s ease;
        }
        .page-header {
            background: var(--gradient-accent);
            padding: 50px 40px;
            margin: -40px -35px 40px;
            border-radius: 0 0 32px 32px;
            box-shadow: 0 15px 50px rgba(255, 0, 101, 0.3);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-25px) rotate(3deg); }
        }

        .page-header-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .page-header h1 {
            color: white;
            font-size: clamp(28px, 5vw, 42px);
            font-weight: 900;
            margin: 0 0 12px 0;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .page-header p {
            color: rgba(255, 255, 255, 0.95);
            font-size: clamp(15px, 2.5vw, 18px);
            margin: 0;
            font-weight: 600;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            line-height: 1.6;
        }

        /* ANNOUNCEMENT CARDS - NAVY BLUE THEME */
        .announcement-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 28px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 10px 35px rgba(97, 97, 255, 0.1);
            border: 1px solid rgba(97, 97, 255, 0.08);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(97, 97, 255, 0.18);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--gray-100);
            margin-bottom: 25px;
        }

        .card-header h3 {
            color: var(--navy);
            font-size: 22px;
            font-weight: 800;
            margin: 0;
        }

        .card-body {
            color: var(--gray-700);
            line-height: 1.7;
        }
        /* EMPTY STATE */
        .no-announcements {
            text-align: center;
            padding: 80px 20px;
            color: #64748b;
            font-size: 19px;
        }
        .no-announcements i {
            font-size: 70px;
            color: #cbd5e1;
            margin-bottom: 20px;
            opacity: 0.7;
        }
        /* Responsive */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 25px 20px;
            }

            .page-header {
                margin: -25px -20px 30px;
                padding: 35px 25px;
            }

            .emergency-btn {
                padding: 18px 35px;
                font-size: 17px;
            }

            .quick-actions {
                flex-direction: column;
                width: 100%;
            }

            .emergency-btn,
            .evac-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <div class="page-header-content">
            <h1>Community Announcements</h1>
        <p>Stay updated with official alerts, drills, and safety advisories from your barangay</p>
        </div>
    </div>

    <div class="announcement-grid">
        <?php
        $stmt = $pdo->query("SELECT a.*, u.name AS author_name 
                             FROM announcements a 
                             JOIN users u ON a.created_by = u.id 
                             ORDER BY a.created_at DESC");

        if ($stmt->rowCount() > 0):
            while ($a = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="card">
                    <h3><?= htmlspecialchars($a['title']) ?></h3>
                    <p><?= nl2br(htmlspecialchars($a['message'])) ?></p>
                    <div class="meta">
                        Posted by <b><?= htmlspecialchars($a['author_name']) ?></b> 
                        • <?= date('F j, Y \a\t g:i A', strtotime($a['created_at'])) ?>
                    </div>
                </div>
            <?php endwhile;
        else: ?>
            <div class="no-announcements">
                <i class="fas fa-bullhorn"></i><br><br>
                <strong>No announcements at the moment.</strong><br>
                <span>Check back later for important updates and safety advisories.</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js/chatbot.js"></script>
</body>
</html>