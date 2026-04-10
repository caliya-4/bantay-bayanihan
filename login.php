<?php
session_start();
require 'config.php';
require 'db_connect.php';
$msg = '';

if ($_POST) {
    // Verify CSRF token if present
    if (isset($_POST['csrf_token']) && !verifyCSRFToken($_POST['csrf_token'])) {
        $msg = "Invalid security token. Please refresh and try again.";
    } else {
        $email = trim($_POST['email']);
        $pass = $_POST['password'];
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Check if user exists and password matches
        // Support both hashed passwords (new) and plaintext (legacy migration)
        if ($user) {
            $passwordValid = false;
            
            // Check if password is hashed (starts with $2y$)
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                // Try plaintext comparison first (for legacy accounts)
                if ($user['password'] === $pass) {
                    // Migrate to hashed password
                    $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);
                    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $updateStmt->execute([$hashedPassword, $user['id']]);
                    $passwordValid = true;
                } else {
                    // Try hashed comparison
                    $passwordValid = password_verify($pass, $user['password']);
                }
            } else {
                // Password is already hashed
                $passwordValid = password_verify($pass, $user['password']);
            }
            
            if ($passwordValid) {
                if ($user['role'] === 'responder' && $user['is_approved'] == 0) {
                    $msg = "Account awaiting approval.";
                } else {
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['barangay'] = $user['barangay'] ?? null;
                    
                    // Regenerate CSRF token
                    generateCSRFToken();
                    
                    header("Location: " . ($user['role'] === 'responder' ? 'barangay_responder/dashboard.php' : 'admin/dashboard.php'));
                    exit;
                }
            } else {
                $msg = "Wrong email or password.";
            }
        } else {
            $msg = "Wrong email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bantay Bayanihan | Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome for Shield Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/design-system.css">
    <style>
        body.login-page {
            background: linear-gradient(135deg, #6161ff, #ff0065);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-box {
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.4);
            width: 100%;
            max-width: 420px;
            text-align: center;
        }
        .login-box h2 {
            color: #0027b3;
            font-size: 32px;
            margin: 0 0 30px;
            font-weight: bold;
        }
        .shield-icon {
            font-size: 90px;
            color: linear-gradient(135deg, #CC0000, #0027b3);
            margin-bottom: 20px;
            text-shadow: 0 4px 15px rgba(204,0,0,0.3);
        }
        input {
            width: 100%;
            padding: 16px;
            margin: 12px 0;
            border: 2px solid #ddd;
            border-radius: 12px;
            font-size: 16px;
            box-sizing: border-box;
            transition: 0.3s;
        }
        input:focus {
            border-color: #CC0000;
            outline: none;
            box-shadow: 0 0 0 4px rgba(204,0,0,0.2);
        }
        button {
            background: #CC0000;
            color: white;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin: 20px 0 10px;
            transition: 0.3s;
        }
        button:hover {
            background: #B30000;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(204,0,0,0.4);
        }
        .error { color: #CC0000; background: #ffe6e6; padding: 12px; border-radius: 10px; margin: 15px 0; }
        a { color: #CC0000; text-decoration: none; font-weight: bold; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body class="login-page">

<div class="login-box">
    <i class="fas fa-shield-alt shield-icon"></i>
    
    <h2>Bantay Bayanihan</h2>
    
    <form method="POST">
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">LOGIN</button>
    </form>

    <?php if($msg): ?>
        <p class="error"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <p><a href="register.php">Register as Responder</a></p>
</div>

</body>
</html>