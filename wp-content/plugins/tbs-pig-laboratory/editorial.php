<?php
defined('ABSPATH') || exit;

// Seed the existing homepage's Rank Math fields once. From then on, the
// Gutenberg Rank Math sidebar is the only editable source for these values.
add_action('init', function () {
    if (get_option('tbs_lab_rank_math_seed_v1')) return;
    $id = (int) get_option('tbs_pig_lab_page_id', 0);
    if (!$id || !get_post($id)) return;
    $defaults = array(
        'rank_math_title' => 'Diseño de producto digital · Estrategia, sistemas y experiencia de usuario',
        'rank_math_description' => 'Estrategia y diseño para transformar retos complejos en productos digitales. De la estructura del sistema a cada interacción, con atención a la claridad, la utilidad y la experiencia de uso.',
        'rank_math_facebook_image' => get_site_icon_url(512),
        'rank_math_facebook_image_id' => (int) get_option('site_icon', 0),
    );
    foreach ($defaults as $key => $value) {
        if ($value && !get_post_meta($id, $key, true)) update_post_meta($id, $key, $value);
    }
    update_option('tbs_lab_rank_math_seed_v1', 1, false);
});

// The active block theme supplies the editor chrome. Enable its spacing and
// type controls without changing any of the theme's published style values.
add_action('after_setup_theme', function () {
    if (!function_exists('wp_theme_has_theme_json') || !wp_theme_has_theme_json()) return;
    add_filter('wp_theme_json_data_theme', function ($theme_json) {
        return $theme_json->update_with(array(
            'version' => 3,
            'settings' => array(
                'appearanceTools' => true,
                'spacing' => array('margin' => true, 'padding' => true),
                'typography' => array('customFontSize' => true, 'lineHeight' => true),
            ),
        ));
    });
});

