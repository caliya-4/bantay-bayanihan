<?php
session_start();
require 'db_connect.php';

// Fetch announcements
$announcements = $pdo->query("SELECT a.*, u.name AS author_name FROM announcements a JOIN users u ON a.created_by = u.id ORDER BY a.created_at DESC LIMIT 10")->fetchAll();

// Fetch published drills
$current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
if ($current_user_id) {
    $brgyStmt = $pdo->prepare("
        SELECT barangay FROM drill_participants 
        WHERE email = (SELECT email FROM users WHERE id = ?) 
        AND barangay IS NOT NULL AND barangay != '' LIMIT 1
    ");
    $brgyStmt->execute([$current_user_id]);
    $userBarangay = $brgyStmt->fetchColumn();

    if ($userBarangay) {
        $drillStmt = $pdo->prepare("
            SELECT * FROM drills 
            WHERE status = 'published' 
            AND (barangay = ? OR barangay IS NULL OR barangay = '')
            AND (drill_date IS NULL OR drill_date >= CURDATE())
            ORDER BY CASE WHEN drill_date IS NOT NULL THEN drill_date ELSE created_at END DESC LIMIT 10
        ");
        $drillStmt->execute([$userBarangay]);
        $drills = $drillStmt->fetchAll();
    } else {
        $drills = $pdo->query("SELECT * FROM drills WHERE status = 'published' 
            AND (drill_date IS NULL OR drill_date >= CURDATE())
            ORDER BY CASE WHEN drill_date IS NOT NULL THEN drill_date ELSE created_at END DESC LIMIT 10")->fetchAll();
    }
} else {
    $drills = $pdo->query("SELECT * FROM drills WHERE status = 'published' 
        AND (drill_date IS NULL OR drill_date >= CURDATE())
        ORDER BY CASE WHEN drill_date IS NOT NULL THEN drill_date ELSE created_at END DESC LIMIT 10")->fetchAll();
}

// If user logged in, fetch their drill participations for displayed drills
$current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$user_participations = [];
if ($current_user_id && count($drills) > 0) {
    $ids = array_map(function($d){ return (int)$d['id']; }, $drills);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $params = $ids;
    array_unshift($params, $current_user_id);

    $sql = "SELECT drill_id, status FROM drill_participations WHERE user_id = ? AND drill_id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $user_participations[(int)$r['drill_id']] = $r['status'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bantay Bayanihan - Public Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <style>
        :root {
            --pink: #ff0065;
            --purple: #6161ff;
            --navy: #00167a;
            --gradient-primary: linear-gradient(135deg, var(--purple), var(--pink));
            --gradient-secondary: linear-gradient(135deg, var(--navy), var(--purple));
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f0f4ff 0%, #fef3f8 100%);
            min-height: 100vh;
        }

        /* MODERN HEADER */
        .header {
            background: var(--gradient-primary);
            color: white;
            padding: 50px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(97, 97, 255, 0.3);
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 400px;
            height: 400px;
            background: rgba(0, 22, 122, 0.15);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(5deg); }
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .header h1 {
            font-size: clamp(32px, 6vw, 56px);
            font-weight: 900;
            margin-bottom: 15px;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            letter-spacing: 1px;
        }

        .header p {
            font-size: clamp(16px, 3vw, 22px);
            opacity: 0.95;
            font-weight: 500;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        /* LOGIN BUTTON */
        .login-btn {
            position: fixed;
            top: 25px;
            right: 25px;
            background: white;
            color: var(--purple);
            padding: 14px 32px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 800;
            font-size: 16px;
            box-shadow: 0 8px 25px rgba(97, 97, 255, 0.3);
            transition: all 0.3s ease;
            z-index: 10000;
            border: 3px solid var(--purple);
        }

        .login-btn:hover {
            background: var(--purple);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(97, 97, 255, 0.4);
        }

        .container {
            max-width: 1400px;
            margin: -50px auto 40px;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }

        /* MODERN SECTION CARDS */
        .section {
            background: white;
            border-radius: 24px;
            box-shadow: 0 15px 50px rgba(0, 22, 122, 0.08);
            margin-bottom: 35px;
            overflow: hidden;
            border: 1px solid rgba(97, 97, 255, 0.1);
            transition: all 0.3s ease;
        }

        .section:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 60px rgba(97, 97, 255, 0.15);
        }

        .section h2 {
            background: var(--gradient-secondary);
            color: white;
            margin: 0;
            padding: 28px 35px;
            font-size: clamp(22px, 4vw, 28px);
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .section h2 i {
            font-size: 32px;
        }

        .section-content {
            padding: 35px;
        }

        /* ANNOUNCEMENTS & 
        */
        .announcement, .drill {
            border-bottom: 2px solid #f0f4ff;
            padding: 25px 0;
            transition: all 0.3s ease;
        }

        .announcement:last-child, .drill:last-child {
            border-bottom: none;
        }

        .announcement:hover, .drill:hover {
            padding-left: 15px;
            background: linear-gradient(90deg, rgba(97, 97, 255, 0.05), transparent);
            border-radius: 12px;
        }

        .announcement h3, .drill h3 {
            color: var(--navy);
            margin: 0 0 12px;
            font-size: 20px;
            font-weight: 800;
        }

        .announcement p, .drill p {
            margin: 0 0 10px;
            color: #475569;
            line-height: 1.7;
        }

        .announcement small, .drill small {
            color: #94a3b8;
            font-size: 13px;
            font-weight: 600;
        }

        /* MAP STYLING */
        #map {
            height: 60vh;
            min-height: 500px;
            border: 6px solid var(--purple);
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(97, 97, 255, 0.25);
            width: 100%;
        }

        .control-panel {
            background: linear-gradient(135deg, #ffffff, #f8f9ff);
            padding: 28px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 22, 122, 0.08);
            margin-bottom: 25px;
            border: 2px solid rgba(97, 97, 255, 0.1);
        }

        .control-panel h4 {
            color: var(--navy);
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .control-panel h5 {
            color: var(--navy);
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 15px;
        }

        .site-info-card {
            background: linear-gradient(135deg, #fef3f8, #f0f4ff);
            border-left: 5px solid var(--pink);
            padding: 18px;
            margin-bottom: 15px;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 0, 101, 0.1);
        }

        .site-info-card:hover {
            transform: translateX(5px);
            box-shadow: 0 8px 25px rgba(255, 0, 101, 0.2);
        }

        .distance-badge {
            font-size: 14px;
            padding: 6px 14px;
            background: var(--pink);
            color: white;
            border-radius: 50px;
            font-weight: 800;
            box-shadow: 0 4px 15px rgba(255, 0, 101, 0.3);
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .legend-item:hover {
            background: rgba(97, 97, 255, 0.05);
        }

        /* BUTTONS */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 800;
            text-decoration: none;
            display: inline-block;
            margin: 4px;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 6px 20px rgba(97, 97, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(97, 97, 255, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
        }

        .btn-info {
            background: linear-gradient(135deg, #6161ff, #00167a);
            color: white;
            box-shadow: 0 6px 20px rgba(97, 97, 255, 0.3);
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(97, 97, 255, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #64748b, #475569);
            color: white;
            box-shadow: 0 6px 20px rgba(100, 116, 139, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(100, 116, 139, 0.4);
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
        }

        /* GRID LAYOUT */
        .row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        @media (max-width: 992px) {
            .row {
                grid-template-columns: 1fr;
            }
        }

        /* UTILITY CLASSES */
        .d-flex { display: flex; }
        .gap-2 { gap: 10px; }
        .flex-wrap { flex-wrap: wrap; }
        .justify-content-between { justify-content: space-between; }
        .align-items-start { align-items: flex-start; }
        .align-items-center { align-items: center; }
        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 1rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-3 { margin-top: 1rem; }
        .py-3 { padding: 1rem 0; }
        .w-100 { width: 100%; }
        .text-center { text-align: center; }
        .text-muted { color: #94a3b8; }
        .small { font-size: 14px; }

        /* JOIN MODAL */
        .modal-content {
            background: white;
            margin: 8% auto;
            padding: 40px;
            border-radius: 24px;
            width: 90%;
            max-width: 550px;
            box-shadow: 0 25px 80px rgba(0, 22, 122, 0.3);
            border: 3px solid var(--purple);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 3px solid rgba(97, 97, 255, 0.1);
        }

        .modal-header h2 {
            color: var(--navy);
            font-size: 28px;
            font-weight: 900;
        }

        .modal-close {
            cursor: pointer;
            font-size: 32px;
            color: #cbd5e1;
            transition: all 0.2s;
            background: none;
            border: none;
            padding: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            color: var(--pink);
            background: rgba(255, 0, 101, 0.1);
            transform: rotate(90deg);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: var(--navy);
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--purple);
            box-shadow: 0 0 0 4px rgba(97, 97, 255, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .form-actions button {
            flex: 1;
            padding: 16px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit {
            background: var(--gradient-primary);
            color: white;
            border: none;
            box-shadow: 0 8px 25px rgba(97, 97, 255, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(97, 97, 255, 0.4);
        }

        .btn-cancel {
            background: #e2e8f0;
            color: #475569;
            border: none;
        }

        .btn-cancel:hover {
            background: #cbd5e1;
        }
        /* Gamification Section Styles */
        .gamification-section {
            background: linear-gradient(135deg, #f8f9ff 0%, #fef3f8 100%);
            padding: 80px 0;
            margin-top: 60px;
        }

        .gamification-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-header h2 {
            font-size: clamp(32px, 5vw, 48px);
            font-weight: 900;
            background: linear-gradient(135deg, #6161ff, #ff0065);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 15px;
        }

        .section-header p {
            font-size: 18px;
            color: #64748b;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Quiz Cards Grid */
        .quiz-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }

        .quiz-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            border: 3px solid #e2e8f0;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .quiz-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(135deg, #6161ff, #ff0065);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .quiz-card:hover::before {
            transform: scaleX(1);
        }

        .quiz-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(97, 97, 255, 0.25);
            border-color: #6161ff;
        }

        .quiz-icon {
            font-size: 64px;
            margin-bottom: 15px;
            display: block;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
        }

        .quiz-card h3 {
            font-size: 22px;
            font-weight: 800;
            color: #00167a;
            margin-bottom: 10px;
        }

        .quiz-card p {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .quiz-meta {
            display: flex;
            justify-content: center;
            gap: 20px;
            font-size: 13px;
            color: #94a3b8;
        }

        /* Leaderboard */
        .leaderboard-section {
            background: white;
            border-radius: 24px;
            padding: 40px;
            border: 3px solid #6161ff;
            box-shadow: 0 15px 40px rgba(97, 97, 255, 0.15);
        }

        .leaderboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .leaderboard-header h3 {
            font-size: 28px;
            font-weight: 900;
            color: #00167a;
        }

        .leaderboard-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .leaderboard-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9ff, #fef3f8);
            border-radius: 16px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .leaderboard-item:hover {
            transform: translateX(10px);
            border-color: #6161ff;
            box-shadow: 0 8px 20px rgba(97, 97, 255, 0.15);
        }

        .leaderboard-rank {
            font-size: 32px;
            min-width: 50px;
            text-align: center;
        }

        .leaderboard-info {
            flex: 1;
        }

        .leaderboard-barangay {
            font-size: 18px;
            font-weight: 800;
            color: #00167a;
            margin-bottom: 5px;
        }

        .leaderboard-users {
            font-size: 14px;
            color: #64748b;
        }

        .leaderboard-score {
            font-size: 32px;
            font-weight: 900;
            background: linear-gradient(135deg, #6161ff, #ff0065);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Quiz Modal */
        .quiz-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 22, 122, 0.8);
            backdrop-filter: blur(10px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .quiz-modal.active {
            display: flex;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .quiz-content {
            background: white;
            border-radius: 24px;
            padding: 40px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            border: 4px solid #6161ff;
            box-shadow: 0 30px 80px rgba(0, 22, 122, 0.4);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(50px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        .quiz-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .quiz-title {
            font-size: 24px;
            font-weight: 900;
            color: #00167a;
        }

        .quiz-close {
            background: none;
            border: none;
            font-size: 32px;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.3s;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quiz-close:hover {
            background: #fee;
            color: #ff0065;
            transform: rotate(90deg);
        }

        .quiz-progress {
            margin-bottom: 30px;
        }

        .progress-text {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-weight: 700;
            color: #64748b;
        }

        .progress-bar {
            height: 12px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #6161ff, #ff0065);
            transition: width 0.5s ease;
            border-radius: 10px;
        }

        .question-container {
            margin-bottom: 30px;
        }

        .question-text {
            font-size: 20px;
            font-weight: 700;
            color: #00167a;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .options-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .option-btn {
            background: linear-gradient(135deg, #f8f9ff, #fef3f8);
            border: 3px solid #e2e8f0;
            border-radius: 16px;
            padding: 18px 25px;
            font-size: 16px;
            font-weight: 600;
            color: #00167a;
            text-align: left;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .option-btn:hover {
            border-color: #6161ff;
            transform: translateX(10px);
            box-shadow: 0 8px 20px rgba(97, 97, 255, 0.15);
        }

        .option-btn.correct {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            border-color: #10b981;
            color: #065f46;
        }

        .option-btn.incorrect {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            border-color: #ef4444;
            color: #991b1b;
        }

        .option-btn.disabled {
            pointer-events: none;
            opacity: 0.6;
        }

        .feedback-box {
            margin-top: 20px;
            padding: 20px;
            border-radius: 16px;
            border-left: 6px solid;
        }

        .feedback-box.correct {
            background: #d1fae5;
            border-color: #10b981;
            color: #065f46;
        }

        .feedback-box.incorrect {
            background: #fee2e2;
            border-color: #ef4444;
            color: #991b1b;
        }

        .feedback-title {
            font-weight: 800;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .feedback-text {
            line-height: 1.6;
        }

        .points-earned {
            display: inline-block;
            background: linear-gradient(135deg, #6161ff, #ff0065);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 800;
            margin-top: 10px;
        }

        .quiz-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-primary {
            flex: 1;
            background: linear-gradient(135deg, #6161ff, #ff0065);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(97, 97, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(97, 97, 255, 0.4);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .quiz-complete {
            text-align: center;
            padding: 40px 0;
        }

        .complete-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .complete-title {
            font-size: 32px;
            font-weight: 900;
            background: linear-gradient(135deg, #6161ff, #ff0065);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 15px;
        }

        .complete-score {
            font-size: 48px;
            font-weight: 900;
            color: #00167a;
            margin-bottom: 10px;
        }

        .complete-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 30px 0;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 900;
            color: #6161ff;
        }

        .stat-label {
            font-size: 14px;
            color: #64748b;
            margin-top: 5px;
        }

        /* Loading State */
        .loading-spinner {
            text-align: center;
            padding: 40px;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #e2e8f0;
            border-top: 5px solid #6161ff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .quiz-grid {
                grid-template-columns: 1fr;
            }
            
            .quiz-content {
                padding: 25px;
            }
            
            .complete-stats {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <a href="login.php" class="login-btn">
        <i class="fas fa-sign-in-alt"></i> Login
    </a>

    <div class="header">
        <div class="header-content">
            <h1><i class="fas fa-shield-alt"></i> Bantay Bayanihan</h1>
            <p>Stay Prepared, Stay Safe - Public Emergency Portal</p>
        </div>
    </div>

    <div class="container">
        <!-- Evacuation Maps -->
        <div class="section">
            <h2>
                <i class="fas fa-map-marked-alt"></i>
                Evacuation Centers & Maps
            </h2>
            <div class="section-content">
                <div class="row">
                    <!-- Map + Controls (Main) -->
                    <div>
                        <div class="control-panel">
                            <h4>🗺️ Evacuation Centers Map</h4>
                            <p class="text-muted mb-3">Find the nearest safe evacuation center in Baguio City</p>

                            <div class="d-flex gap-2 flex-wrap">
                                <button class="btn btn-primary" id="findNearestBtn" onclick="findNearestSites()">
                                    📍 Find Nearest Centers
                                </button>
                                <button class="btn btn-success" onclick="goToMyLocation()">
                                    🎯 Go to My Location
                                </button>
                                <button class="btn btn-info" onclick="showAllSites()">
                                    👁️ Show All Sites
                                </button>
                                <button class="btn btn-secondary" onclick="clearRoutes()">
                                    🗑️ Clear Routes
                                </button>
                            </div>
                        </div>

                        <div id="map"></div>
                    </div>

                    <!-- Nearest Sites Sidebar -->
                    <div>
                        <div class="control-panel">
                            <h5><i class="fas fa-info-circle"></i> Legend</h5>
                            <div class="legend-item">
                                <span style="font-size:20px;background:#10b981;width:24px;height:24px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3);display:inline-flex;align-items:center;justify-content:center;"></span>
                                <span><strong>Evacuation Site</strong></span>
                            </div>
                            <div class="legend-item">
                                <span style="font-size:28px;">🏫</span>
                                <span><strong>School Site</strong></span>
                            </div>
                            <div class="legend-item">
                                <span style="font-size:28px;">🏛️</span>
                                <span><strong>Barangay Hall</strong></span>
                            </div>
                            <div class="legend-item">
                                <span style="font-size:28px;">🏀</span>
                                <span><strong>Court/Gym</strong></span>
                            </div>
                            <div class="legend-item">
                                <span style="font-size:20px;background:#ff0065;width:40px;height:4px;box-shadow:0 2px 8px rgba(255,0,101,0.4);border-radius:2px;display:inline-block;"></span>
                                <span><strong>Road Closure</strong></span>
                            </div>
                        </div>

                        <div class="control-panel">
                            <h5>📍 Nearest Centers</h5>
                            <p class="small text-muted mb-3">Click "Find Nearest Centers" to see results</p>
                            <div id="nearestSitesList">
                                <div class="text-center text-muted py-3">
                                    <p>Use your location to find nearest evacuation centers</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Announcements -->
        <div class="section">
            <h2>
                <i class="fas fa-bullhorn"></i>
                Latest Announcements
            </h2>
            <div class="section-content">
                <?php if (empty($announcements)): ?>
                    <p class="text-muted">No announcements at the moment.</p>
                <?php else: ?>
                    <?php foreach ($announcements as $ann): ?>
                        <div class="announcement">
                            <h3><?php echo htmlspecialchars($ann['title']); ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($ann['message'])); ?></p>
                            <small>Posted by <?php echo htmlspecialchars($ann['author_name']); ?> on <?php echo date('M d, Y', strtotime($ann['created_at'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Drills -->
        <div class="section">
            <h2>
                <i class="fas fa-users"></i>
                Upcoming Safety Drills
            </h2>
            <div class="section-content">
                <?php if (empty($drills)): ?>
                    <p class="text-muted">No active drills at the moment.</p>
                <?php else: ?>
                    <?php foreach ($drills as $drill): ?>
                        <div class="drill">
                            <div class="d-flex justify-content-between align-items-start">
                                <div style="flex: 1;">
                                    <h3><?php echo htmlspecialchars($drill['title']); ?></h3>
                                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
                                        <?php if (!empty($drill['barangay'])): ?>
                                            <span style="display:inline-flex;align-items:center;gap:5px;background:rgba(97,97,255,.1);color:#6161ff;border-radius:20px;padding:4px 12px;font-size:12px;font-weight:700;">
                                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($drill['barangay']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="display:inline-flex;align-items:center;gap:5px;background:rgba(16,185,129,.1);color:#059669;border-radius:20px;padding:4px 12px;font-size:12px;font-weight:700;">
                                                <i class="fas fa-globe"></i> All Barangays
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($drill['drill_date'])): ?>
                                            <span style="display:inline-flex;align-items:center;gap:5px;background:rgba(245,158,11,.1);color:#d97706;border-radius:20px;padding:4px 12px;font-size:12px;font-weight:700;">
                                                <i class="fas fa-calendar-alt"></i> <?php echo date('M j, Y', strtotime($drill['drill_date'])); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($drill['drill_place'])): ?>
                                            <span style="display:inline-flex;align-items:center;gap:5px;background:rgba(255,0,101,.08);color:#ff0065;border-radius:20px;padding:4px 12px;font-size:12px;font-weight:700;">
                                                <i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($drill['drill_place']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <p><?php echo nl2br(htmlspecialchars($drill['description'])); ?></p>
                                    <p><strong>Duration:</strong> <?php echo $drill['duration_minutes']; ?> minutes</p>
                                    <small>Created on <?php echo date('M d, Y', strtotime($drill['created_at'])); ?></small>
                                </div>
                                <?php if ($current_user_id): ?>
                                    <?php $status = isset($user_participations[$drill['id']]) ? $user_participations[$drill['id']] : null; ?>
                                    <?php if (!$status): ?>
                                        <button class="btn btn-primary btn-sm" onclick="openJoinModal(<?php echo $drill['id']; ?>, '<?php echo addslashes(htmlspecialchars($drill['title'])); ?>')">
                                            🎖️ Join Drill
                                        </button>
                                    <?php elseif ($status === 'in_progress'): ?>
                                        <div id="mission-controls-<?php echo $drill['id']; ?>">
                                            <button class="btn btn-success btn-sm" onclick="completeMission(<?php echo $drill['id']; ?>, true)">✓ Complete Mission</button>
                                            <button class="btn btn-secondary btn-sm" style="margin-left:8px;" onclick="completeMission(<?php echo $drill['id']; ?>, false)">✗ Abandon Mission</button>
                                        </div>
                                    <?php elseif ($status === 'completed'): ?>
                                        <span class="small" style="color:#10b981;font-weight:800;">✓ Mission Complete</span>
                                    <?php else: ?>
                                        <span class="small text-muted">Status: <?php echo htmlspecialchars($status); ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="btn btn-primary btn-sm" onclick="openJoinModal(<?php echo $drill['id']; ?>, '<?php echo addslashes(htmlspecialchars($drill['title'])); ?>')">
                                        🎖️ Join Drill
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

<!-- Gamification Section HTML -->
    <section class="gamification-section" id="gamification">
        <div class="gamification-container">
            <!-- Section Header -->
            <div class="section-header">
                <h2>🎯 Test Your Preparedness!</h2>
                <p>Learn about disaster safety through interactive quizzes and compete with your barangay</p>
            </div>

            <!-- Quiz Categories Grid -->
            <div class="quiz-grid">
                <div class="quiz-card" onclick="startQuiz('earthquake')">
                    <span class="quiz-icon">🏚️</span>
                    <h3>Earthquake Safety</h3>
                    <p>Learn what to do before, during, and after an earthquake</p>
                    <div class="quiz-meta">
                        <span>⏱️ 3 min</span>
                        <span>📝 5 questions</span>
                    </div>
                </div>

                <div class="quiz-card" onclick="startQuiz('typhoon')">
                    <span class="quiz-icon">🌀</span>
                    <h3>Typhoon Preparedness</h3>
                    <p>Master typhoon safety and preparation techniques</p>
                    <div class="quiz-meta">
                        <span>⏱️ 3 min</span>
                        <span>📝 5 questions</span>
                    </div>
                </div>

                <div class="quiz-card" onclick="startQuiz('fire')">
                    <span class="quiz-icon">🔥</span>
                    <h3>Fire Safety</h3>
                    <p>Know how to prevent and respond to fires</p>
                    <div class="quiz-meta">
                        <span>⏱️ 3 min</span>
                        <span>📝 5 questions</span>
                    </div>
                </div>

                <div class="quiz-card" onclick="startQuiz('flood')">
                    <span class="quiz-icon">🌊</span>
                    <h3>Flood Safety</h3>
                    <p>Learn how to stay safe during floods</p>
                    <div class="quiz-meta">
                        <span>⏱️ 3 min</span>
                        <span>📝 5 questions</span>
                    </div>
                </div>

                <div class="quiz-card" onclick="startQuiz('landslide')">
                    <span class="quiz-icon">⛰️</span>
                    <h3>Landslide Awareness</h3>
                    <p>Recognize warning signs and evacuation procedures</p>
                    <div class="quiz-meta">
                        <span>⏱️ 3 min</span>
                        <span>📝 5 questions</span>
                    </div>
                </div>

                <div class="quiz-card" onclick="startQuiz('general')">
                    <span class="quiz-icon">📋</span>
                    <h3>General Preparedness</h3>
                    <p>Essential knowledge for disaster readiness</p>
                    <div class="quiz-meta">
                        <span>⏱️ 3 min</span>
                        <span>📝 5 questions</span>
                    </div>
                </div>
            </div>

            <!-- Certification Section -->
            <div style="background: linear-gradient(135deg, #ff0065, #6161ff); border-radius: 24px; padding: 40px; margin-bottom: 40px; text-align: center; box-shadow: 0 20px 60px rgba(97, 97, 255, 0.25);">
                <div style="color: white;">
                    <div style="font-size: 48px; margin-bottom: 15px;">📜</div>
                    <h3 style="margin: 0 0 10px 0; font-size: 28px; font-weight: 900;">Get Your Bantay Bayanihan Certification</h3>
                    <p style="margin: 0 0 25px 0; font-size: 16px; opacity: 0.95;">Take our comprehensive certification quiz and prove your disaster preparedness expertise. No drill participation required!</p>
                    <p style="margin: 0 0 25px 0; font-size: 14px; opacity: 0.9;">✓ 20 questions covering all disaster types  •  ✓ 75% passing score  •  ✓ Instant certification</p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="certification-quiz.php" style="display: inline-block; background: white; color: #6161ff; padding: 14px 32px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 16px; transition: all 0.3s; cursor: pointer;">Start Certification Quiz →</a>
                    <?php else: ?>
                        <div id="cert-email-form" style="display:inline-block;">
                            <input type="email" id="cert-email" placeholder="Your email" style="padding:14px 20px; border-radius:12px; border:2px solid #e2e8f0; margin-right:8px;" />
                            <input type="text" id="cert-barangay" placeholder="Your barangay" style="padding:14px 20px; border-radius:12px; border:2px solid #e2e8f0; margin-right:8px;" />
                            <button onclick="startCertWithEmail()" style="background: white; color: #6161ff; border: none; padding: 14px 32px; border-radius: 12px; font-weight: 700; font-size: 16px; cursor: pointer; transition: all 0.3s;">Start Certification Quiz →</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Leaderboard -->
            <div class="leaderboard-section">
                <div class="leaderboard-header">
                    <h3>🏆 Top Prepared Barangays</h3>
                    <span style="color: #64748b; font-size: 14px;">Updated live</span>
                </div>
                <div class="leaderboard-list" id="leaderboard-list">
                    <div class="loading-spinner">
                        <div class="spinner"></div>
                        <p style="margin-top: 15px; color: #64748b;">Loading rankings...</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quiz Modal -->
    <div class="quiz-modal" id="quiz-modal">
        <div class="quiz-content">
            <div class="quiz-header">
                <div class="quiz-title" id="quiz-modal-title">Earthquake Safety Quiz</div>
                <button class="quiz-close" onclick="closeQuiz()">×</button>
            </div>

            <div class="quiz-progress">
                <div class="progress-text">
                    <span>Question <span id="current-question">1</span> of <span id="total-questions">5</span></span>
                    <span>Score: <span id="current-score">0</span></span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progress-fill" style="width: 20%"></div>
                </div>
            </div>

            <div id="quiz-body">
                <!-- Questions loaded here -->
            </div>
        </div>
    </div>
    </div>
    </section>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // [Keep all your existing JavaScript - map functionality, etc.]
        // Initialize map centered on Baguio City
        const map = L.map('map').setView([16.4023, 120.5960], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        let markers = [];
        let userMarker = null;
        let userLocation = null;
        let routeLines = [];

        const siteIcons = {
            school: '🏫',
            barangay_hall: '🏛️',
            court: '🏀',
            covered_court: '🏀',
            gym: '🏋️',
            other: '✅'
        };

        async function loadAllSites() {
            try {
                const response = await fetch('api/evacuation/get-sites.php?t=' + Date.now(), {
                    cache: 'no-store'
                });

                if (!response.ok) {
                    const txt = await response.text().catch(() => 'No response body');
                    throw new Error(`HTTP ${response.status}: ${txt}`);
                }

                const data = await response.json().catch(err => {
                    throw new Error('Invalid JSON from get-sites.php: ' + err.message);
                });

                if (!data || !data.success) {
                    console.error('get-sites returned error or unexpected payload', data);
                    alert('Evacuation sites API returned an error — check console for details.');
                    return;
                }

                if (!Array.isArray(data.data)) {
                    console.error('get-sites returned non-array data', data.data);
                    alert('Evacuation sites API returned invalid data format.');
                    return;
                }

                markers = [];
                data.data.forEach(site => addSiteMarker(site));
                console.log(`Loaded ${data.data.length} evacuation sites`);

            } catch (error) {
                console.error('Error loading sites:', error);
                alert('Failed to load evacuation sites. See console for details.');
            }
        }

        async function loadRoadClosures() {
            try {
                const response = await fetch('api/admin/get-closures.php');
                const data = await response.json();
                if (data.success) {
                    data.data.forEach(closure => {
                        L.polyline([
                            [closure.start_lat, closure.start_lng],
                            [closure.end_lat, closure.end_lng]
                        ], {
                            color: '#ff0065',
                            weight: 5,
                            dashArray: '12, 8',
                            opacity: 0.9
                        }).addTo(map)
                        .bindPopup(`<b>⚠️ Road Closed</b><br>${closure.description}`);
                    });
                }
            } catch (err) {
                console.warn('Could not load road closures:', err);
            }
        }

        function addSiteMarker(site) {
            const icon = siteIcons[site.type] || '📍';
            const customIcon = L.divIcon({
                html: `<div style="font-size: 30px;">${icon}</div>`,
                className: 'custom-marker',
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            });

            const marker = L.marker([site.latitude, site.longitude], { icon: customIcon }).addTo(map);

            const facilities = Array.isArray(site.facilities)
                ? site.facilities.join(', ')
                : (site.facilities || 'Not specified');

            marker.bindPopup(`
                <div style="min-width:200px;">
                    <h6><strong>${site.name}</strong></h6>
                    <p class="mb-1"><strong>Type:</strong> ${site.type.replace('_', ' ')}</p>
                    <p class="mb-1"><strong>Barangay:</strong> ${site.barangay}</p>
                    <p class="mb-1"><strong>Facilities:</strong> ${facilities}</p>
                    <button class="btn btn-sm btn-primary mt-2" onclick="getDirections(${site.latitude}, ${site.longitude}, '${site.name.replace(/'/g, "\\'")}')">
                        Get Directions
                    </button>
                </div>
            `);
            markers.push({ marker, site });
        }

        async function findNearestSites() {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser');
                return;
            }

            const btn = document.getElementById('findNearestBtn');
            btn.disabled = true;
            btn.innerHTML = '🔄 Finding...';

            navigator.geolocation.getCurrentPosition(async function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                userLocation = { lat, lng };

                console.log('User location:', lat, lng);

                if (userMarker) map.removeLayer(userMarker);

                const userIcon = L.divIcon({
                    html: '<div style="font-size: 36px;">📍</div>',
                    className: 'user-marker',
                    iconSize: [36, 36],
                    iconAnchor: [18, 36]
                });
                userMarker = L.marker([lat, lng], { icon: userIcon })
                    .addTo(map)
                    .bindPopup('<strong>You are here</strong>')
                    .openPopup();

                map.setView([lat, lng], 15);

                try {
                    const apiUrl = `api/evacuation/nearest.php?lat=${lat}&lng=${lng}&limit=5&t=${Date.now()}`;
                    console.log('Fetching from:', apiUrl);
                    
                    const response = await fetch(apiUrl, {
                        cache: 'no-store'
                    });
                    
                    console.log('Response status:', response.status);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    console.log('API Response:', data);
                    
                    if (data.success && data.data) {
                        console.log('Found sites:', data.data.length);
                        displayNearestSites(data.data);
                    } else {
                        console.error('API returned error:', data.message);
                        alert('Error: ' + (data.message || 'Failed to find nearest sites'));
                    }
                } catch (error) {
                    console.error('Fetch error:', error);
                    alert('Failed to find nearest sites. Error: ' + error.message);
                }

                btn.disabled = false;
                btn.innerHTML = '📍 Find Nearest Centers';
            }, function(error) {
                console.error('Geolocation error:', error);
                alert('Unable to get your location: ' + error.message + '\nPlease enable location services.');
                btn.disabled = false;
                btn.innerHTML = '📍 Find Nearest Centers';
            });
        }

        function displayNearestSites(sites) {
            const list = document.getElementById('nearestSitesList');
            list.innerHTML = '';

            if (sites.length === 0) {
                list.innerHTML = '<p class="text-muted text-center py-3">No nearby centers found</p>';
                return;
            }

            // Sort by distance ascending first
        sites.sort((a, b) => parseFloat(a.distance_km) - parseFloat(b.distance_km));

        // Deduplicate by normalized site name (trim + lowercase)
        const seenNames = new Set();
        const uniqueSites = [];
        
        sites.forEach(site => {
                const normalizedName = (site.name || '').trim().toLowerCase();
                if (!seenNames.has(normalizedName)) {
                    seenNames.add(normalizedName);
                    uniqueSites.push(site);
                }
            });

            if (uniqueSites.length === 0) {
                list.innerHTML = '<p class="text-muted text-center py-3">No nearby centers found</p>';
                return;
            }

            uniqueSites.forEach((site, index) => {
                const km      = parseFloat(site.distance_km);
                const meters  = Math.round(km * 1000);
                const primaryNum  = km < 1 ? meters          : km.toFixed(2);
                const primaryUnit = km < 1 ? 'm'             : 'km';
                const secondary   = km < 1 ? `${km.toFixed(3)} km` : `${meters.toLocaleString()} m`;
                const color = km < 0.5 ? '#00c48c' : km < 1.5 ? '#ffb800' : '#ff6b35';
                const label = km < 0.5 ? 'Very Close' : km < 1.5 ? 'Nearby' : 'Far';
                const walkMins  = km < 0.05 ? '<1' : Math.ceil(km / 0.08);
                const driveMins = km < 0.5  ? '<1' : Math.ceil(km / 0.5);

                const siteCard = `
                    <div class="site-info-card" style="border-left-color: ${color};">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="mb-0" style="font-weight:800; color:#00167a; font-size:14px;">
                                ${index + 1}. ${site.name}
                            </h6>
                        </div>
                        <p class="small mb-2" style="color:#64748b; margin:0;">
                            ${site.barangay} &nbsp;·&nbsp; ${site.type.replace(/_/g,' ')}
                        </p>
                        <div style="background:#f8f9ff; border-radius:12px; padding:10px 14px; margin-bottom:10px; border:1.5px solid ${color}33;">
                            <div style="display:flex; align-items:baseline; gap:5px; margin-bottom:2px;">
                                <span style="font-size:26px; font-weight:900; line-height:1; color:${color};">${primaryNum}</span>
                                <span style="font-size:14px; font-weight:700; color:${color};">${primaryUnit}</span>
                                <span style="font-size:10px; font-weight:800; letter-spacing:1.5px; text-transform:uppercase; background:${color}18; color:${color}; padding:2px 7px; border-radius:999px; margin-left:4px;">${label}</span>
                            </div>
                            <div style="font-size:12px; color:#94a3b8;">
                                Also <strong style="color:#475569;">${secondary}</strong> away
                            </div>
                        </div>
                        <div style="display:flex; gap:6px; margin-bottom:10px;">
                            <div style="flex:1; background:#fff; border:1.5px solid #e2e8f0; border-radius:10px; padding:7px 6px; text-align:center;">
                                <div style="font-size:15px;">🚶</div>
                                <div style="font-size:12px; font-weight:800; color:#00167a;">~${walkMins} min</div>
                                <div style="font-size:10px; color:#94a3b8;">Walk</div>
                            </div>
                            <div style="flex:1; background:#fff; border:1.5px solid #e2e8f0; border-radius:10px; padding:7px 6px; text-align:center;">
                                <div style="font-size:15px;">🚗</div>
                                <div style="font-size:12px; font-weight:800; color:#00167a;">~${driveMins} min</div>
                                <div style="font-size:10px; color:#94a3b8;">Drive</div>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-success w-100" onclick="getDirections(${site.latitude}, ${site.longitude}, '${site.name.replace(/'/g, "\\'")}')">
                            🧭 Get Directions
                        </button>
                    </div>
                `;
                list.innerHTML += siteCard;
            });
        }

        async function getDirections(toLat, toLng, siteName) {
            if (!userLocation) {
                alert('Please find your location first by clicking "Find Nearest Centers"');
                return;
            }

            clearRoutes();

            // Show loading indicator
            const loadingLine = L.polyline([
                [userLocation.lat, userLocation.lng],
                [toLat, toLng]
            ], { color: '#ccc', weight: 4, dashArray: '8, 8' }).addTo(map);
            routeLines.push(loadingLine);

            try {
                const url = `https://router.project-osrm.org/route/v1/foot/${userLocation.lng},${userLocation.lat};${toLng},${toLat}?overview=full&geometries=geojson`;
                const response = await fetch(url);
                const data = await response.json();

                // Remove loading line
                map.removeLayer(loadingLine);
                routeLines = routeLines.filter(l => l !== loadingLine);

                if (data.code !== 'Ok' || !data.routes.length) {
                    alert('Could not find a route. Try a different location.');
                    return;
                }

                const route = data.routes[0];
                const coords = route.geometry.coordinates.map(c => [c[1], c[0]]); // flip lng,lat → lat,lng

                const routeLine = L.polyline(coords, {
                    color: '#6161ff',
                    weight: 6,
                    opacity: 0.85
                }).addTo(map);
                routeLines.push(routeLine);

                map.fitBounds(routeLine.getBounds(), { padding: [40, 40] });

                const distanceKm = (route.distance / 1000).toFixed(2);
                const estimatedMinutes = Math.ceil(route.duration / 60);
                const driveMins = Math.ceil(route.duration / 60 / 3);

                alert(`Route to ${siteName}\n\nWalking distance: ${distanceKm} km\nEstimated walking time: ~${estimatedMinutes} minutes\nEstimated driving time: ~${driveMins} minutes`);

            } catch (err) {
                console.error('Routing error:', err);
                // Fallback to straight line if OSRM fails
                map.removeLayer(loadingLine);
                routeLines = routeLines.filter(l => l !== loadingLine);
                const fallback = L.polyline([
                    [userLocation.lat, userLocation.lng],
                    [toLat, toLng]
                ], { color: '#6161ff', weight: 5, dashArray: '10, 6' }).addTo(map);
                routeLines.push(fallback);
                alert(`Route to ${siteName} (approximate)\n\nCould not load road routing. Showing straight line.`);
            }
        }

        function goToMyLocation() {
            if (userLocation) {
                map.setView([userLocation.lat, userLocation.lng], 15);
                if (userMarker) userMarker.openPopup();
            } else {
                alert('Please click "Find Nearest Centers" first to share your location.');
            }
        }

        function showAllSites() {
            if (markers.length > 0) {
                const group = L.featureGroup(markers.map(m => m.marker));
                map.fitBounds(group.getBounds().pad(0.1));
            }
        }

        function clearRoutes() {
            routeLines.forEach(line => map.removeLayer(line));
            routeLines = [];
        }

        loadAllSites();
        loadRoadClosures();

        function openJoinModal(drillId, drillTitle) {
            document.getElementById('joinModal').style.display = 'block';
            document.getElementById('drillIdInput').value = drillId;
            document.getElementById('drillTitleDisplay').textContent = drillTitle;
            document.getElementById('joinDrillForm').reset();
        }

        function closeJoinModal() {
            document.getElementById('joinModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('joinModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        async function submitJoinDrill() {
            const drillId = document.getElementById('drillIdInput').value;
            const name = document.getElementById('participantName').value.trim();
            const email = document.getElementById('participantEmail').value.trim();
            const barangay = document.getElementById('participantBarangay').value.trim();
            const phone = document.getElementById('participantPhone').value.trim();

            if (!name) {
                alert('Please enter your name');
                return;
            }

            if (!email) {
                alert('Please enter your email');
                return;
            }

            // Simple email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address');
                return;
            }

            if (!barangay) {
                alert('Please select your barangay');
                return;
            }

            // Show loading state
            const submitBtn = document.querySelector('#joinDrillForm button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';
            submitBtn.disabled = true;

            try {
                const response = await fetch('api/drills/join-drill.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        drill_id: drillId,
                        name: name,
                        email: email,
                        barangay: barangay,
                        phone: phone
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Close the modal first
                    closeJoinModal();
                    
                    // Show confetti celebration!
                    celebrateRegistration(name, data.data.drill_title, data.email_sent);
                    
                } else {
                    alert('Error: ' + (data.message || 'Failed to join drill'));
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to join drill. Please try again.');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        }

        /**
         * Celebrate drill registration with confetti and modal
         */
        function celebrateRegistration(participantName, drillTitle, emailSent) {
            // Fire confetti!
            const duration = 3000;
            const animationEnd = Date.now() + duration;
            const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 10000 };

            function randomInRange(min, max) {
                return Math.random() * (max - min) + min;
            }

            const interval = setInterval(function() {
                const timeLeft = animationEnd - Date.now();

                if (timeLeft <= 0) {
                    return clearInterval(interval);
                }

                const particleCount = 50 * (timeLeft / duration);

                // Fire confetti from different positions
                confetti(Object.assign({}, defaults, { 
                    particleCount, 
                    origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 },
                    colors: ['#ff0065', '#6161ff', '#00167a', '#10b981']
                }));
                confetti(Object.assign({}, defaults, { 
                    particleCount, 
                    origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 },
                    colors: ['#ff0065', '#6161ff', '#00167a', '#10b981']
                }));
            }, 250);

            // Show success modal
            showSuccessModal(participantName, drillTitle, emailSent);
        }

        /**
         * Show success modal with celebration message
         */
        function showSuccessModal(participantName, drillTitle, emailSent) {
            // Create modal HTML
            const modalHTML = `
                <div id="successModal" style="
                    display: flex;
                    position: fixed;
                    z-index: 10000;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0, 22, 122, 0.8);
                    backdrop-filter: blur(10px);
                    align-items: center;
                    justify-content: center;
                    animation: fadeIn 0.3s ease;
                ">
                    <div style="
                        background: white;
                        padding: 50px;
                        border-radius: 24px;
                        max-width: 550px;
                        width: 90%;
                        box-shadow: 0 25px 80px rgba(0, 22, 122, 0.4);
                        border: 4px solid #6161ff;
                        text-align: center;
                        animation: bounceIn 0.5s ease;
                    ">
                        <!-- Success Icon -->
                        <div style="
                            width: 100px;
                            height: 100px;
                            background: linear-gradient(135deg, #10b981, #059669);
                            border-radius: 50%;
                            margin: 0 auto 30px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            box-shadow: 0 10px 40px rgba(16, 185, 129, 0.4);
                        ">
                            <span style="font-size: 60px; color: white;">✓</span>
                        </div>

                        <!-- Success Message -->
                        <h2 style="
                            color: #00167a;
                            font-size: 32px;
                            font-weight: 900;
                            margin: 0 0 15px 0;
                        ">
                            Registration Successful!
                        </h2>

                        <p style="
                            font-size: 20px;
                            color: #6161ff;
                            font-weight: 700;
                            margin: 0 0 25px 0;
                        ">
                            Thank you, <strong>${participantName}</strong>! 🎉
                        </p>

                        <!-- Drill Info -->
                        <div style="
                            background: linear-gradient(135deg, #f8f9ff, #fef3f8);
                            padding: 25px;
                            border-radius: 16px;
                            margin: 25px 0;
                            border-left: 5px solid #6161ff;
                            text-align: left;
                        ">
                            <h3 style="
                                color: #00167a;
                                margin: 0 0 10px 0;
                                font-size: 18px;
                            ">
                                📋 You're registered for:
                            </h3>
                            <p style="
                                color: #475569;
                                margin: 0;
                                font-size: 16px;
                                font-weight: 600;
                            ">
                                ${drillTitle}
                            </p>
                        </div>

                        <!-- Email Confirmation -->
                        ${emailSent ? `
                            <div style="
                                background: #d1fae5;
                                padding: 15px;
                                border-radius: 12px;
                                margin: 20px 0;
                                border-left: 5px solid #10b981;
                            ">
                                <p style="
                                    margin: 0;
                                    color: #065f46;
                                    font-weight: 700;
                                    font-size: 15px;
                                ">
                                    📧 Confirmation email sent!
                                </p>
                                <p style="
                                    margin: 5px 0 0;
                                    color: #047857;
                                    font-size: 13px;
                                ">
                                    Check your inbox for drill details
                                </p>
                            </div>
                        ` : ''}

                        <!-- What's Next -->
                        <div style="
                            text-align: left;
                            margin: 25px 0;
                            padding: 20px;
                            background: #fff7ed;
                            border-radius: 12px;
                            border-left: 5px solid #f59e0b;
                        ">
                            <h4 style="
                                color: #92400e;
                                margin: 0 0 12px 0;
                                font-size: 16px;
                            ">
                                📌 What's Next?
                            </h4>
                            <ul style="
                                margin: 0;
                                padding-left: 20px;
                                color: #78350f;
                                font-size: 14px;
                                line-height: 1.8;
                            ">
                                <li>Check your email for drill details</li>
                                <li>Review the drill instructions</li>
                                <li>Prepare any required materials</li>
                                <li>Mark your calendar</li>
                            </ul>
                        </div>

                        <!-- Close Button -->
                        <button onclick="closeSuccessModal()" style="
                            background: linear-gradient(135deg, #6161ff, #ff0065);
                            color: white;
                            border: none;
                            padding: 18px 50px;
                            font-size: 18px;
                            font-weight: 800;
                            border-radius: 12px;
                            cursor: pointer;
                            margin-top: 20px;
                            box-shadow: 0 8px 25px rgba(97, 97, 255, 0.4);
                            transition: all 0.3s ease;
                        " onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 35px rgba(97, 97, 255, 0.5)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(97, 97, 255, 0.4)'">
                            🎉 Awesome!
                        </button>
                    </div>
                </div>

                <style>
                    @keyframes fadeIn {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }

                    @keyframes bounceIn {
                        0% { transform: scale(0.3); opacity: 0; }
                        50% { transform: scale(1.05); }
                        70% { transform: scale(0.9); }
                        100% { transform: scale(1); opacity: 1; }
                    }
                </style>
            `;

            // Add modal to page
            document.body.insertAdjacentHTML('beforeend', modalHTML);

            // Reload page when modal closes (to update drill participant count)
            window.closeSuccessModal = function() {
                const modal = document.getElementById('successModal');
                modal.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => {
                    modal.remove();
                    location.reload(); // Reload to show updated participant status
                }, 300);
            };
        }

        // Add fadeOut animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
        `;
        document.head.appendChild(style);
        async function completeMission(drillId, success) {
            if (!confirm(success ? 'Mark mission as completed?' : 'Abandon this mission?')) return;
            try {
                const res = await fetch('api/drills/finish-drill.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ drill_id: drillId, success: success ? 1 : 0 })
                });
                const j = await res.json();
                if (j.success) {
                    const el = document.getElementById('mission-controls-' + drillId);
                    if (el) {
                        if (success) el.innerHTML = '<span class="small" style="color:#10b981;font-weight:800;">✓ Mission Complete</span>';
                        else el.innerHTML = '<span class="small" style="color:#64748b;font-weight:800;">✗ Mission Abandoned</span>';
                    } else {
                        location.reload();
                    }
                    alert(j.message || 'Updated mission status');
                } else {
                    alert('Error: ' + (j.message || 'Could not update mission'));
                }
            } catch (err) {
                console.error(err);
                alert('Failed to update mission status');
            }
        }
    </script>

    <!-- Join Drill Modal -->
    <div id="joinModal" style="display:none; position:fixed; z-index:10001; left:0; top:0; width:100%; height:100%; background-color:rgba(0, 22, 122, 0.7); backdrop-filter: blur(5px);">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Join Drill</h2>
                <button class="modal-close" onclick="closeJoinModal()">×</button>
            </div>
            <p style="margin:0 0 25px 0; color:#64748b; font-weight: 600;"><strong>Drill:</strong> <span id="drillTitleDisplay" style="color: var(--navy);"></span></p>
            
            <form id="joinDrillForm" onsubmit="event.preventDefault(); submitJoinDrill();">
                <input type="hidden" id="drillIdInput">
                
                <div class="form-group">
                    <label for="participantName">Full Name *</label>
                    <input type="text" id="participantName" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="participantEmail">Email Address *</label>
                    <input type="email" id="participantEmail" placeholder="example@email.com" required>
                </div>

                <div class="form-group">
                    <label for="participantBarangay">Barangay *</label>
                    <select id="participantBarangay" required>
                        <option value="">-- Select Barangay --</option>
                        <option value="Abanao-Zandueta-Kayong-Chugum">Abanao-Zandueta-Kayong-Chugum</option>
                        <option value="Alfonso Tabora">Alfonso Tabora</option>
                        <option value="Ambiong">Ambiong</option>
                        <option value="Andres Bonifacio">Andres Bonifacio</option>
                        <option value="Apugan-Loakan">Apugan-Loakan</option>
                        <option value="Asin Road">Asin Road</option>
                        <option value="Atok Trail">Atok Trail</option>
                        <option value="Aurora Hill Proper">Aurora Hill Proper</option>
                        <option value="Aurora Hill, North Central">Aurora Hill, North Central</option>
                        <option value="Aurora Hill, South Central">Aurora Hill, South Central</option>
                        <option value="Bagong Lipunan (Market Area)">Bagong Lipunan (Market Area)</option>
                        <option value="Bakakeng Central">Bakakeng Central</option>
                        <option value="Bakakeng North">Bakakeng North</option>
                        <option value="Bal-Marcoville (Marcoville)">Bal-Marcoville (Marcoville)</option>
                        <option value="Balsigan">Balsigan</option>
                        <option value="Bayan Park East">Bayan Park East</option>
                        <option value="Bayan Park Village">Bayan Park Village</option>
                        <option value="Bayan Park West (Bayan Park)">Bayan Park West (Bayan Park)</option>
                        <option value="BGH Compound">BGH Compound</option>
                        <option value="Bonifacio-Caguioa-Rimando">Bonifacio-Caguioa-Rimando</option>
                        <option value="Brookside">Brookside</option>
                        <option value="Brookspoint">Brookspoint</option>
                        <option value="Cabinet Hill-Teacher's Camp">Cabinet Hill-Teacher's Camp</option>
                        <option value="Camdas Subdivision">Camdas Subdivision</option>
                        <option value="Camp 7">Camp 7</option>
                        <option value="Camp 8">Camp 8</option>
                        <option value="Camp Allen">Camp Allen</option>
                        <option value="Campo Filipino">Campo Filipino</option>
                        <option value="City Camp Central">City Camp Central</option>
                        <option value="City Camp Proper">City Camp Proper</option>
                        <option value="Country Club Village">Country Club Village</option>
                        <option value="Cresencia Village Barangay">Cresencia Village Barangay</option>
                        <option value="Dagsian, Lower">Dagsian, Lower</option>
                        <option value="Dagsian, Upper">Dagsian, Upper</option>
                        <option value="Department of Public Services (DPS) Compound">Department of Public Services (DPS) Compound</option>
                        <option value="Dizon Subdivision">Dizon Subdivision</option>
                        <option value="Dominican Hill Mirador">Dominican Hill Mirador</option>
                        <option value="Dontogan">Dontogan</option>
                        <option value="Engineers' Hill">Engineers' Hill</option>
                        <option value="Fairview Village">Fairview Village</option>
                        <option value="Ferdinand (Happy Homes-Campo)">Ferdinand (Happy Homes-Campo)</option>
                        <option value="Fort del Pilar">Fort del Pilar</option>
                        <option value="Gabriela Silang">Gabriela Silang</option>
                        <option value="General Emilio F. Aguinaldo">General Emilio F. Aguinaldo</option>
                        <option value="General Luna, Lower">General Luna, Lower</option>
                        <option value="General Luna, Upper">General Luna, Upper</option>
                        <option value="Gibraltar">Gibraltar</option>
                        <option value="Greenwater Village">Greenwater Village</option>
                        <option value="Guisad Central">Guisad Central</option>
                        <option value="Guisad Sorong">Guisad Sorong</option>
                        <option value="Happy Hollow">Happy Hollow</option>
                        <option value="Happy Homes (Happy Homes-Lucban)">Happy Homes (Happy Homes-Lucban)</option>
                        <option value="Harrison-Claudio Carantes">Harrison-Claudio Carantes</option>
                        <option value="Hillside">Hillside</option>
                        <option value="Holy Ghost Extension">Holy Ghost Extension</option>
                        <option value="Holy Ghost Proper">Holy Ghost Proper</option>
                        <option value="Honeymoon (Honeymoon-Holy Ghost)">Honeymoon (Honeymoon-Holy Ghost)</option>
                        <option value="Imelda R. Marcos (La Salle)">Imelda R. Marcos (La Salle)</option>
                        <option value="Imelda Village">Imelda Village</option>
                        <option value="Irisan">Irisan</option>
                        <option value="Kabayanihan">Kabayanihan</option>
                        <option value="Kagitingan">Kagitingan</option>
                        <option value="Kayang Extension">Kayang Extension</option>
                        <option value="Kayang-Hilltop">Kayang-Hilltop</option>
                        <option value="Kias">Kias</option>
                        <option value="Legarda-Burnham-Kisad">Legarda-Burnham-Kisad</option>
                        <option value="Liwanag-Loakan">Liwanag-Loakan</option>
                        <option value="Loakan Proper">Loakan Proper</option>
                        <option value="Lopez Jaena">Lopez Jaena</option>
                        <option value="Lourdes Subdivision Extension">Lourdes Subdivision Extension</option>
                        <option value="Lourdes Subdivision, Lower">Lourdes Subdivision, Lower</option>
                        <option value="Lourdes Subdivision, Proper">Lourdes Subdivision, Proper</option>
                        <option value="Lualhati">Lualhati</option>
                        <option value="Lucnab">Lucnab</option>
                        <option value="Magsaysay Private Road">Magsaysay Private Road</option>
                        <option value="Magsaysay, Lower">Magsaysay, Lower</option>
                        <option value="Magsaysay, Upper">Magsaysay, Upper</option>
                        <option value="Malcolm Square-Perfecto">Malcolm Square-Perfecto</option>
                        <option value="Manuel A. Roxas">Manuel A. Roxas</option>
                        <option value="Market Subdivision, Upper">Market Subdivision, Upper</option>
                        <option value="Middle Quezon Hill Subdivision">Middle Quezon Hill Subdivision</option>
                        <option value="Military Cut-off">Military Cut-off</option>
                        <option value="Mines View Park">Mines View Park</option>
                        <option value="Modern Site, East">Modern Site, East</option>
                        <option value="Modern Site, West">Modern Site, West</option>
                        <option value="MRR-Queen Of Peace">MRR-Queen Of Peace</option>
                        <option value="New Lucban">New Lucban</option>
                        <option value="Outlook Drive">Outlook Drive</option>
                        <option value="Pacdal">Pacdal</option>
                        <option value="Padre Burgos">Padre Burgos</option>
                        <option value="Padre Zamora">Padre Zamora</option>
                        <option value="Palma-Urbano">Palma-Urbano</option>
                        <option value="Phil-Am">Phil-Am</option>
                        <option value="Pinget">Pinget</option>
                        <option value="Pinsao Pilot Project">Pinsao Pilot Project</option>
                        <option value="Pinsao Proper">Pinsao Proper</option>
                        <option value="Pucsusan">Pucsusan</option>
                        <option value="Puliwes">Puliwes</option>
                        <option value="Quezon Hill Proper">Quezon Hill Proper</option>
                        <option value="Quezon Hill, Upper">Quezon Hill, Upper</option>
                        <option value="Quirino Hill, East">Quirino Hill, East</option>
                        <option value="Quirino Hill, Lower">Quirino Hill, Lower</option>
                        <option value="Quirino Hill, Middle">Quirino Hill, Middle</option>
                        <option value="Quirino Hill, West">Quirino Hill, West</option>
                        <option value="Quirino-Magsaysay, Upper">Quirino-Magsaysay, Upper</option>
                        <option value="Rizal Monument Area">Rizal Monument Area</option>
                        <option value="Rock Quarry, Lower">Rock Quarry, Lower</option>
                        <option value="Rock Quarry, Middle">Rock Quarry, Middle</option>
                        <option value="Rock Quarry, Upper">Rock Quarry, Upper</option>
                        <option value="Saint Joseph Village">Saint Joseph Village</option>
                        <option value="Salud Mitra">Salud Mitra</option>
                        <option value="San Antonio Village">San Antonio Village</option>
                        <option value="San Luis Village">San Luis Village</option>
                        <option value="San Roque Village">San Roque Village</option>
                        <option value="San Vicente">San Vicente</option>
                        <option value="Sanitary Camp South">Sanitary Camp South</option>
                        <option value="Sanitary Camp, North">Sanitary Camp, North</option>
                        <option value="Santa Escolastica">Santa Escolastica</option>
                        <option value="Santo Rosario Valley">Santo Rosario Valley</option>
                        <option value="Santo Tomas Proper">Santo Tomas Proper</option>
                        <option value="Santo Tomas School Area">Santo Tomas School Area</option>
                        <option value="Scout Barrio">Scout Barrio</option>
                        <option value="Session Road Area">Session Road Area</option>
                        <option value="Slaughter House Area">Slaughter House Area</option>
                        <option value="SLU-SVP Housing Village">SLU-SVP Housing Village</option>
                        <option value="South Drive">South Drive</option>
                        <option value="Teodora Alonzo">Teodora Alonzo</option>
                        <option value="Trancoville">Trancoville</option>
                        <option value="Victoria Village">Victoria Village</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="participantPhone">Phone Number (Optional)</label>
                    <input type="tel" id="participantPhone" placeholder="09XXXXXXXXX">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        🎖️ Join Drill
                    </button>
                    <button type="button" class="btn-cancel" onclick="closeJoinModal()">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/chatbot.js"></script>
    <script>
    // Gamification JavaScript
    let currentQuiz = null;
    let currentQuestions = [];
    let currentQuestionIndex = 0;
    let score = 0;
    let totalPoints = 0;

    // Load leaderboard on page load
    function loadLeaderboard() {
        fetch('api/gamification/get-leaderboard.php?limit=5')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.leaderboard.length > 0) {
                    const html = data.leaderboard.map(entry => `
                        <div class="leaderboard-item">
                            <div class="leaderboard-rank">${entry.medal || '📍'}</div>
                            <div class="leaderboard-info">
                                <div class="leaderboard-barangay">${entry.barangay}</div>
                                <div class="leaderboard-users">${entry.total_users} residents</div>
                            </div>
                            <div class="leaderboard-score">${entry.avg_preparedness}%</div>
                        </div>
                    `).join('');
                    document.getElementById('leaderboard-list').innerHTML = html;
                } else {
                    document.getElementById('leaderboard-list').innerHTML = `
                        <div style="text-align: center; padding: 40px; color: #64748b;">
                            <p style="font-size: 18px; margin-bottom: 10px;">🎯 Be the first!</p>
                            <p>Take a quiz to put your barangay on the leaderboard</p>
                        </div>
                    `;
                }
            })
            .catch(err => {
                console.error('Leaderboard error:', err);
                document.getElementById('leaderboard-list').innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #ef4444;">
                        <p>Error loading leaderboard. Please try again.</p>
                    </div>
                `;
            });
    }

    // Start quiz
    function startQuiz(category) {
        const categoryNames = {
            'earthquake': 'Earthquake Safety',
            'typhoon': 'Typhoon Preparedness',
            'fire': 'Fire Safety',
            'flood': 'Flood Safety',
            'landslide': 'Landslide Awareness',
            'general': 'General Preparedness'
        };

        currentQuiz = category;
        currentQuestionIndex = 0;
        score = 0;
        totalPoints = 0;
        
        document.getElementById('quiz-modal-title').textContent = categoryNames[category] + ' Quiz';
        document.getElementById('quiz-modal').classList.add('active');
        document.getElementById('quiz-body').innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p style="margin-top: 15px; color: #64748b;">Loading questions...</p>
            </div>
        `;

        // Load questions
        fetch(`api/gamification/get-quiz.php?category=${category}&limit=5`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.questions.length > 0) {
                    currentQuestions = data.questions;
                    document.getElementById('total-questions').textContent = data.questions.length;
                    showQuestion();
                } else {
                    document.getElementById('quiz-body').innerHTML = `
                        <div style="text-align: center; padding: 40px;">
                            <p style="color: #ef4444; font-size: 18px;">Error loading quiz questions</p>
                            <button class="btn-primary" onclick="closeQuiz()">Close</button>
                        </div>
                    `;
                }
            })
            .catch(err => {
                console.error('Quiz load error:', err);
                document.getElementById('quiz-body').innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <p style="color: #ef4444; font-size: 18px;">Network error. Please check your connection.</p>
                        <button class="btn-primary" onclick="closeQuiz()">Close</button>
                    </div>
                `;
            });
    }

    // Show current question
    function showQuestion() {
        const question = currentQuestions[currentQuestionIndex];
        const questionNumber = currentQuestionIndex + 1;
        const totalQuestions = currentQuestions.length;
        const progress = (questionNumber / totalQuestions) * 100;

        document.getElementById('current-question').textContent = questionNumber;
        document.getElementById('current-score').textContent = totalPoints;
        document.getElementById('progress-fill').style.width = progress + '%';

        document.getElementById('quiz-body').innerHTML = `
            <div class="question-container">
                <div class="question-text">${question.question}</div>
                <div class="options-list">
                    <button class="option-btn" onclick="selectAnswer('A', ${question.id})">
                        <strong>A:</strong> ${question.option_a}
                    </button>
                    <button class="option-btn" onclick="selectAnswer('B', ${question.id})">
                        <strong>B:</strong> ${question.option_b}
                    </button>
                    <button class="option-btn" onclick="selectAnswer('C', ${question.id})">
                        <strong>C:</strong> ${question.option_c}
                    </button>
                    <button class="option-btn" onclick="selectAnswer('D', ${question.id})">
                        <strong>D:</strong> ${question.option_d}
                    </button>
                </div>
                <div id="feedback-area"></div>
            </div>
        `;
    }

    // Select answer
    let hasAnsweredCurrent = false; // track whether current question has been answered
    function selectAnswer(answer, questionId) {
        if (hasAnsweredCurrent) return; // prevent double-click
        hasAnsweredCurrent = true; // mark as answered
        // Disable all buttons
        const buttons = document.querySelectorAll('.option-btn');
        buttons.forEach(btn => btn.classList.add('disabled'));

        // Submit answer
        fetch('api/gamification/submit-answer.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                question_id: questionId,
                selected_answer: answer
            })
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                // if quiz API returned an error (e.g. not logged in) show message and allow retry
                alert(data.message || 'There was an issue submitting your answer.');
                hasAnsweredCurrent = false;
                buttons.forEach(btn => btn.classList.remove('disabled'));
                return;
            }

            // Mark selected button
            buttons.forEach(btn => {
                const btnLetter = btn.textContent.trim()[0];
                if (btnLetter === answer) {
                    btn.classList.add(data.is_correct ? 'correct' : 'incorrect');
                }
                if (btnLetter === data.correct_answer) {
                    btn.classList.add('correct');
                }
            });

            // Update score
            if (data.is_correct) {
                score++;
                totalPoints += data.points_earned;
            }

            // Show feedback
            document.getElementById('feedback-area').innerHTML = `
                <div class="feedback-box ${data.is_correct ? 'correct' : 'incorrect'}">
                    <div class="feedback-title">
                        ${data.is_correct ? '✅ Correct!' : '❌ Incorrect'}
                        ${data.is_correct ? `<span class="points-earned">+${data.points_earned} points</span>` : ''}
                    </div>
                    <div class="feedback-text">${data.explanation}</div>
                </div>
                <div class="quiz-actions">
                    <button class="btn-primary" onclick="nextQuestion()">
                        ${currentQuestionIndex < currentQuestions.length - 1 ? 'Next Question →' : 'Finish Quiz 🎉'}
                    </button>
                </div>
            `;
        })
        .catch(err => {
            console.error('Submit error:', err);
            alert('Error submitting answer. Please try again.');
        });
    }

    // Next question
    function nextQuestion() {
        currentQuestionIndex++;
        hasAnsweredCurrent = false; // Reset flag for next question
        if (currentQuestionIndex < currentQuestions.length) {
            showQuestion();
        } else {
            showResults();
        }
    }

    // Show results
    function showResults() {
        const percentage = Math.round((score / currentQuestions.length) * 100);
        
        document.getElementById('quiz-body').innerHTML = `
            <div class="quiz-complete">
                <div class="complete-icon">🎉</div>
                <div class="complete-title">Quiz Complete!</div>
                <div class="complete-score">${score} / ${currentQuestions.length}</div>
                
                <div class="complete-stats">
                    <div class="stat-item">
                        <div class="stat-value">${percentage}%</div>
                        <div class="stat-label">Accuracy</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">${totalPoints}</div>
                        <div class="stat-label">Points Earned</div>
                    </div>
                </div>

                <p style="color: #64748b; margin: 20px 0;">
                    ${percentage >= 80 ? '🌟 Excellent work! You\'re well-prepared!' : 
                    percentage >= 60 ? '👍 Good job! Keep learning!' :
                    '📚 Keep studying to improve your preparedness!'}
                </p>

                <div class="quiz-actions">
                    <button class="btn-primary" onclick="closeQuiz(); loadLeaderboard();">Close</button>
                </div>
            </div>
        `;
    }

    // Close quiz
    function closeQuiz() {
        document.getElementById('quiz-modal').classList.remove('active');
    }

    // certification email redirect for guests
    function startCertWithEmail() {
        const emailInput = document.getElementById('cert-email');
        const barangayInput = document.getElementById('cert-barangay');
        const email = emailInput.value.trim();
        const barangay = barangayInput.value.trim();
        if (!email) {
            alert('Please enter your email address');
            emailInput.focus();
            return;
        }
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!pattern.test(email)) {
            alert('Please enter a valid email address');
            emailInput.focus();
            return;
        }
        if (!barangay) {
            alert('Please enter your barangay');
            barangayInput.focus();
            return;
        }
        // redirect to certification page with email and barangay parameters
        window.location.href = 'certification-quiz.php?email=' + encodeURIComponent(email) + '&barangay=' + encodeURIComponent(barangay);
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        loadLeaderboard();
        // Refresh leaderboard every 30 seconds
        setInterval(loadLeaderboard, 30000);
    });
    </script>
</body>
</html>