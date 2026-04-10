<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
?>

<header class="top-header">
    <div class="header-left">
        <div class="header-logo">
            <span class="logo-icon">🛡️</span>
            <h2>Bantay Bayanihan</h2>
        </div>
        <div class="header-user">
            <span class="welcome-text">Welcome,</span>
            <strong class="user-name"><?=htmlspecialchars($_SESSION['name'] ?? '')?></strong>
            <span class="user-role"><?=ucfirst($_SESSION['role'] ?? '')?></span>
        </div>
    </div>
    <a href="../logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
</header>

<style>
    :root {
        --pink: #ff0065;
        --purple: #6161ff;
        --navy: #00167a;
        --gradient-primary: linear-gradient(135deg, #6161ff, #ff0065);
        --gradient-secondary: linear-gradient(135deg, #00167a, #6161ff);
    }

    .top-header {
        height: 75px;
        background: var(--gradient-primary);
        color: white;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 35px;
        box-shadow: 0 8px 25px rgba(97, 97, 255, 0.3);
        font-family: 'Segoe UI', Arial, sans-serif;
        backdrop-filter: blur(10px);
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 35px;
    }

    .header-logo {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-right: 35px;
        border-right: 2px solid rgba(255, 255, 255, 0.2);
    }

    .logo-icon {
        font-size: 32px;
        filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.3));
    }

    .header-logo h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 900;
        letter-spacing: 0.5px;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }

    .header-user {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .welcome-text {
        font-size: 14px;
        opacity: 0.9;
        font-weight: 500;
    }

    .user-name {
        font-size: 16px;
        font-weight: 800;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .user-role {
        font-size: 12px;
        background: rgba(255, 255, 255, 0.25);
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .logout-btn {
        background: white;
        color: var(--purple);
        padding: 12px 24px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 800;
        font-size: 14px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 8px;
        border: 2px solid transparent;
    }

    .logout-btn i {
        font-size: 16px;
    }

    .logout-btn:hover {
        background: var(--pink);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 0, 101, 0.3);
        border-color: white;
    }

    .logout-btn:active {
        transform: translateY(0);
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .top-header {
            padding: 0 20px;
            height: auto;
            min-height: 70px;
            flex-direction: column;
            gap: 15px;
            padding: 15px 20px;
        }

        .header-left {
            flex-direction: column;
            gap: 12px;
            align-items: flex-start;
            width: 100%;
        }

        .header-logo {
            border-right: none;
            padding-right: 0;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 12px;
            width: 100%;
        }

        .header-user {
            font-size: 13px;
        }

        .logout-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<!-- Push main content below fixed header -->
<div style="height: 75px;"></div>