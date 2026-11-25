<?php
defined('ABSPATH') || exit;

function tbs_case_chapters($layout) {
    $chapters = array(
        'guardian' => array('intro' => 'Introducción', 'reto' => 'El reto', 'enfoque' => 'Enfoque', 'solucion' => 'Solución', 'sistema' => 'Sistema', 'conclusiones' => 'Conclusiones'),
        'meandme' => array('intro' => 'Introducción', 'reto' => 'El reto', 'arquitectura' => 'Arquitectura', 'experiencia' => 'La experiencia', 'implementacion' => 'Implementación', 'aprendizajes' => 'Aprendizajes'),
        'lidar' => array('intro' => 'Introducción', 'reto' => 'El reto', 'estructura' => 'Estructura', 'visualizacion' => 'Visualización', 'rutas' => 'Rutas', 'conclusiones' => 'Conclusiones'),
    );
    return isset($chapters[$layout]) ? $chapters[$layout] : array();
}

function tbs_case_navigation($layout) {
    $chapters = tbs_case_chapters($layout);
    if (!$chapters) return '';
    $links = '';
    $number = 0;
    foreach ($chapters as $anchor => $label) {
        $number++;
        $display = str_pad((string) $number, 2, '0', STR_PAD_LEFT);
        $links .= '<a href="#' . esc_attr($anchor) . '" aria-label="' . esc_attr($display . ' — ' . $label) . '">'
            . '<span class="tbs-case-nav-number" aria-hidden="true">' . esc_html($display) . '</span>'
            . '<span class="tbs-case-nav-label" aria-hidden="true"><span>' . esc_html($label) . '</span></span></a>';
    }
    return '<nav class="tbs-case-nav" aria-label="Capítulos del caso de estudio">' . $links . '</nav>';
}

function tbs_case_embedded_body($id, $layout) {
    if (!tbs_case_chapters($layout)) return '';
    // The companion template also uses this URL to invalidate cached assets.
    return '<link rel="stylesheet" href="' . esc_url(tbs_case_stylesheet_url()) . '">'
        . '<a class="tbs-case-skip" href="#case-content">Saltar al contenido del caso</a>'
        . '<div class="tbs-case-layout">' . tbs_case_navigation($layout)
        . '<article class="tbs-case-article" id="case-content">' . apply_filters('the_content', get_post_field('post_content', $id)) . '</article>'
        . '</div>';
}

// Historical content migrations are omitted from the public snapshot.

add_action('template_redirect', function () {
    if (!is_singular('work') || is_preview()) return;
    $id = get_queried_object_id();
    $routes = array('guardian-case-study' => 'guardian', 'meandme-case-study' => 'meandme', 'lidar-case-study' => 'lidar');
    $post = get_post($id);
    if (!$post || !isset($routes[$post->post_name])) return;
    $destination = get_page_by_path($routes[$post->post_name], OBJECT, 'work');
    if (!$destination || get_post_meta($destination->ID, 'tbs_case_layout', true) !== $routes[$post->post_name]) return;
    wp_safe_redirect(get_permalink($destination), 301);
    exit;
}, 1);

/** Keep case navigation in the same order as the home, header and footer. */
function tbs_case_render_portfolio_next($content) {
    if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST) || !is_singular('work') || is_feed()) return $content;
    if (!function_exists('tbs_lab_portfolio_posts') || !function_exists('tbs_case_next_project') || !class_exists('WP_HTML_Tag_Processor')) return $content;

    $projects = array_values(tbs_lab_portfolio_posts());
    $count = count($projects);
    if ($count < 2) return $content;
    $current = get_queried_object_id();
    $position = null;
    foreach ($projects as $index => $project) {
        if ((int) $project->ID === (int) $current) {
            $position = $index;
            break;
        }
    }
    if ($position === null) return $content;
    $next = $projects[($position + 1) % $count];
    $permalink = get_permalink($next->ID);
    if (!$permalink) return $content;

    // Parse the native component without rendering blocks or re-entering the_content.
    $blocks = parse_blocks(tbs_case_next_project(get_the_title($next->ID), (string) get_post_meta($next->ID, 'tbs_summary', true), '/'));
    if (($blocks[0]['blockName'] ?? '') !== 'core/group' || ($blocks[0]['innerBlocks'][0]['blockName'] ?? '') !== 'core/html') return $content;
    $link = new WP_HTML_Tag_Processor($blocks[0]['innerBlocks'][0]['innerHTML']);
    if (!$link->next_tag('A') || !$link->has_class('tbs-case-next-link')) return $content;
    $link->set_attribute('href', $permalink);
    $replacement = $link->get_updated_html();

    // Match complete anchors only; comments, scripts and styles remain byte-for-byte intact.
    $attributes = '(?:[^<>"\']+|"[^"]*"|\'[^\']*\')*';
    $protected = '<!--[\s\S]*?-->|<(?:script|style)\b' . $attributes . '>[\s\S]*?</(?:script|style)\s*>';
    $anchor_pattern = '~(?:' . $protected . ')(*SKIP)(*F)|<a\b' . $attributes . '>(?:(?!<a\b|</a\s*>)[\s\S])*</a\s*>~i';
    $replaced = false;
    $updated = preg_replace_callback($anchor_pattern, function ($match) use ($replacement, &$replaced) {
        $tag = new WP_HTML_Tag_Processor($match[0]);
        if (!$tag->next_tag('A') || !$tag->has_class('tbs-case-next-link')) return $match[0];
        $replaced = true;
        return $replacement;
    }, $content);
    if ($updated === null) return $content;
    if ($replaced) return $updated;

    // Layers' simple return link occupies the same closing slot; preserve its wrapper and siblings.
    $return_pattern = '~(?:' . $protected . ')(*SKIP)(*F)|(?<opening><div\b' . $attributes . '>)(?<space>\s*)<p\b' . $attributes . '>\s*(?<link><a\b' . $attributes . '>(?<label>[^<]*)</a\s*>)\s*</p\s*>~i';
    $updated = preg_replace_callback($return_pattern, function ($match) use ($replacement, &$replaced) {
        $group = new WP_HTML_Tag_Processor($match['opening']);
        if (!$group->next_tag('DIV') || !$group->has_class('tbs-case-closing')) return $match[0];
        $return_link = new WP_HTML_Tag_Processor($match['link']);
        if (!$return_link->next_tag('A') || $return_link->get_attribute('href') !== home_url('/#portfolio')) return $match[0];
        if (trim(html_entity_decode($match['label'], ENT_QUOTES | ENT_HTML5, 'UTF-8')) !== 'Volver a proyectos ↗') return $match[0];
        $replaced = true;
        return $match['opening'] . $match['space'] . $replacement;
    }, $content);
    if ($updated === null) return $content;
    if ($replaced) return $updated;

    // Some cases retain only local styles/scripts in the closing; insert without replacing them.
    $opening_pattern = '~(?:' . $protected . ')(*SKIP)(*F)|<div\b' . $attributes . '>~i';
    $inserted = false;
    $updated = preg_replace_callback($opening_pattern, function ($match) use ($replacement, &$inserted) {
        if ($inserted) return $match[0];
        $group = new WP_HTML_Tag_Processor($match[0]);
        if (!$group->next_tag('DIV') || !$group->has_class('tbs-case-closing')) return $match[0];
        $inserted = true;
        return $match[0] . $replacement;
    }, $content);
    return $updated === null ? $content : $updated;
}
add_filter('the_content', 'tbs_case_render_portfolio_next', 30);
