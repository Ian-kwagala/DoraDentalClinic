<?php
/**
 * seed_admin.php
 * -----------------------------------------------------------------------
 * ONE-TIME SCRIPT to create the very first admin account (chicken-and-egg
 * problem: register_staff.php requires being logged in as admin already,
 * so this creates that first admin directly).
 *
 * HOW TO USE:
 * 1. Import dcms_schema.sql first.
 * 2. Edit the $fullName / $email / $password values below.
 * 3. Visit this file in your browser once: http://localhost/dental-clinic-system/database/seed_admin.php
 * 4. Log in with those credentials, then DELETE this file (or move it
 *    outside the web root) — leaving it live is a security risk since
 *    anyone who finds the URL could create another admin account.
 */

define('DCMS_APP', true);
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';

// ---- EDIT THESE before running ----
$fullName = 'Dr. Dora';
$email    = 'dora@doradentalgem.com';
$password = '123'; // change this immediately after first login
// ------------------------------------

$check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$check->execute([$email]);

if ($check->fetch()) {
    die('An account with that email already exists. Seed skipped.');
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare(
    "INSERT INTO users (full_name, email, password_hash, role, is_active, created_at)
     VALUES (?, ?, ?, 'admin', 1, NOW())"
);
$stmt->execute([$fullName, $email, $hash]);

echo "Admin account created for {$email}. Log in, then DELETE this file.";