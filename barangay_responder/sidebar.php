<!-- RESIDENT SIDEBAR - SCARLET RED THEME -->
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
                RESPONDER PANEL
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
        
    <div style="padding: 0 20px;">
        <h3 style="
            text-align:center; 
            margin-bottom:30px; 
            font-size:21px; 
            font-weight:900;
            letter-spacing:0.5px;
        ">
            Hello, <?=htmlspecialchars($_SESSION['name'])?>!
        </h3>
        <nav>
            <a href="dashboard.php"          class="<?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>">Dashboard</a>
            <a href="drills-and-trainings.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'drills-and-trainings.php') ? 'active' : '' ?>">Drills & Trainings</a>
            <a href="drill-stats.php"        class="<?= (basename($_SERVER['PHP_SELF']) == 'drill-stats.php') ? 'active' : '' ?>">Drill Statistics</a>
            <!-- <a href="evacuation-routes.php"  class="<?= (basename($_SERVER['PHP_SELF']) == 'evacuation-routes.php') ? 'active' : '' ?>">Evacuation Routes</a> -->
            <a href="report-emergency.php"   class="<?= (basename($_SERVER['PHP_SELF']) == 'report-emergency.php' || basename($_SERVER['PHP_SELF']) == 'my-reports.php') ? 'active' : '' ?>">Report & Manage</a>
            <a href="announcements.php"      class="<?= (basename($_SERVER['PHP_SELF']) == 'announcements.php') ? 'active' : '' ?>">Announcements</a>
            <!-- <a href="../certification-quiz.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'certification-quiz.php') ? 'active' : '' ?>">📜 Get Certified</a> -->
            <!-- <a href="profile.php"            class="<?= (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : '' ?>">Profile</a> -->
        </nav>
            <!-- Keep announcements link in sidebar for full announcements page access, but update the styling note to reflect it's integrated in dashboard. -->
    </div>
</aside>

<style>
    aside a {
        display: block;
        color: white;
        padding: 15px 25px;
        text-decoration: none;
        margin: 7px 18px;
        border-radius: 10px;
        transition: all 0.35s ease;
        font-size: 15px;
        font-weight: 600;
        position: relative;
        overflow: hidden;
    }

    /* Hover & Active State - Slightly darker scarlet */
    aside a:hover,
    aside a.active {
        background: #B30000;
           transform: translateX(8px);
           box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }

    /* Optional: Small left accent bar on active item */
    aside a.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 5px;
        background: white;
    }

    /* Smooth scrollbar (optional but looks premium) */
    aside::-webkit-scrollbar {
        width: 6px;
    }
    aside::-webkit-scrollbar-track {
        background: transparent;
    }
    aside::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.3);
        border-radius: 3px;
    }
    aside::-webkit-scrollbar-thumb:hover {
        background: rgba(255,255,255,0.5);
    }
</style>