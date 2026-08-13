<?php
/**
 * Theme footer template (WordPress template hierarchy root file).
 *
 * Distinct from template-parts/sections/footer.php, which is the
 * visual Footer section's actual content (already rendered by
 * front-page.php before this file's get_footer() call). This file only
 * closes the HTML document and fires wp_footer() — required for every
 * script enqueued with the "load in footer" argument (header.js,
 * hero.js, faq-item.js, quote-form.js, slider.js all depend on this
 * actually firing).
 *
 * @package LVJCB
 */
?>
<?php wp_footer(); ?>
</body>
</html>
