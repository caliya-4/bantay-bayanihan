<?php
// api/admin/test-closures.php - Test road closures system
header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== ROAD CLOSURES SYSTEM TEST ===\n\n";

// Step 1: Check database connection
echo "Step 1: Checking database connection...\n";
try {
    require_once __DIR__ . '/../../db_connect.php';
    echo "✅ Database connected successfully\n\n";
} catch (Exception $e) {
    die("❌ Database connection failed: " . $e->getMessage() . "\n");
}

// Step 2: Check if table exists
echo "Step 2: Checking if road_closures table exists...\n";
try {
    $stmt = $pdo->query("DESCRIBE road_closures");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Table exists with columns: " . implode(', ', $columns) . "\n\n";
} catch (PDOException $e) {
    if ($e->getCode() == '42S02') {
        echo "❌ Table does not exist!\n\n";
        echo "SOLUTION: Run this SQL in phpMyAdmin:\n";
        echo "==========================================\n";
        echo file_get_contents(__DIR__ . '/../../create_road_closures_table.sql');
        echo "\n==========================================\n";
        exit;
    } else {
        die("❌ Error checking table: " . $e->getMessage() . "\n");
    }
}

// Step 3: Count existing closures
echo "Step 3: Counting existing road closures...\n";
try {
    $count = $pdo->query("SELECT COUNT(*) FROM road_closures")->fetchColumn();
    echo "✅ Found $count road closures in database\n\n";
} catch (Exception $e) {
    die("❌ Error counting closures: " . $e->getMessage() . "\n");
}

// Step 4: Test adding a closure
echo "Step 4: Testing add closure...\n";
try {
    $test_sql = "INSERT INTO road_closures 
                 (start_lat, start_lng, end_lat, end_lng, description, severity, status, created_by) 
                 VALUES (16.4023, 120.5960, 16.4045, 120.5978, 'Test Road Closure', 'low', 'active', 1)";
    $pdo->exec($test_sql);
    $test_id = $pdo->lastInsertId();
    echo "✅ Test closure added successfully (ID: $test_id)\n\n";
    
    // Clean up test data
    echo "Step 5: Cleaning up test data...\n";
    $pdo->exec("DELETE FROM road_closures WHERE id = $test_id");
    echo "✅ Test data cleaned up\n\n";
    
} catch (Exception $e) {
    die("❌ Error testing insert: " . $e->getMessage() . "\n");
}

// Step 6: Test getting closures
echo "Step 6: Testing get closures...\n";
try {
    $stmt = $pdo->query("SELECT * FROM road_closures WHERE status = 'active' LIMIT 3");
    $closures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Successfully retrieved " . count($closures) . " active closures\n\n";
    
    if (count($closures) > 0) {
        echo "Sample closure:\n";
        $sample = $closures[0];
        echo "  ID: {$sample['id']}\n";
        echo "  Description: {$sample['description']}\n";
        echo "  Status: {$sample['status']}\n";
        echo "  Coordinates: ({$sample['start_lat']}, {$sample['start_lng']}) to ({$sample['end_lat']}, {$sample['end_lng']})\n\n";
    }
    
} catch (Exception $e) {
    die("❌ Error getting closures: " . $e->getMessage() . "\n");
}

echo "=== ALL TESTS PASSED ===\n\n";
echo "Your road closures system is working correctly!\n";
echo "You can now use the API endpoints:\n";
echo "  - POST /api/admin/add-closure.php\n";
echo "  - GET  /api/admin/get-closures.php\n";
echo "  - POST /api/admin/update-closure.php\n";
echo "  - POST /api/admin/delete-closure.php\n";