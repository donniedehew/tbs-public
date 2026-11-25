<?php
/**
 * Plugin Name: TBS — Case Studies
 * Description: Patrones editoriales y casos Guardian, ME AND ME y LiDAR para el CPT Work de TBS.
 * Version: 0.3.34
 * Requires PHP: 7.4
 */
defined('ABSPATH') || exit;

function tbs_case_script_url() {
    return add_query_arg('ver', (string) filemtime(__DIR__ . '/case.js'), plugins_url('case.js', __FILE__));
}

function tbs_case_render_header() {
    if (function_exists('tbs_lab_render_work_header')) {
        tbs_lab_render_work_header();
        return;
    }
    // Keep navigation usable when the companion plugin is not active.
    echo '<header class="tbs-case-header"><a class="tbs-case-brand" href="' . esc_url(home_url('/')) . '">TBS<sup>®</sup></a>'
        . '<a href="' . esc_url(home_url('/#portfolio')) . '">ITEMS ↗</a></header>';
}

function tbs_case_stylesheet_url() {
    return add_query_arg('ver', (string) filemtime(__DIR__ . '/case.css'), plugins_url('case.css', __FILE__));
}

function tbs_case_fit_device_to_capture($content, $block) {
    $attrs = isset($block['attrs']) ? $block['attrs'] : array();
    $classes = isset($attrs['className']) ? $attrs['className'] : '';
    $anchor = isset($attrs['anchor']) ? $attrs['anchor'] : '';
    if (strpos($classes, 'tbs-case-has-image') === false
        || !preg_match('/^(guardian-|lidar-|meandme-)/', $anchor)
        || $anchor === 'meandme-navigation'
        || empty($attrs['id'])
        || !class_exists('WP_HTML_Tag_Processor')) return $content;

    $metadata = wp_get_attachment_metadata((int) $attrs['id']);
    if (empty($metadata['width']) || empty($metadata['height'])) return $content;
    $width = (int) $metadata['width'];
    $height = (int) $metadata['height'];

    // Tablets use 72% of the canvas in both axes. Phone screens use 20% x 76%.
    // Derive the canvas from the capture so its screen has the native ratio.
    $canvas_width = $anchor === 'meandme-responsive' ? $width * 76 : $width;
    $canvas_height = $anchor === 'meandme-responsive' ? $height * 20 : $height;
    $html = new WP_HTML_Tag_Processor($content);
    if (!$html->next_tag('DIV')) return $content;
    $html->add_class('tbs-case-device-adaptive');
    $style = rtrim((string) $html->get_attribute('style'), '; ');
    $html->set_attribute('style', ($style ? $style . ';' : '')
        . '--tbs-capture-aspect:' . $canvas_width . '/' . $canvas_height . ';');
    if ($html->next_tag('IMG')) {
        $html->set_attribute('width', $width);
        $html->set_attribute('height', $height);
        $html->set_attribute('data-object-fit', 'contain');
    }
    return $html->get_updated_html();
}
add_filter('render_block_core/cover', 'tbs_case_fit_device_to_capture', 10, 2);