// The published page is the sole source for editorial copy. Work remains the
// sole source for project cards, their links and their video attachments.
function tbs_lab_block($name, $html, $attrs = array()) {
    return '<!-- wp:' . $name . ($attrs ? ' ' . wp_json_encode($attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '') . ' -->' . $html . '<!-- /wp:' . $name . ' -->';
}
function tbs_lab_heading($text, $level = 2, $class = '') {
    return tbs_lab_block('heading', '<h' . $level . ' class="wp-block-heading' . ($class ? ' ' . esc_attr($class) : '') . '">' . $text . '</h' . $level . '>',
        array('level' => $level, 'className' => $class));
}
function tbs_lab_paragraph($text, $class = '') {
    return tbs_lab_block('paragraph', '<p' . ($class ? ' class="' . esc_attr($class) . '"' : '') . '>' . $text . '</p>',
        $class ? array('className' => $class) : array());
}
function tbs_lab_button($text, $url, $class = '') {
    $classes = 'wp-block-button' . ($class ? ' ' . $class : '');
    return tbs_lab_block('button', '<div class="' . esc_attr($classes) . '"><a class="wp-block-button__link wp-element-button" href="' . esc_url($url) . '">' . esc_html($text) . '</a></div>',
        array('className' => $class));
}
function tbs_lab_group($anchor, $label, $inner, $class = '') {
    $attrs = array('anchor' => $anchor, 'metadata' => array('name' => $label));
    if ($class) $attrs['className'] = $class;
    return tbs_lab_block('group', '<div id="' . esc_attr($anchor) . '" class="wp-block-group' . ($class ? ' ' . esc_attr($class) : '') . '">' . $inner . '</div>', $attrs);
}
function tbs_lab_initial_content() {
    $out = '';
    $out .= tbs_lab_group('tbs-header', '01 · Enlace superior', tbs_lab_button('ITEMS', '#portfolio', 'tbs-header-link'));
    $out .= tbs_lab_group('tbs-hero', '02 · Portada',
        tbs_lab_heading('Diseccionar y <span>sintetizar el sistema</span>,<br> para iterarlo con criterio', 1) .
        tbs_lab_paragraph('DISEÑO | PRODUCTO | UX/UI | DESARROLLO', 'discipline micro'));
    $out .= tbs_lab_group('tbs-nav', '03 · Navegación fija',
        tbs_lab_button('INPUTS ↗', '#intro', 'tbs-nav-link') . tbs_lab_button('OUTPUTS ↗', '#servicios', 'tbs-nav-link'));
    $out .= tbs_lab_group('tbs-intro', '04 · Introducción',
        tbs_lab_heading('Del sistema a la interfaz') .
        tbs_lab_paragraph('Trabajo desde la estructura: entender qué ocurre, separar las partes y encontrar una lógica antes de decidir cómo debe verse.<br>Mi práctica cruza product design, UX/UI, dirección de arte y desarrollo. El medio cambia —un SaaS industrial, una herramienta LiDAR, una plataforma OTT o un e-commerce—, pero el proceso parte siempre del mismo lugar: convertir complejidad en un sistema claro, útil y con carácter.'));
    $out .= tbs_lab_group('tbs-marquee', '05 · Rótulo de trabajos',
        tbs_lab_heading('<span>proyectos / proyectos / proyectos / proyectos /</span>', 2, 'marquee'));
    $services = '';
    foreach (array(
        array('Dar sentido', 'Producto, arquitectura de información, jerarquías y flujos. Entender el problema antes de dibujar la interfaz.'),
        array('Dar carácter', 'Dirección de arte, identidad, sistemas visuales e interacción. Construir un lenguaje reconocible sin separar forma y función.'),
        array('Hacer que funcione', 'Prototipado, especificación, colaboración con desarrollo y ejecución técnica cuando el proyecto lo requiere. Llevar la decisión de diseño hasta el producto real.'),
    ) as $i => $service) {
        $services .= tbs_lab_group('tbs-service-' . ($i + 1), 'Servicio ' . ($i + 1),
            tbs_lab_heading($service[0], 3) . tbs_lab_paragraph($service[1]), 'tbs-service');
    }
    $out .= tbs_lab_group('tbs-services', '06 · Servicios',
        tbs_lab_heading('Una práctica, distintas escalas') . tbs_lab_group('tbs-service-grid', 'Tarjetas de servicios', $services, 'services'));
    $out .= tbs_lab_group('tbs-footer-title', '07 · Título de proyectos', tbs_lab_heading('Proyectos'));
    $out .= tbs_lab_group('tbs-contact', '08 · Contacto',
        tbs_lab_paragraph('thebutchery.studio®') .
        tbs_lab_paragraph('DISEÑO | PRODUCTO | UX/UI | DESARROLLO', 'micro') .
        tbs_lab_button("LET'S TALK ↗", 'https://thebutchery.studio', 'tbs-contact-link'));
    $out .= tbs_lab_group('tbs-copyright', '09 · Copyright', tbs_lab_paragraph('© 2026'));
    $out .= tbs_lab_group('tbs-legal', '10 · Enlaces legales',
        tbs_lab_button('Privacidad', '#footer', 'tbs-legal-link') .
        tbs_lab_button('Cookies', '#footer', 'tbs-legal-link') .
        tbs_lab_button('Términos', '#footer', 'tbs-legal-link'));
    return $out;
}

add_action('admin_init', function () {
    if (!current_user_can('edit_pages') || get_option('tbs_lab_editorial_migrated_v1')) return;
    $id = (int) get_option('tbs_pig_lab_page_id', 0);
    $page = $id ? get_post($id) : null;
    if (!$page || $page->post_type !== 'page') return;
    if (strpos($page->post_content, 'tbs-hero') !== false) {
        update_option('tbs_lab_editorial_migrated_v1', $id, false);
        return;
    }
    // Never replace content that an editor has already authored for this page.
    if (trim(wp_strip_all_tags($page->post_content)) && strpos($page->post_content, 'La escena y los textos se mantienen en sus archivos fuente') === false) return;
    update_option('tbs_lab_editorial_backup_159', $page->post_content, false);
    wp_save_post_revision($id);
    $result = wp_update_post(array('ID' => $id, 'post_content' => tbs_lab_initial_content()), true);
    if (!is_wp_error($result)) update_option('tbs_lab_editorial_migrated_v1', $id, false);
}, 40);

function tbs_lab_regions($post_id) {
    $regions = array();
    foreach (parse_blocks(get_post_field('post_content', $post_id)) as $block) {
        if ($block['blockName'] !== 'core/group') continue;
        $anchor = isset($block['attrs']['anchor']) ? $block['attrs']['anchor'] : '';
        if (strpos($anchor, 'tbs-') !== 0) continue;
        $html = '';
        foreach ($block['innerBlocks'] as $child) $html .= render_block($child);
        $regions[substr($anchor, 4)] = $html;
    }
    return $regions;
}

add_action('enqueue_block_editor_assets', function () {
    $post_id = isset($GLOBALS['post']->ID) ? (int) $GLOBALS['post']->ID : 0;
    if ($post_id !== (int) get_option('tbs_pig_lab_page_id', 0)) return;
    wp_enqueue_style('tbs-lab-editor', plugin_dir_url(__FILE__) . 'editor.css', array('wp-edit-blocks'), '0.4.80');
});
