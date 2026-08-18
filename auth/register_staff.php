<?php
/**
 * register_staff.php
 * -----------------------------------------------------------------------
 * ADMIN-ONLY page to create new staff accounts (dentist / receptionist /
 * another admin). This is how Dora adds her team after the first admin
 * account exists (see database/seed_admin.php for creating THAT one).
 */

define('DCMS_APP', true);
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role([ROLE_ADMIN]); // only admins may reach this page

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';

    $validRoles = [ROLE_ADMIN, ROLE_DENTIST, ROLE_RECEPTIONIST];

    if ($fullName === '' || $email === '' || $password === '' || !in_array($role, $validRoles, true)) {
        $error = 'Please fill in all fields with a valid role.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        // Check email isn't already used
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'That email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                "INSERT INTO users (full_name, email, password_hash, role, is_active, created_at)
                 VALUES (?, ?, ?, ?, 1, NOW())"
            );
            $stmt->execute([$fullName, $email, $hash, $role]);

            flash_set('success', "Staff account created for {$fullName}.");
            redirect('/auth/register_staff.php');
        }
    }
}

$pageTitle = 'Register Staff';
require __DIR__ . '/../includes/header.php';
?>

<h2 class="font-display" style="margin-bottom:4px;">Register Staff Account</h2>
<p style="color:var(--muted-foreground);margin-bottom:24px;font-size:13.5px;">Admin-only: create login access for a dentist or receptionist.</p>

<?php if ($error): ?>
    <div class="flash flash-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width:420px;">
    <form method="POST" action="">
        <div class="form-group">
            <label class="form-label" for="full_name">Full name</label>
            <input class="form-input" type="text" id="full_name" name="full_name" required
                   value="<?= e($_POST['full_name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input class="form-input" type="email" id="email" name="email" required
                   value="<?= e($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label class="form-label" for="password">Temporary password</label>
            <input class="form-input" type="password" id="password" name="password" required minlength="8">
        </div>
        <div class="form-group">
            <label class="form-label" for="role">Role</label>
            <select class="form-input" id="role" name="role" required>
                <option value="">Select role…</option>
                <option value="dentist">Dentist</option>
                <option value="receptionist">Receptionist</option>
                <option value="admin">Administrator</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Create account</button>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>