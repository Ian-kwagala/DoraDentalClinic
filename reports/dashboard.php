<?php
/**
 * dashboard.php (SHELL VERSION — Phase 0)
 * -----------------------------------------------------------------------
 * For now this just proves login + role-based routing works, and gives
 * every role a landing page. The real stat cards, revenue chart, and
 * treatment-mix donut (from the mockup) get built in Phase 6 once there's
 * real data to show.
 */

define('DCMS_APP', true);
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login(); // any logged-in role can see the dashboard

$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';
$user = current_user();
?>

<h2 class="font-display rise-in" style="margin-bottom:4px;">
    Welcome back, <?= e(explode(' ', $user['full_name'])[0]) ?> 👋
</h2>
<p style="color:var(--muted-foreground);margin-bottom:24px;font-size:13.5px;">
    Signed in as <?= e(ucfirst($user['role'])) ?>. Phase 0 (setup) is complete —
    Patients, Appointments, Clinical, Billing, Notifications, and Reports
    modules will populate this dashboard as each phase is built.
</p>

<div class="card rise-in" style="max-width:480px;">
    <strong>System status</strong>
    <p style="color:var(--muted-foreground);font-size:13px;margin-top:6px;">
        ✅ Database connected<br>
        ✅ Authentication working<br>
        ✅ Role-based access enforced<br>
        ⏳ Awaiting Phase 1 — Patient Management
    </p>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>