/**
 * script.js
 * -----------------------------------------------------------------------
 * Small shared behaviors used across pages. Kept deliberately minimal —
 * each module (patients, appointments, etc.) will add its own JS file
 * later for things specific to that screen (e.g. the odontogram in
 * Phase 3, the live charts in Phase 6).
 */

document.addEventListener('DOMContentLoaded', function () {

    // Auto-dismiss flash messages (success/error banners) after 4 seconds
    // so they don't clutter the screen on a long session.
    const flash = document.querySelector('.flash');
    if (flash) {
        setTimeout(function () {
            flash.style.transition = 'opacity 0.4s ease';
            flash.style.opacity = '0';
            setTimeout(function () { flash.remove(); }, 400);
        }, 4000);
    }

    // Login page: clicking a role option visually selects it and checks
    // its radio button (the radio is what the form actually submits).
    document.querySelectorAll('.role-option').forEach(function (option) {
        option.addEventListener('click', function () {
            document.querySelectorAll('.role-option').forEach(function (el) {
                el.classList.remove('selected');
            });
            option.classList.add('selected');
            option.querySelector('input[type="radio"]').checked = true;
        });
    });

});