<?php
require_once 'app/config/config.php';
require_once 'app/models/Database.php';

try {
    \App\Models\Database::getConnection();
    echo "Database connection successful!\n";
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}
