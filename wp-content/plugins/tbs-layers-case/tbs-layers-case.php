<?php
/**
 * Plugin Name: TBS · Caso LAYERS
 * Description: Integra el caso LAYERS en la plantilla compartida del CPT Work.
 * Version: 1.0.3
 * Author: The Butchery Studio
 * Requires PHP: 7.4
 */
defined('ABSPATH') || exit;

add_filter('template_include', static function ($template) {
    $post = get_queried_object();
    if (!is_singular('work') || !($post instanceof WP_Post) || $post->post_name !== 'layers') {
        return $template;
    }
    if (post_password_required($post) || ($post->post_status !== 'publish' && !current_user_can('read_post', $post->ID))) return $template;
    if (basename($template) !== 'single-work.php' || !function_exists('tbs_case_embedded_body') || !function_exists('tbs_case_navigation')) {
        return $template;
    }
    // Keep the initial draft intact until its editable content has been migrated.
    if (strpos($post->post_content, 'id="layers-case"') !== false) {
        return $template;
    }
    $GLOBALS['tbs_layers_shared_template'] = $template;
    $GLOBALS['tbs_layers_render_id'] = (int) $post->ID;
    return __DIR__ . '/preview.php';
}, PHP_INT_MAX);
