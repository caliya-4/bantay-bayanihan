<?php
// Setup script for road_closures table
require 'db_connect.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS `road_closures` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `name` varchar(255) DEFAULT 'Road Closure',
      `description` text,
      `start_lat` decimal(10,8) NOT NULL,
      `start_lng` decimal(11,8) NOT NULL,
      `end_lat` decimal(10,8) NOT NULL,
      `end_lng` decimal(11,8) NOT NULL,
      `reported_by` int(11) DEFAULT NULL,
      `status` enum('active','inactive') DEFAULT 'active',
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql);
    echo "✅ Road closures table created successfully!";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
