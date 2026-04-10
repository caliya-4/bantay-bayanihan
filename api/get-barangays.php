<?php
header('Content-Type: application/json');
require '../db_connect.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    if ($query) {
        // Search barangays by name
        $stmt = $pdo->prepare("
            SELECT DISTINCT barangay 
            FROM barangay_stats 
            WHERE barangay LIKE ? 
            ORDER BY barangay ASC
            LIMIT 20
        ");
        $stmt->execute(['%' . $query . '%']);
    } else {
        // Get all barangays if no search query
        $stmt = $pdo->query("
            SELECT DISTINCT barangay 
            FROM barangay_stats 
            ORDER BY barangay ASC
        ");
    }
    
    $barangays = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['success' => true, 'data' => $barangays]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
