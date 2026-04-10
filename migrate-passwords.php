<?php
/**
 * Password Migration Script
 * 
 * This script migrates existing plaintext passwords to hashed passwords.
 * Run this ONCE after deploying the new password hashing system.
 * 
 * Usage: php migrate-passwords.php
 * 
 * IMPORTANT: Delete this file after running the migration!
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';

echo "=== Bantay Bayanihan Password Migration ===\n\n";

try {
    // Get all users
    $stmt = $pdo->query("SELECT id, email, password FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $migrated = 0;
    $alreadyHashed = 0;
    $errors = 0;
    
    echo "Found " . count($users) . " users to check.\n\n";
    
    foreach ($users as $user) {
        // Check if password is already hashed
        // Hashed passwords start with $2y$, $2a$, $2b$, or $argon2
        $isHashed = preg_match('/^\$2[aby]?\$|^\$argon2/', $user['password']);
        
        if ($isHashed) {
            echo "✓ User {$user['email']} - Already hashed\n";
            $alreadyHashed++;
            continue;
        }
        
        // Hash the plaintext password
        try {
            $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
            
            // Update the database
            $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->execute([$hashedPassword, $user['id']]);
            
            echo "✓ User {$user['email']} - Migrated successfully\n";
            $migrated++;
        } catch (Exception $e) {
            echo "✗ User {$user['email']} - Error: " . $e->getMessage() . "\n";
            $errors++;
        }
    }
    
    echo "\n=== Migration Summary ===\n";
    echo "Total users checked: " . count($users) . "\n";
    echo "Already hashed: $alreadyHashed\n";
    echo "Migrated: $migrated\n";
    echo "Errors: $errors\n\n";
    
    if ($errors > 0) {
        echo "WARNING: Some users had errors. Please check the logs.\n";
    } else {
        echo "SUCCESS: All passwords migrated successfully!\n";
        echo "\nIMPORTANT: Delete this file now for security!\n";
    }
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
