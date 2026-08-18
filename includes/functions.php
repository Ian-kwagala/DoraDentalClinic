<?php
/**
 * functions.php
 * -----------------------------------------------------------------------
 * Small, reusable helper functions shared by every module.
 * Nothing here talks to the database directly (except the patient code
 * generator, which needs $pdo to check uniqueness) — these are generic
 * utilities used by many pages.
 */

defined('DCMS_APP') or die('Direct access not permitted.');

/**
 * e()
 * Escapes a string for safe HTML output. Short name so it's painless to
 * use everywhere, e.g.: <h1><?= e($patient['first_name']) ?></h1>
 * Prevents XSS by converting <, >, ", ' into their HTML-safe equivalents.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * redirect()
 * Sends the browser to another page within the app and stops execution.
 * Always exits immediately after — otherwise PHP keeps running the rest
 * of the script even after sending the redirect header.
 */
function redirect(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

/**
 * flash_set() / flash_get()
 * A tiny "flash message" system: store a one-time message in the session
 * (e.g. "Patient saved successfully") that survives exactly one redirect,
 * then disappears. Used for success/error banners after form submissions.
 */
function flash_set(string $type, string $message): void
{
    // $type is usually 'success', 'error', or 'info' — used to pick a CSS class
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']); // clear it so it only shows once
        return $flash;
    }
    return null;
}

/**
 * generate_patient_code()
 * Builds a human-friendly patient ID like "DDG-1042" (DDG = Dora's Dental
 * Gem). Checks the database to make sure the number hasn't been used
 * before (in case of gaps from soft-deleted records).
 */
function generate_patient_code(PDO $pdo): string
{
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM patients");
    $count = (int) $stmt->fetch()['total'];

    do {
        $count++;
        $code = 'DDG-' . str_pad((string) (1000 + $count), 4, '0', STR_PAD_LEFT);

        // Make sure this code isn't already taken (belt-and-braces check)
        $check = $pdo->prepare("SELECT id FROM patients WHERE patient_code = ?");
        $check->execute([$code]);
    } while ($check->fetch());

    return $code;
}

/**
 * generate_invoice_number()
 * Builds an invoice number like "INV-2026-0189" — year plus a running
 * sequence, similar to the mockup's "#2026-0189" format.
 */
function generate_invoice_number(PDO $pdo): string
{
    $year = date('Y');
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM invoices WHERE YEAR(created_at) = ?");
    $stmt->execute([$year]);
    $count = (int) $stmt->fetch()['total'] + 1;

    return 'INV-' . $year . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
}

/**
 * format_ugx()
 * Formats a number as Ugandan Shillings, matching the mockup's
 * "UGX 120,000" style (no decimals — UGX isn't normally shown with cents).
 */
function format_ugx($amount): string
{
    return 'UGX ' . number_format((float) $amount, 0);
}