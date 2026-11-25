<?php
/**
 * Plugin Name: TBS — Pig Laboratory
 * Description: Laboratorio 3D, portfolio y perfil editorial para thebutchery.studio.
 * Version: 0.4.99
 * Requires PHP: 7.4
 * Author: The Butchery Studio
 */
defined('ABSPATH') || exit;
require_once __DIR__ . '/portfolio.php';
require_once __DIR__ . '/editorial.php';
require_once __DIR__ . '/work-header.php';
require_once __DIR__ . '/shared-footer.php';

// Visitors manage consent inline on /cookies/. Keep the opt-in engine active.
add_filter('cmplz_cookiebanner_settings_front_end', static function ($settings) {
    $settings['disable_cookiebanner'] = true;
    return $settings;
});

// These standalone documents provide their own layout and motion. Keep the
// normal head/footer hooks for metadata, consent, analytics and the admin bar.
function tbs_lab_use_standalone_assets() {
    // Pig and Work print their design CSS in the templates. WordPress only
    // supplies functional styles here, even if a theme adds new asset names.
    add_filter('print_styles_array', static function ($handles) {
        $allowed = array('admin-bar', 'dashicons', 'buttons', 'wp-auth-check',
            'wp-img-auto-sizes-contain', 'wp-emoji-styles', 'wp-block-template-skip-link',
            'jetpack-stats', 'rank-math');
        return array_values(array_filter($handles, static function ($handle) use ($allowed) {
            return in_array($handle, $allowed, true) || strpos($handle, 'cmplz-') === 0;
        }));
    }, PHP_INT_MAX);
    remove_action('wp_head', 'wp_custom_css_cb', 101);
    remove_action('wp_head', 'wp_print_font_faces', 50);
    remove_action('wp_head', 'locale_stylesheet');
    $remove_theme_motion = static function () {
        foreach (array('tbs-motion', 'tbs-splittext', 'tbs-scrolltrigger', 'tbs-gsap', 'tbs-lenis') as $handle) {
            wp_dequeue_script($handle);
        }
    };
    add_action('wp_enqueue_scripts', $remove_theme_motion, PHP_INT_MAX);
    add_action('wp_print_footer_scripts', $remove_theme_motion, 0);
}

register_activation_hook(__FILE__, function () {
    $id = (int) get_option('tbs_pig_lab_page_id', 0);
    if ($id && get_post($id)) {
        return;
    }
    $id = wp_insert_post(array(
        'post_type' => 'page',
        'post_title' => 'TBS — Laboratorio MVP',
        'post_name' => 'tbs-laboratorio',
        'post_status' => 'draft',
        'post_content' => '<!-- wp:paragraph --><p>Laboratorio visual TBS. Esta página utiliza el diseño incluido en el plugin TBS — Pig Laboratory. La escena y los textos se mantienen en sus archivos fuente.</p><!-- /wp:paragraph -->',
        'meta_input' => array('_tbs_pig_lab' => '1'),
    ), true);
    if (!is_wp_error($id)) {
        update_option('tbs_pig_lab_page_id', (int) $id, false);
    }
});

add_filter('template_include', function ($template) {
    if (is_singular('work') && !post_password_required()) {
        return __DIR__ . '/single-work.php';
    }
    if (!is_page() || post_password_required()) {
        return $template;
    }
    if ((int) get_queried_object_id() !== (int) get_option('tbs_pig_lab_page_id', 0)) {
        return $template;
    }
    return __DIR__ . '/view.php';
}, 99);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $id = (int) get_option('tbs_pig_lab_page_id', 0);
    if ($id && current_user_can('edit_post', $id)) {
        array_unshift($links, '<a href="' . esc_url(get_edit_post_link($id, 'raw')) . '">Página del laboratorio</a>');
    }
    return $links;
});



add_action('wp_footer', function () {
    ?>
    <script>
    document.addEventListener('click', function (event) {
        var link = event.target && event.target.closest ? event.target.closest('a[href^="mailto:"]') : null;
        if (!link || !document.body.classList.contains('cmplz-statistics') || typeof window.gtag !== 'function') return;
        window.gtag('event', 'contact_click', { contact_method: 'email' });
    }, true);
    </script>
    <?php
}, 30);
