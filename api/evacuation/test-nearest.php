<?php
// api/evacuation/test-nearest.php - Test file for debugging
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Step 1: Starting test...\n";

// Test 1: Check if db_connect.php exists
$db_path_1 = __DIR__ . '/../../db_connect.php';
$db_path_2 = __DIR__ . '/../db_connect.php';
$db_path_3 = dirname(dirname(__DIR__)) . '/db_connect.php';

echo "Step 2: Checking database file paths...\n";
echo "Path 1: $db_path_1 - " . (file_exists($db_path_1) ? "EXISTS" : "NOT FOUND") . "\n";
echo "Path 2: $db_path_2 - " . (file_exists($db_path_2) ? "EXISTS" : "NOT FOUND") . "\n";
echo "Path 3: $db_path_3 - " . (file_exists($db_path_3) ? "EXISTS" : "NOT FOUND") . "\n";

// Try to include the database
try {
    if (file_exists($db_path_1)) {
        require_once $db_path_1;
        echo "Step 3: Database included successfully (path 1)\n";
    } elseif (file_exists($db_path_2)) {
        require_once $db_path_2;
        echo "Step 3: Database included successfully (path 2)\n";
    } elseif (file_exists($db_path_3)) {
        require_once $db_path_3;
        echo "Step 3: Database included successfully (path 3)\n";
    } else {
        die("ERROR: Cannot find db_connect.php\n");
    }
} catch (Exception $e) {
    die("ERROR including database: " . $e->getMessage() . "\n");
}

// Test 2: Check if $pdo is defined
echo "Step 4: Checking if \$pdo exists...\n";
if (!isset($pdo)) {
    die("ERROR: \$pdo variable not defined in db_connect.php\n");
}
echo "Step 4: \$pdo exists!\n";

// Test 3: Check if evacuation_sites table exists
echo "Step 5: Checking if evacuation_sites table exists...\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM evacuation_sites");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Step 5: Table exists! Found {$result['count']} evacuation sites\n";
} catch (PDOException $e) {
    die("ERROR: Cannot access evacuation_sites table: " . $e->getMessage() . "\n");
}

// Test 4: Try the actual query with test coordinates (Baguio City)
echo "Step 6: Testing nearest query with Baguio coordinates (16.4023, 120.5960)...\n";
try {
    $lat = 16.4023;
    $lng = 120.5960;
    $limit = 5;
    
    $sql = "SELECT 
                id,
                name,
                type,
                barangay,
                latitude,
                longitude,
                (6371 * acos(
                    cos(radians(:lat)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(:lng)) + 
                    sin(radians(:lat)) * 
                    sin(radians(latitude))
                )) AS distance_km
            FROM evacuation_sites 
            WHERE status = 'active'
            HAVING distance_km < 50
            ORDER BY distance_km ASC
            LIMIT :limit";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':lat', $lat, PDO::PARAM_STR);
    $stmt->bindValue(':lng', $lng, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Step 6: Query successful! Found " . count($sites) . " nearby sites\n\n";
    
    echo "Results:\n";
    echo json_encode([
        'success' => true,
        'message' => 'Test completed successfully!',
        'sites_found' => count($sites),
        'sample_sites' => array_slice($sites, 0, 3) // Show first 3 sites
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    die("\nERROR in query: " . $e->getMessage() . "\n");
}