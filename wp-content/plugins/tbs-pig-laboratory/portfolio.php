<?php
defined('ABSPATH') || exit;

// Reuse the site's Work content type; retain the content if ACF is disabled.
add_action('init', function () {
    if (!post_type_exists('work')) {
        register_post_type('work', array('label' => 'Work', 'public' => true,
            'show_in_rest' => true, 'supports' => array('title', 'editor', 'thumbnail')));
    }
}, 30);

add_action('acf/init', function () {
    acf_add_local_field_group(array(
        'key' => 'group_tbs_portfolio', 'title' => 'TBS — Tarjeta del portfolio',
        'location' => array(array(array('param' => 'post_type', 'operator' => '==', 'value' => 'work'))),
        'fields' => array(
            array('key' => 'field_tbs_show', 'name' => 'tbs_show', 'label' => 'Mostrar en el portfolio',
                'type' => 'true_false', 'ui' => 1, 'default_value' => 0),
            array('key' => 'field_tbs_order', 'name' => 'tbs_order', 'label' => 'Orden',
                'type' => 'number', 'default_value' => 10, 'min' => 0, 'step' => 1),
            array('key' => 'field_tbs_category', 'name' => 'tbs_category', 'label' => 'Especialidades',
                'type' => 'text'),
            array('key' => 'field_tbs_summary', 'name' => 'tbs_summary', 'label' => 'Descripción breve',
                'type' => 'textarea', 'rows' => 4),
            array('key' => 'field_tbs_image', 'name' => 'tbs_image', 'label' => 'Imagen / portada del vídeo',
                'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'library' => 'all'),
            array('key' => 'field_tbs_video', 'name' => 'tbs_video', 'label' => 'Vídeo del proyecto',
                'type' => 'file', 'return_format' => 'id', 'library' => 'all', 'mime_types' => 'mp4,webm',
                'instructions' => 'Opcional. Sustituye la imagen por un vídeo en bucle y sin sonido. La imagen se conserva como portada. Recomendado: MP4 H.264, 16:9 y comprimido para web.'),
        ),
    ));
});

// One-time migration from the approved static cards, never overwrites edits.
add_action('admin_init', function () {
    if (!current_user_can('manage_options') || get_option('tbs_portfolio_seed_v1') || !post_type_exists('work')) return;
    $rows = json_decode(file_get_contents(__DIR__ . '/portfolio-seed.json'), true);
    if (!$rows) return;
    require_once ABSPATH . 'wp-admin/includes/image.php';
    foreach ($rows as $index => $row) {
        $existing = get_posts(array('post_type' => 'work', 'post_status' => 'any',
            'name' => $row['slug'], 'numberposts' => 1));
        if ($existing) continue;
        $id = wp_insert_post(array('post_type' => 'work', 'post_status' => 'publish',
            'post_title' => $row['title'], 'post_name' => $row['slug']), true);
        if (is_wp_error($id)) return;
        $values = array('show' => 1, 'order' => $index + 1, 'category' => $row['category'],
            'summary' => $row['summary'], 'link' => '', 'video' => '');
        $file = __DIR__ . '/dist/assets/' . $row['image'];
        $upload = wp_upload_bits($row['image'], null, file_get_contents($file));
        if (empty($upload['error'])) {
            $type = wp_check_filetype($upload['file']);
            $attachment = wp_insert_attachment(array('post_mime_type' => $type['type'],
                'post_title' => $row['title'], 'post_status' => 'inherit'), $upload['file'], $id, true);
            if (!is_wp_error($attachment)) {
                wp_update_attachment_metadata($attachment, wp_generate_attachment_metadata($attachment, $upload['file']));
                update_post_meta($attachment, '_wp_attachment_image_alt', $row['alt']);
                $values['image'] = $attachment;
            }
        }
        update_post_meta($id, '_tbs_default_image', $row['image']);
        foreach ($values as $key => $value) {
            update_post_meta($id, 'tbs_' . $key, $value);
            update_post_meta($id, '_tbs_' . $key, 'field_tbs_' . $key);
        }
    }
    update_option('tbs_portfolio_seed_v1', 1, false);
    do_action('litespeed_purge_url', get_permalink((int) get_option('tbs_pig_lab_page_id')));
});

function tbs_lab_purge_portfolio_navigation_cache($id) {
    static $purged = false;
    if (is_numeric($id) && get_post_type((int) $id) === 'work') {
        if ($purged || wp_is_post_revision((int) $id) || wp_is_post_autosave((int) $id)) return;
        $purged = true;
        // Counts, order and titles appear in the shared header of every template.
        do_action('litespeed_purge_all');
    }
}
add_action('acf/save_post', 'tbs_lab_purge_portfolio_navigation_cache', 30);
add_action('save_post_work', 'tbs_lab_purge_portfolio_navigation_cache', 30);

function tbs_portfolio_markup() {
    $posts = tbs_lab_portfolio_posts();
    $cards = ''; $links = '';
    foreach ($posts as $index => $post) {
        $id = $post->ID; $slug = $post->post_name; $title = get_the_title($id);
        $image = wp_get_attachment_image_url((int) get_post_meta($id, 'tbs_image', true), 'full');
        if (!$image) $image = plugin_dir_url(__FILE__) . 'dist/assets/' . basename(get_post_meta($id, '_tbs_default_image', true) ?: 'guardian.jpg');
        $video = wp_get_attachment_url((int) get_post_meta($id, 'tbs_video', true));
        $webm = $video ? preg_replace('/\.mp4$/i', '.webm', $video) : '';
        if (!$webm || !attachment_url_to_postid($webm)) $webm = '';
        $url = get_permalink($id);
        $action = '<a class="item-link" href="' . esc_url($url) . '">VER PROYECTO</a>';
        $media = $video ? '<div class="project-image"><video autoplay muted loop playsinline preload="metadata" poster="' . esc_url($image) . '" data-tbs-autoplay aria-label="' .
            esc_attr($title) . '">' . ($webm ? '<source src="' . esc_url($webm) . '" type="video/webm">' : '') .
            '<source src="' . esc_url($video) . '" type="video/mp4"></video></div>' :
            '<a class="project-image" href="' . esc_url($url) . '" aria-label="Ver ' .
            esc_attr($title) . '"><img src="' . esc_url($image) . '" alt="' . esc_attr($title) . '" loading="lazy" width="1200" height="675"></a>';
        if ($video) {
            $native = substr($media, strlen('<div class="project-image">'), -strlen('</div>'));
            $deferred = str_replace(
                ['<video autoplay muted loop playsinline preload="metadata"', '<source src="'],
                ['<video muted loop playsinline preload="none" data-tbs-lazy', '<source data-tbs-src="'],
                $native
            );
            $media = '<div class="project-image">' . $deferred . '<noscript>' . $native . '</noscript></div>';
        }
        $cards .= '<article class="project" id="' . esc_attr($slug) . '"><div><h2>' .
            tbs_lab_portfolio_number($id) . ' — ' . esc_html($title) . '</h2><p class="category">' .
            esc_html(get_post_meta($id, 'tbs_category', true)) . '</p><p>' .
            esc_html(get_post_meta($id, 'tbs_summary', true)) . '</p>' . $action . '</div>' . $media . '</article>';
        $links .= '<a href="' . esc_url($url) . '">' .
            esc_html($title) . '<span>↗</span></a>';
    }
    return array($cards, $links);
}
