<?php
// api/evacuation/find-table.php - Find the correct table name
header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../db_connect.php';

echo "=== FINDING EVACUATION TABLE ===\n\n";

// Step 1: Show all tables
echo "Step 1: All tables in database:\n";
echo "================================\n";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    echo "\n";
    
    // Step 2: Look for evacuation-related tables
    echo "Step 2: Looking for evacuation-related tables:\n";
    echo "================================================\n";
    
    $evacuation_tables = array_filter($tables, function($table) {
        return stripos($table, 'evacuation') !== false || 
               stripos($table, 'center') !== false ||
               stripos($table, 'site') !== false;
    });
    
    if (empty($evacuation_tables)) {
        echo "❌ No evacuation-related tables found!\n\n";
        echo "Checking for tables with location data (latitude/longitude):\n";
        echo "=============================================================\n";
        
        // Check each table for latitude/longitude columns
        foreach ($tables as $table) {
            try {
                $cols = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_COLUMN);
                $hasLat = in_array('latitude', $cols);
                $hasLng = in_array('longitude', $cols);
                
                if ($hasLat && $hasLng) {
                    echo "✅ $table (has latitude and longitude)\n";
                    
                    // Show sample data
                    $sample = $pdo->query("SELECT * FROM `$table` LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                    if ($sample) {
                        echo "   Sample columns: " . implode(', ', array_keys($sample)) . "\n";
                    }
                }
            } catch (Exception $e) {
                // Skip tables we can't access
            }
        }
    } else {
        echo "✅ Found evacuation tables:\n";
        foreach ($evacuation_tables as $table) {
            echo "- $table\n";
            
            // Show structure
            try {
                $cols = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
                echo "  Columns:\n";
                foreach ($cols as $col) {
                    echo "    - {$col['Field']} ({$col['Type']})\n";
                }
                
                // Show count
                $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                echo "  Total records: $count\n\n";
            } catch (Exception $e) {
                echo "  Error: " . $e->getMessage() . "\n\n";
            }
        }
    }
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===\n";
echo "Copy the correct table name and update your API files!\n";