<?php
/**
 * logout.php
 * -----------------------------------------------------------------------
 * Destroys the session and sends the user back to the login page.
 */

define('DCMS_APP', true);
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

log_out_user();
redirect('/auth/login.php');