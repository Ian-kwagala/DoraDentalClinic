<?php
/**
 * footer.php
 * -----------------------------------------------------------------------
 * Closes the main-content/app-shell divs opened in header.php, and loads
 * shared JS. Every page that includes header.php must include this at
 * the very end.
 */
defined('DCMS_APP') or die('Direct access not permitted.');
?>
    </main>
</div>

<script src="<?= BASE_URL ?>/assets/js/script.js"></script>
<script>
    // Renders all <i data-lucide="..."> tags into actual SVG icons.
    // Must run after the DOM (and any dynamically added icons) exists.
    lucide.createIcons();
</script>
</body>
</html>