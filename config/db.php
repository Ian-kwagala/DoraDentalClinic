<?php
/**
 * db.php
 * -----------------------------------------------------------------------
 * Single shared database connection using PDO.
 */

defined('DCMS_APP') or die('Direct access not permitted.');

// ---- Connection settings -------------------------------------------------
$DB_HOST = 'localhost';
$DB_NAME = 'doradental';          // must match the database you imported in phpMyAdmin
$DB_USER = 'root';
$DB_PASS = '';              // default XAMPP root password is blank
$DB_CHARSET = 'utf8mb4';
$DB_PORT = '3309';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET};port={$DB_PORT}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}