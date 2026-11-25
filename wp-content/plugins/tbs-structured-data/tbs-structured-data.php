<?php
/**
 * Plugin Name: TBS Structured Data
 * Description: Datos estructurados del perfil y los seis casos públicos. No modifica contenido ni ajustes guardados.
 * Version: 1.0.0
 * Requires PHP: 7.4
 * Author: The Butchery Studio
 */
defined('ABSPATH') || exit;

function tbs_sd_scope() {
    if (is_admin() || is_feed() || is_preview() || is_404() || post_password_required()) return false;
    if (wp_parse_url(home_url('/'), PHP_URL_HOST) !== 'thebutchery.studio') return false;
    if (get_post_status(get_queried_object_id()) !== 'publish') return false;
    foreach (array_keys($_GET) as $key) {
        if (strpos((string) $key, 'tbs_') === 0) return false;
    }
    return is_front_page() || (is_singular('work') && in_array(get_post_field('post_name', get_queried_object_id()), array('guardian', 'tiivii', 'meandme', 'lidar', 'benchmarks', 'layers'), true));
}

// The homepage is a professional profile, not an article attributed to admin.
add_filter('rank_math/json_ld', function ($data) {
    return tbs_sd_scope() && is_front_page() ? array() : $data;
}, 99);

function tbs_sd_graph() {
    $home = home_url('/');
    $id = get_queried_object_id();
    $url = is_front_page() ? $home : get_permalink($id);
    $title = get_post_meta($id, 'rank_math_title', true);
    if (!$title || strpos($title, '%') !== false) $title = wp_get_document_title();
    $description = get_post_meta($id, 'rank_math_description', true);
    $person = array('@type' => 'Person', '@id' => $home . '#rodrigo', 'name' => 'Rodrigo Andrés Cuñarro', 'url' => $home, 'jobTitle' => 'Product Designer · UX/UI', 'sameAs' => array('https://es.linkedin.com/in/rodrigo-andr%C3%A9s-cu%C3%B1arro-81462055'));
    $studio = array('@type' => 'Organization', '@id' => $home . '#studio', 'name' => 'The Butchery Studio', 'url' => $home, 'founder' => array('@id' => $person['@id']));
    $website = array('@type' => 'WebSite', '@id' => $home . '#website', 'url' => $home, 'name' => 'The Butchery Studio', 'publisher' => array('@id' => $studio['@id']), 'inLanguage' => 'es');
    $page = array('@type' => is_front_page() ? 'ProfilePage' : 'WebPage', '@id' => $url . '#webpage', 'url' => $url, 'name' => wp_strip_all_tags($title), 'inLanguage' => 'es', 'isPartOf' => array('@id' => $website['@id']));
    if ($description) $page['description'] = wp_strip_all_tags($description);
    $graph = array($person, $studio, $website);
    if (is_front_page()) {
        $page['mainEntity'] = array('@id' => $person['@id']);
    } else {
        $work = array('@type' => 'CreativeWork', '@id' => $url . '#case-study', 'url' => $url, 'name' => wp_strip_all_tags($title), 'creator' => array('@id' => $person['@id']), 'inLanguage' => 'es', 'mainEntityOfPage' => array('@id' => $page['@id']));
        if ($description) $work['description'] = wp_strip_all_tags($description);
        $image = get_the_post_thumbnail_url($id, 'full');
        if ($image) $work['image'] = $image;
        $page['mainEntity'] = array('@id' => $work['@id']);
        $breadcrumb = array('@type' => 'BreadcrumbList', '@id' => $url . '#breadcrumb', 'itemListElement' => array(array('@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => $home), array('@type' => 'ListItem', 'position' => 2, 'name' => wp_strip_all_tags(get_the_title($id)), 'item' => $url)));
        $page['breadcrumb'] = array('@id' => $breadcrumb['@id']);
        $graph[] = $work;
        $graph[] = $breadcrumb;
    }
    $graph[] = $page;
    return array('@context' => 'https://schema.org', '@graph' => $graph);
}

// Some case templates render their own head without calling wp_head().
// A scoped buffer covers both template paths without editing those templates.
add_action('template_redirect', function () {
    if (!tbs_sd_scope()) return;
    $json = wp_json_encode(tbs_sd_graph(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    if (!$json) return;
    ob_start(function ($html) use ($json) {
        if (stripos($html, '</head>') === false || strpos($html, 'id="tbs-structured-data"') !== false) return $html;
        $script = '<script type="application/ld+json" id="tbs-structured-data">' . $json . '</script>' . "\n";
        $offset = stripos($html, '</head>');
        return substr($html, 0, $offset) . $script . substr($html, $offset);
    });
}, 0);
