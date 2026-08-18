<?php
/**
 * index.php
 * -----------------------------------------------------------------------
 * App entry point. Just routes to the dashboard (if logged in) or the
 * login page (if not). No HTML of its own.
 */

define('DCMS_APP', true);
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (current_user()) {
    redirect('/reports/dashboard.php');
} else {
    redirect('/auth/login.php');
}