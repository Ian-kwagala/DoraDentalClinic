<?php
/**
 * auth.php
 * -----------------------------------------------------------------------
 * Login, session, and role-checking logic. Every protected page includes
 * this file and calls require_login() (and often require_role()) at the
 * very top, before any HTML is output.
 */

defined('DCMS_APP') or die('Direct access not permitted.');

// Start the session once, here, so every page that includes auth.php
// automatically has session access without repeating session_start().
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * attempt_login()
 * Checks email + password against the users table. Returns the user row
 * (without the password hash) on success, or null on failure.
 * Uses password_verify() to compare against the bcrypt hash stored in DB
 * — we NEVER store or compare plain-text passwords.
 */
function attempt_login(PDO $pdo, string $email, string $password): ?array
{
    $stmt = $pdo->prepare(
        "SELECT id, full_name, email, password_hash, role, is_active
         FROM users
         WHERE email = ? AND deleted_at IS NULL
         LIMIT 1"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        return null; // no such email
    }

    if ((int) $user['is_active'] !== 1) {
        return null; // account disabled by admin
    }

    if (!password_verify($password, $user['password_hash'])) {
        return null; // wrong password
    }

    // Never keep the hash floating around in the session
    unset($user['password_hash']);
    return $user;
}

/**
 * log_in_user()
 * Stores the authenticated user's details in the session.
 * session_regenerate_id() prevents session fixation attacks (a new
 * session ID is issued right at login, so an attacker who knew the old
 * pre-login session ID can't hijack the now-authenticated session).
 */
function log_in_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email']     = $user['email'];
    $_SESSION['role']      = $user['role'];
}

/**
 * current_user()
 * Quick way to grab the logged-in user's session data anywhere.
 * Returns null if nobody is logged in.
 */
function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id'        => $_SESSION['user_id'],
        'full_name' => $_SESSION['full_name'],
        'email'     => $_SESSION['email'],
        'role'      => $_SESSION['role'],
    ];
}

/**
 * require_login()
 * Call at the top of any page that should only be visible to logged-in
 * staff. Bounces guests to the login page.
 */
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        redirect('/auth/login.php');
    }
}

/**
 * require_role()
 * Call after require_login() on pages restricted to specific roles.
 * Example: require_role(['admin']); // admin-only page
 *          require_role(['admin', 'dentist']); // admin or dentist
 */
function require_role(array $allowedRoles): void
{
    require_login();
    if (!in_array($_SESSION['role'], $allowedRoles, true)) {
        // Logged in, but not allowed here — send them to their own dashboard
        // instead of a blank error page.
        flash_set('error', "You don't have permission to access that page.");
        redirect('/reports/dashboard.php');
    }
}

/**
 * log_out_user()
 * Destroys the session completely (used by auth/logout.php).
 */
function log_out_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie('PHPSESSID', '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}