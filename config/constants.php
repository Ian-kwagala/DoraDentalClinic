<?php
/**
 * constants.php
 * -----------------------------------------------------------------------
 * App-wide constants and the security guard used across the system.
 */

if (!defined('DCMS_APP')) {
    define('DCMS_APP', true);
}

// Human-readable app name, used in page titles / emails / SMS footers
define('APP_NAME', "Dora's Dental Gem");

// Base URL path — MUST MATCH your actual htdocs folder name.
// Your screenshot shows C:\xampp\htdocs\DoraDentalClinic, so it's set below.
define('BASE_URL', '/DoraDentalClinic');

// Allowed staff roles in the system (used by auth.php for role checks)
define('ROLE_ADMIN', 'admin');
define('ROLE_DENTIST', 'dentist');
define('ROLE_RECEPTIONIST', 'receptionist');