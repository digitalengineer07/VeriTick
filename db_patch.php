<?php
require_once 'includes/db.php';
try {
    $pdo->query("ALTER TABLE Users ADD COLUMN organizer_code VARCHAR(50) DEFAULT NULL UNIQUE;");
    echo "Added organizer_code\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }

try {
    $pdo->query("ALTER TABLE Users ADD COLUMN linked_organizer_id INT DEFAULT NULL;");
    echo "Added linked_organizer_id\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }

try {
    $pdo->query("UPDATE Users SET organizer_code = CONCAT('ORG-', UPPER(SUBSTRING(MD5(RAND()), 1, 6))) WHERE role = 'admin' AND organizer_code IS NULL;");
    echo "Updated admin codes\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }
echo "Done.";
