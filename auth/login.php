<?php
/**
 * login.php
 * -----------------------------------------------------------------------
 * Staff login screen, styled after the mockup: role picker + email +
 * password. NOTE: the "role" radio button here is just a UI convenience
 * (it doesn't affect the query) — the REAL role check happens by looking
 * up whatever role is actually stored on that user's account in the
 * database. This stops someone from picking "Admin" in the UI to try to
 * get admin access with a receptionist's credentials.
 */

define('DCMS_APP', true); // this page IS an entry point, so it defines the flag itself
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, don't show the login page again
if (current_user()) {
    redirect('/reports/dashboard.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $user = attempt_login($pdo, $email, $password);
        if ($user) {
            log_in_user($user);
            redirect('/reports/dashboard.php');
        } else {
            // Deliberately vague — don't reveal whether it was the email
            // or password that was wrong (avoids helping account guessing).
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in · <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="auth-screen">
    <!-- Left hero panel (hidden on mobile via CSS) -->
    <div class="auth-hero">
        <div style="display:flex;align-items:center;gap:10px;">
            <i data-lucide="gem"></i>
            <strong><?= e(APP_NAME) ?></strong>
        </div>
        <div>
            <h1>Every smile, every record,<br>in one calm place.</h1>
            <p style="opacity:0.85;max-width:420px;">Scheduling, odontograms, billing and patient reminders — designed for the pace of a busy dental practice.</p>
        </div>
        <div style="display:flex;gap:32px;font-size:13px;opacity:0.85;">
            <div><strong style="font-size:20px;display:block;">128</strong>Visits this week</div>
            <div><strong style="font-size:20px;display:block;">4</strong>Treatment chairs</div>
            <div><strong style="font-size:20px;display:block;">98%</strong>Reminder delivery</div>
        </div>
    </div>

    <!-- Right login form panel -->
    <div class="auth-panel">
        <div class="auth-panel-inner rise-in">
            <h2 style="margin-bottom:6px;">Welcome back</h2>
            <p style="color:var(--muted-foreground);margin-bottom:24px;font-size:13.5px;">Choose your role and sign in to continue.</p>

            <?php if ($error): ?>
                <div class="flash flash-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <!-- Role picker: cosmetic only, see comment at top of file -->
                <div class="form-group">
                    <label class="role-option selected">
                        <input type="radio" name="role_hint" value="admin" checked>
                        <div>
                            <div style="font-weight:600;font-size:13.5px;">Administrator</div>
                            <div style="font-size:11.5px;color:var(--muted-foreground);">Full clinic oversight & reports</div>
                        </div>
                    </label>
                    <label class="role-option">
                        <input type="radio" name="role_hint" value="dentist">
                        <div>
                            <div style="font-weight:600;font-size:13.5px;">Dentist</div>
                            <div style="font-size:11.5px;color:var(--muted-foreground);">Charts, notes & prescriptions</div>
                        </div>
                    </label>
                    <label class="role-option">
                        <input type="radio" name="role_hint" value="receptionist">
                        <div>
                            <div style="font-weight:600;font-size:13.5px;">Receptionist</div>
                            <div style="font-size:11.5px;color:var(--muted-foreground);">Bookings, billing & reminders</div>
                        </div>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Staff email</label>
                    <input class="form-input" type="email" id="email" name="email" required
                           value="<?= e($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-input" type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Sign in →</button>
            </form>

            <p style="text-align:center;margin-top:20px;font-size:11.5px;color:var(--muted-foreground);">
                Protected clinic system · Dora's Dental Gem
            </p>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/script.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>