function tbs_case_block($name, $html, $attrs = array()) {
    return '<!-- wp:' . $name . ($attrs ? ' ' . wp_json_encode($attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '') . ' -->' . $html . '<!-- /wp:' . $name . ' -->';
}
function tbs_case_p($text, $class = '') {
    return tbs_case_block('paragraph', '<p' . ($class ? ' class="' . esc_attr($class) . '"' : '') . '>' . esc_html($text) . '</p>', $class ? array('className' => $class) : array());
}
function tbs_case_h($text, $level = 2) {
    return tbs_case_block('heading', '<h' . $level . '>' . esc_html($text) . '</h' . $level . '>', array('level' => $level));
}
function tbs_case_group($inner, $class, $id = '') {
    $attrs = array('className' => $class);
    if ($id) $attrs['anchor'] = $id;
    return tbs_case_block('group', '<div class="wp-block-group ' . esc_attr($class) . '"' . ($id ? ' id="' . esc_attr($id) . '"' : '') . '>' . $inner . '</div>', $attrs);
}
function tbs_case_facts($metadata) {
    $order = array('Proyecto', 'Cliente', 'Sector', 'Disciplina', 'Plataforma', 'Alcance', 'Desarrollos científicos', 'Colaboración');
    $ordered = array();
    foreach ($order as $key) {
        if (array_key_exists($key, $metadata)) $ordered[$key] = $metadata[$key];
    }
    // Keep case-specific fields after the shared fields, in their original order.
    foreach ($metadata as $key => $value) {
        if (!array_key_exists($key, $ordered)) $ordered[$key] = $value;
    }
    $inner = '';
    foreach ($ordered as $key => $value) $inner .= tbs_case_p($key . ' — ' . $value, 'tbs-case-meta');
    return tbs_case_block('group', '<div class="wp-block-group tbs-case-facts">' . $inner . '</div>', array(
        'className' => 'tbs-case-facts',
        'metadata' => array('name' => 'TBS · Ficha del caso'),
    ));
}
function tbs_case_section($number, $id, $heading, $body) {
    return tbs_case_group(tbs_case_p($number . ' / ' . strtoupper($id), 'tbs-case-eyebrow') . tbs_case_h($heading) . $body, 'tbs-case-section', $id);
}
function tbs_case_media($id, $label, $ratio = 'wide') {
    $class = 'tbs-case-media tbs-case-media-' . $ratio;
    if ($id === 'guardian-hero' || $id === 'lidar-hero' || $id === 'meandme-hero') {
        $original = get_page_by_path(substr($id, 0, -5), OBJECT, 'work');
        $video_id = $original ? (int) get_post_meta($original->ID, 'tbs_video', true) : 0;
        $video_url = $video_id ? wp_get_attachment_url($video_id) : '';
        if ($video_url) {
            $class .= ' tbs-case-hero-video';
            return tbs_case_block('cover',
                '<div id="' . esc_attr($id) . '" class="wp-block-cover ' . esc_attr($class) . '"><video class="wp-block-cover__video-background" autoplay muted loop playsinline src="' . esc_url($video_url) . '" data-object-fit="cover"></video><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"></div></div>',
                array('url' => $video_url, 'id' => $video_id, 'backgroundType' => 'video', 'dimRatio' => 0, 'isDark' => false, 'className' => $class, 'anchor' => $id));
        }
    }
    // Local attachment IDs are supplied by the integrating site, never copied from production.
    // Each value is array(attachment_id, alternative_text), keyed by the media anchor.
    $case_images = apply_filters('tbs_case_media_images', array());
    if (isset($case_images[$id]) && is_array($case_images[$id]) && count($case_images[$id]) >= 2) {
        $image_id = (int) $case_images[$id][0];
        $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
        if ($image_url) {
            $class .= ' tbs-case-has-image';
            return tbs_case_block('cover',
                '<div id="' . esc_attr($id) . '" class="wp-block-cover ' . esc_attr($class) . '"><img class="wp-block-cover__image-background wp-image-' . $image_id . '" alt="' . esc_attr($case_images[$id][1]) . '" src="' . esc_url($image_url) . '" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"></div></div>',
                array('url' => $image_url, 'id' => $image_id, 'dimRatio' => 0, 'isDark' => false, 'className' => $class, 'anchor' => $id));
        }
    }
    $inner = tbs_case_p($label . ' · Contenido pendiente', 'tbs-case-media-label');
    return tbs_case_block('cover',
        '<div id="' . esc_attr($id) . '" class="wp-block-cover ' . esc_attr($class) . '"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container">' . $inner . '</div></div>',
        array('dimRatio' => 0, 'isDark' => false, 'className' => $class, 'anchor' => $id));
}
function tbs_case_statement($text) {
    return tbs_case_group(tbs_case_p($text), 'tbs-case-statement');
}
function tbs_case_next_project($name, $description, $url) {
    $html = '<a class="tbs-case-next-link" href="' . esc_url(home_url($url)) . '">'
        . '<span class="tbs-case-next-eyebrow">SIGUIENTE PROYECTO</span>'
        . '<span class="tbs-case-next-main"><span class="tbs-case-next-title">' . esc_html($name) . '</span><span class="tbs-case-next-arrow" aria-hidden="true">↗</span></span>'
        . '<span class="tbs-case-next-description">' . esc_html($description) . '</span>'
        . '</a>';
    return tbs_case_group(tbs_case_block('html', $html), 'tbs-case-closing');
}
function tbs_case_feature($number, $title, $paras, $statement, $media, $reverse = false) {
    $text = tbs_case_p($number, 'tbs-case-eyebrow') . tbs_case_h($title, 3);
    foreach ($paras as $p) $text .= tbs_case_p($p);
    $text .= tbs_case_statement($statement);
    $copy = tbs_case_group($text, 'tbs-case-feature-copy');
    return tbs_case_group($reverse ? $media . $copy : $copy . $media, 'tbs-case-feature' . ($reverse ? ' is-reversed' : ''));
}
function tbs_case_content() {
    $c = '';
    $hero = tbs_case_p('CASE STUDY / 01', 'tbs-case-eyebrow')
        . tbs_case_p('INPROTECH GUARDIAN', 'tbs-case-product')
        . tbs_case_h('Hacer visible lo invisible.', 1)
        . tbs_case_p('Diseño UX/UI para una plataforma de ciberseguridad industrial que transforma datos técnicos, dispositivos y conexiones en una experiencia de exploración y monitorización.', 'tbs-case-lead');
    $metadata = array(
        'Proyecto' => 'InprOTech Guardian · 2021–2022',
        'Sector' => 'Ciberseguridad industrial OT',
        'Disciplina' => 'Product Design / UX/UI',
        'Tipología' => 'Plataforma B2B',
        'Cliente' => 'InprOTech (actualmente Greinfort)',
    );
    $hero .= tbs_case_facts($metadata);
    $hero .= tbs_case_media('guardian-hero', 'GUARDIAN / HERO');
    $c .= tbs_case_group($hero, 'tbs-case-hero');
    $c .= tbs_case_section('01', 'intro', 'Diseñar para entender lo complejo.',
        tbs_case_p('Guardian es una plataforma de ciberseguridad industrial que permite identificar activos conectados, visualizar las comunicaciones de una red y monitorizar eventos y amenazas.')
        . tbs_case_p('El desafío de diseño consiste en trasladar información altamente especializada a una interfaz que permita comprender el estado de una infraestructura, explorar sus componentes y acceder al detalle cuando sea necesario.')
        . tbs_case_p('Mi aportación se centró en el diseño UX/UI del producto, trabajando la organización de la información, las jerarquías visuales y la experiencia de navegación.'));
    $c .= tbs_case_section('02', 'reto', 'La complejidad no desaparece. Se organiza.',
        tbs_case_p('Las redes industriales reúnen dispositivos, protocolos, conexiones y eventos que generan información de distinta naturaleza.')
        . tbs_case_p('Para quienes supervisan estas infraestructuras, no basta con acceder a los datos. Es necesario entender las relaciones entre los activos, reconocer situaciones relevantes y profundizar en los elementos que requieren atención.')
        . tbs_case_p('El reto era conciliar tres necesidades: visibilidad global, exploración detallada y acceso claro a los eventos de seguridad.'));
    $principles = '';
    foreach (array(
        array('01', 'Visibilidad', 'Entender el estado general de la infraestructura y localizar la información principal desde una visión global.'),
        array('02', 'Contexto', 'Explorar dispositivos y comunicaciones sin perder de vista las relaciones que existen dentro de la red.'),
        array('03', 'Prioridad', 'Organizar eventos y alertas para facilitar su identificación, clasificación y consulta.'),
    ) as $item) {
        $principles .= tbs_case_group(tbs_case_p($item[0], 'tbs-case-eyebrow') . tbs_case_h($item[1], 3) . tbs_case_p($item[2]), 'tbs-case-principle');
    }
    $c .= tbs_case_section('03', 'enfoque', 'Tres necesidades. Un mismo sistema.',
        tbs_case_p('La propuesta de interfaz se articula alrededor de tres necesidades que requieren diferentes maneras de presentar y recorrer la información.')
        . tbs_case_group($principles, 'tbs-case-principles'));
    $solution = tbs_case_p('La experiencia se organiza en diferentes niveles de información. Cada área del producto responde a una tarea específica y utiliza los patrones de interfaz más adecuados para ella.');
    $solution .= tbs_case_feature('4.1 / DASHBOARD', 'Una visión global para empezar a entender.',
        array('El dashboard reúne indicadores y visualizaciones que ofrecen una primera lectura del estado de la red industrial.',
              'La jerarquía de la información facilita identificar los datos principales y reconocer las áreas que requieren una consulta más detallada.'),
        'Una visión general debe orientar, no concentrar toda la complejidad.', tbs_case_media('guardian-dashboard', 'GUARDIAN / DASHBOARD', 'standard'));
    $solution .= tbs_case_feature('4.2 / MAPA DE RED', 'De los datos a las relaciones.',
        array('La topología permite representar los dispositivos de la red y las conexiones existentes entre ellos.',
              'La exploración visual aporta una perspectiva diferente a los listados: permite comprender la estructura general, identificar elementos concretos y consultar sus relaciones dentro del sistema.'),
        'Mantener el contexto mientras se profundiza en el detalle.', tbs_case_media('guardian-network-map', 'GUARDIAN / MAPA DE RED'));
    $solution .= tbs_case_feature('4.3 / GESTIÓN DE ALERTAS', 'Identificar lo importante. Profundizar cuando importa.',
        array('La gestión de eventos de seguridad exige un modelo de navegación diferente al mapa de red.',
              'Los listados y las jerarquías permiten revisar alertas, interpretar sus niveles de severidad y acceder a información detallada.',
              'El diseño debe priorizar la legibilidad, la consistencia entre estados y la facilidad de consulta.'),
        'Distintas tareas necesitan distintas formas de organizar la información.', tbs_case_media('guardian-alerts', 'GUARDIAN / ALERTAS', 'standard'), true);
    $c .= tbs_case_section('04', 'solucion', 'Del conjunto al detalle.', $solution);
    $c .= tbs_case_section('05', 'sistema', 'Diferentes vistas. Una misma lógica.',
        tbs_case_p('Un producto complejo necesita adaptarse a distintas tareas sin perder coherencia.')
        . tbs_case_p('Dashboard, topología y alertas utilizan estructuras específicas, pero deben compartir un lenguaje reconocible: navegación, tipografía, componentes, estados y criterios de jerarquía.')
        . tbs_case_p('La consistencia permite que la experiencia resulte familiar incluso cuando cambia la naturaleza de la información.')
        . tbs_case_media('guardian-ui-details', 'GUARDIAN / DETALLES DE INTERFAZ', 'standard'));
    $c .= tbs_case_section('06', 'conclusiones', 'La claridad también es una funcionalidad.',
        tbs_case_p('Diseñar productos para entornos técnicos exige algo más que simplificar una interfaz.')
        . tbs_case_p('Significa entender cómo se relaciona la información, qué necesita consultar el usuario y cómo puede recorrer diferentes niveles de complejidad sin perder el contexto.')
        . tbs_case_p('En Guardian, el diseño de la experiencia aborda estas necesidades desde tres perspectivas complementarias: visión global, exploración de relaciones y gestión de eventos.')
        . tbs_case_statement('Diseñar sistemas complejos no consiste en ocultar su complejidad, sino en hacerla comprensible.'));
    $c .= tbs_case_next_project('LiDAR App', 'Explorar información geoespacial.', '/work/lidar/');
    return $c;
}

// Public snapshot: register patterns without creating or replacing site content.
add_action('init', function () {
    if (!function_exists('register_block_pattern')) return;
    if (function_exists('register_block_pattern_category')) register_block_pattern_category('tbs-case', array('label' => 'TBS / Case Study'));
    $facts_pattern = tbs_case_facts(array(
        'Proyecto' => 'Nombre del proyecto · Año',
        'Sector' => 'Sector del proyecto',
        'Disciplina' => 'Disciplinas del proyecto',
        'Colaboración' => 'Equipo o colaboradores',
    ));
    $patterns = array(
        'case-hero' => array('TBS / Case Hero', tbs_case_group(tbs_case_p('CASE STUDY / 01', 'tbs-case-eyebrow') . tbs_case_h('Título del caso', 1) . tbs_case_p('Entradilla', 'tbs-case-lead') . $facts_pattern . tbs_case_media('case-hero-media', 'MEDIO / HERO'), 'tbs-case-hero')),
        'case-facts' => array('TBS / Ficha del caso', $facts_pattern),
        'editorial-section' => array('TBS / Editorial Section', tbs_case_section('01', 'case-section', 'Título de sección', tbs_case_p('Texto editorial.'))),
        'three-principles' => array('TBS / Three Principles', tbs_case_group(tbs_case_group(tbs_case_p('01') . tbs_case_h('Principio', 3) . tbs_case_p('Descripción.'), 'tbs-case-principle'), 'tbs-case-principles')),
        'feature-split' => array('TBS / Feature Split', tbs_case_feature('01', 'Título de función', array('Descripción.'), 'Idea principal.', tbs_case_media('case-feature-media', 'MEDIO / FUNCIÓN', 'standard'))),
        'fullwidth-media' => array('TBS / Fullwidth Media', tbs_case_media('case-fullwidth-media', 'MEDIO / PENDIENTE')),
        'editorial-statement' => array('TBS / Editorial Statement', tbs_case_statement('Idea destacada.')),
        'case-closing' => array('TBS / Case Closing', tbs_case_group(tbs_case_p('SIGUIENTE PROYECTO', 'tbs-case-eyebrow') . tbs_case_h('Nombre del proyecto') . tbs_case_p('Descripción.'), 'tbs-case-closing')),
        'journey-steps' => array('TBS / Journey Steps', tbs_meandme_journey()),
    );
    foreach ($patterns as $slug => $data) register_block_pattern('tbs/' . $slug, array('title' => $data[0], 'categories' => array('tbs-case'), 'content' => $data[1]));
});
add_filter('template_include', function ($template) {
    $post = get_queried_object();
    if (!is_singular('work') || !($post instanceof WP_Post) || post_password_required($post)) return $template;
    if ($post->post_status !== 'publish' && !current_user_can('read_post', $post->ID)) return $template;
    if (is_singular('work') && get_post_meta(get_queried_object_id(), '_tbs_guardian_case', true) === '1') return __DIR__ . '/single-guardian.php';
    if (is_singular('work') && get_post_meta(get_queried_object_id(), '_tbs_lidar_case', true) === '1') return __DIR__ . '/single-lidar.php';
    if (is_singular('work') && get_post_meta(get_queried_object_id(), '_tbs_meandme_case', true) === '1') return __DIR__ . '/single-meandme.php';
    return $template;
}, 100);

require_once __DIR__ . '/lidar.php';
require_once __DIR__ . '/meandme.php';
require_once __DIR__ . '/work-integration.php';





