<!-- MODERN ADMIN SIDEBAR -->
<aside style="
    width: 280px;
    background: linear-gradient(180deg, #00167a 0%, #6161ff 100%);
    color: white;
    padding: 0;
    position: fixed;
    left: 0;
    top: 70px;
    bottom: 0;
    overflow-y: auto;
    box-shadow: 5px 0 40px rgba(0, 22, 122, 0.4);
    font-family: 'Segoe UI', Arial, sans-serif;
    z-index: 999;
">
    <div style="padding: 30px 0;">
        <div style="
            text-align: center;
            margin-bottom: 40px;
            padding: 25px 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            margin: 0 20px 40px;
            border: 2px solid rgba(255, 255, 255, 0.2);
        ">
            <div style="
                width: 70px;
                height: 70px;
                background: linear-gradient(135deg, #ff0065, #6161ff);
                border-radius: 50%;
                margin: 0 auto 15px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 32px;
                box-shadow: 0 8px 25px rgba(255, 0, 101, 0.4);
            ">
                🛡️
            </div>
            <h3 style="
                margin: 0;
                font-size: 22px;
                font-weight: 900;
                letter-spacing: 1.5px;
                text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            ">
                ADMIN PANEL
            </h3>
            <p style="
                margin: 8px 0 0;
                font-size: 12px;
                opacity: 0.85;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 1px;
            ">
                Control Center
            </p>
        </div>
        
        <nav style="padding: 0 15px;">
            <a href="dashboard.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="announcements.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'announcements.php') ? 'active' : '' ?>">
                <i class="fas fa-bullhorn"></i>
                <span>Announcements</span>
            </a>
            <a href="manage-emergencies.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'manage-emergencies.php') ? 'active' : '' ?>">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Manage Emergencies</span>
            </a>
            <a href="emergency-map.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'emergency-map.php') ? 'active' : '' ?>">
                <i class="fas fa-map-marked-alt"></i>
                <span>Emergency Map</span>
            </a>
            <a href="drill-management.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'drill-management.php') ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list"></i>
                <span>Drill Management</span>
            </a>
            <a href="drill-analytics.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'drill-analytics.php') ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Drill Analytics</span>
            </a>
            <a href="pending-registrations.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'pending-registrations.php') ? 'active' : '' ?>">
                <i class="fas fa-user-clock"></i>
                <span>Pending Registrations</span>
            </a>
            <a href="responder.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'responder.php') ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>Responders</span>
            </a>
            <!-- <a href="reports-analytics.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'reports-analytics.php') ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Reports & Analytics</span>
            </a> -->
            <!-- <a href="performance.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'performance.php') ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i>
                <span>Performance</span>
            </a> -->
        </nav>
    </div>
</aside>

<style>
    aside a {
        display: flex;
        align-items: center;
        gap: 15px;
        color: white;
        padding: 16px 20px;
        text-decoration: none;
        margin: 6px 0;
        border-radius: 14px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 15px;
        font-weight: 700;
        position: relative;
        overflow: hidden;
    }

    aside a i {
        font-size: 18px;
        width: 24px;
        text-align: center;
        z-index: 2;
    }

    aside a span {
        z-index: 2;
    }

    /* Hover state */
    aside a:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateX(8px);
        padding-left: 28px;
    }

    /* Active state */
    aside a.active {
        background: linear-gradient(135deg, #ff0065, #ff1a75);
        box-shadow: 0 8px 25px rgba(255, 0, 101, 0.4);
        transform: translateX(8px);
        padding-left: 28px;
    }

    /* Active indicator */
    aside a.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 5px;
        background: white;
        border-radius: 0 4px 4px 0;
        box-shadow: 0 0 15px rgba(255, 255, 255, 0.6);
    }

    /* Shimmer effect on hover */
    aside a::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.2),
            transparent
        );
        transition: left 0.5s;
    }

    aside a:hover::after {
        left: 100%;
    }

    /* Scrollbar styling */
    aside::-webkit-scrollbar {
        width: 8px;
    }

    aside::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.2);
    }

    aside::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 10px;
    }

    aside::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }

    /* Mobile responsive */
    @media (max-width: 992px) {
        aside {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        aside.mobile-open {
            transform: translateX(0);
        }
    }
</style>