<?php
/**
 * header.php
 * -----------------------------------------------------------------------
 * Shared page header: <head>, the dark navy sidebar nav (matching the
 * mockup), and the opening of the main content area.
 *
 * HOW TO USE:
 * Every logged-in page includes this near the top, AFTER require_login(),
 * and sets an optional $pageTitle variable before including it:
 *     $pageTitle = 'Patients';
 *     require __DIR__ . '/../includes/header.php';
 *
 * The sidebar highlights the active menu item by comparing $pageTitle,
 * and shows/hides menu items based on the logged-in user's role.
 */

defined('DCMS_APP') or die('Direct access not permitted.');

$user = current_user();
$pageTitle = $pageTitle ?? 'Dashboard';

// Menu items with the roles allowed to see each one.
// This drives BOTH which links render AND (paired with require_role on
// the destination page) which pages a role can actually reach.
$menuItems = [
    ['label' => 'Dashboard',        'href' => '/reports/dashboard.php',   'icon' => 'layout-dashboard', 'roles' => ['admin', 'dentist', 'receptionist']],
    ['label' => 'Patients',         'href' => '/patients/list.php',       'icon' => 'users',             'roles' => ['admin', 'dentist', 'receptionist']],
    ['label' => 'Appointments',     'href' => '/appointments/calendar.php','icon' => 'calendar',         'roles' => ['admin', 'dentist', 'receptionist']],
    ['label' => 'Clinical Records', 'href' => '/clinical/dental_chart.php','icon' => 'stethoscope',      'roles' => ['admin', 'dentist']],
    ['label' => 'Billing',          'href' => '/billing/invoice.php',     'icon' => 'receipt',           'roles' => ['admin', 'receptionist']],
    ['label' => 'Notifications',    'href' => '/notifications/log.php',   'icon' => 'bell',              'roles' => ['admin', 'receptionist']],
    ['label' => 'Reports',          'href' => '/reports/dashboard.php',   'icon' => 'bar-chart-3',       'roles' => ['admin']],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> · <?= e(APP_NAME) ?></title>

    <!-- Fonts used by the mockup theme: Sora for headings, Manrope for body text -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lucide icon library (static SVG icons via CDN, no React needed) -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="app-shell">
    <!-- ===================== SIDEBAR ===================== -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <!-- COMMENT FOR DORA: drop your clinic logo file at
                 assets/images/logo.png (recommended: square, transparent
                 background, ~64x64px). Until then this shows a tooth icon. -->
            <i data-lucide="gem" class="sidebar-brand-icon"></i>
            <div>
                <div class="sidebar-brand-name"><?= e(APP_NAME) ?></div>
                <div class="sidebar-brand-tagline">Gentle care, brilliant smiles</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <?php foreach ($menuItems as $item): ?>
                <?php if (in_array($user['role'], $item['roles'], true)): ?>
                    <a href="<?= BASE_URL . $item['href'] ?>"
                       class="sidebar-link <?= $pageTitle === $item['label'] ? 'active' : '' ?>">
                        <i data-lucide="<?= e($item['icon']) ?>"></i>
                        <span><?= e($item['label']) ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-user">
            <div class="sidebar-user-avatar"><?= e(strtoupper(substr($user['full_name'], 0, 2))) ?></div>
            <div>
                <div class="sidebar-user-name"><?= e($user['full_name']) ?></div>
                <div class="sidebar-user-role"><?= e(ucfirst($user['role'])) ?></div>
            </div>
            <a href="<?= BASE_URL ?>/auth/logout.php" title="Log out" class="sidebar-logout">
                <i data-lucide="log-out"></i>
            </a>
        </div>
    </aside>

    <!-- ===================== MAIN CONTENT ===================== -->
    <main class="main-content">
        <?php $flash = flash_get(); ?>
        <?php if ($flash): ?>
            <div class="flash flash-<?= e($flash['type']) ?> rise-in">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>