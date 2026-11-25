<?php
/**
 * Plugin Name: TBS · Caso tiivii
 * Description: Integra el caso tiivii en la plantilla compartida de proyectos del estudio.
 * Version: 1.2.0
 */
defined('ABSPATH') || exit;
add_filter('template_include', static function ($template) {
    $post = get_queried_object();
    if (!is_singular('work') || !($post instanceof WP_Post) || $post->post_name !== 'tiivii') return $template;
    if (post_password_required($post) || ($post->post_status !== 'publish' && !current_user_can('read_post', $post->ID))) return $template;
    if (basename($template) !== 'single-work.php' || !function_exists('tbs_case_embedded_body') || !function_exists('tbs_case_navigation')) return $template;
    $GLOBALS['tbs_tiivii_shared_template'] = $template;
    $GLOBALS['tbs_tiivii_render_id'] = (int) $post->ID;
    return __DIR__ . '/preview.php';
}, PHP_INT_MAX);
