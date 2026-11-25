<?php
defined('ABSPATH') || exit;

// Share one request-local query across the header, home cards and footer.
function tbs_lab_portfolio_posts() {
    static $posts = null;
    if ($posts === null) {
        $posts = get_posts(array(
            'post_type' => 'work', 'post_status' => 'publish', 'numberposts' => -1,
            'meta_key' => 'tbs_order', 'orderby' => array('meta_value_num' => 'ASC', 'ID' => 'ASC'),
            'meta_query' => array(array('key' => 'tbs_show', 'value' => '1')),
        ));
    }
    return $posts;
}

// The visible portfolio order is also the case number, including tied order values.
function tbs_lab_portfolio_number($post_id) {
    static $numbers = null;
    if ($numbers === null) {
        $numbers = array();
        foreach (tbs_lab_portfolio_posts() as $index => $post) {
            $numbers[(int) $post->ID] = sprintf('%02d', $index + 1);
        }
    }
    return isset($numbers[(int) $post_id]) ? $numbers[(int) $post_id] : '';
}

// Native Gutenberg paragraphs and the older Meandme HTML share the same display rule.
add_filter('the_content', function ($html) {
    if (strpos($html, 'tbs-case-eyebrow') === false) return $html;
    $id = is_singular('work') ? get_queried_object_id() : get_the_ID();
    if (!$id || get_post_type($id) !== 'work') return $html;
    $number = tbs_lab_portfolio_number($id);
    if ($number === '') return $html;
    return preg_replace_callback('/<p\b[^>]*>[\s\S]*?<\/p>/i', function ($paragraph) use ($number) {
        $processor = new WP_HTML_Tag_Processor($paragraph[0]);
        if (!$processor->next_tag(array('tag_name' => 'P')) || !$processor->has_class('tbs-case-eyebrow')
            || !preg_match('/^CASE\s+STUDY\s*\/\s*(\d+)$/i', trim(wp_strip_all_tags($paragraph[0])), $label)) return $paragraph[0];
        return preg_replace_callback('/(?<!\d)' . preg_quote($label[1], '/') . '(?!\d)(?=(?:\s|<[^>]*>)*<\/p>\s*$)/i', function () use ($number) {
            return $number;
        }, $paragraph[0], 1);
    }, $html);
}, 20);

function tbs_lab_navigation_index_blocks() {
    static $blocks = null;
    if ($blocks === null) {
        $id = (int) get_option('tbs_pig_lab_page_id', 0);
        $blocks = $id ? parse_blocks((string) get_post_field('post_content', $id)) : array();
    }
    return $blocks;
}

function tbs_lab_navigation_region_block($anchor) {
    foreach (tbs_lab_navigation_index_blocks() as $block) {
        if ($block['blockName'] === 'core/group' && isset($block['attrs']['anchor']) && $block['attrs']['anchor'] === $anchor) {
            return $block;
        }
    }
    return null;
}

function tbs_lab_navigation_buttons($block) {
    $buttons = array();
    if (!$block || empty($block['innerBlocks'])) return $buttons;
    foreach ($block['innerBlocks'] as $child) {
        if ($child['blockName'] === 'core/button') $buttons[] = $child;
        else $buttons = array_merge($buttons, tbs_lab_navigation_buttons($child));
    }
    return $buttons;
}

function tbs_lab_navigation_button_settings($block, $fallback) {
    if (!$block) return $fallback;
    $html = isset($block['innerHTML']) ? $block['innerHTML'] : '';
    $charset = get_bloginfo('charset') ?: 'UTF-8';
    $label = html_entity_decode(wp_strip_all_tags($html), ENT_QUOTES, $charset);
    $label = preg_replace('/\s*\[\s*\d+\s*\]\s*/u', ' ', $label);
    $label = preg_replace('/[↗×+]/u', '', $label);
    $label = trim(preg_replace('/\s+/u', ' ', $label));
    if ($label !== '') $fallback['label'] = $label;
    if (class_exists('WP_HTML_Tag_Processor')) {
        $processor = new WP_HTML_Tag_Processor($html);
        if ($processor->next_tag(array('tag_name' => 'A'))) {
            $url = $processor->get_attribute('href');
            if (is_string($url) && trim($url) !== '') {
                $url = esc_url_raw($url);
                if ($url !== '') $fallback['url'] = $url;
            }
        }
    }
    return $fallback;
}

function tbs_lab_navigation_settings() {
    static $settings = null;
    if ($settings !== null) return $settings;
    $settings = array(
        'projects' => array('label' => 'PROYECTOS', 'url' => '#portfolio'),
        'profile' => array('label' => 'PERFIL', 'url' => '#profile'),
        'contact' => array('label' => 'CONTACTO', 'url' => '#footer'),
    );
    $header = tbs_lab_navigation_buttons(tbs_lab_navigation_region_block('tbs-header'));
    $corners = tbs_lab_navigation_buttons(tbs_lab_navigation_region_block('tbs-nav'));
    if (isset($header[0])) $settings['projects'] = tbs_lab_navigation_button_settings($header[0], $settings['projects']);
    if (isset($corners[0])) $settings['profile'] = tbs_lab_navigation_button_settings($corners[0], $settings['profile']);
    if (isset($corners[1])) $settings['contact'] = tbs_lab_navigation_button_settings($corners[1], $settings['contact']);
    return $settings;
}

function tbs_lab_navigation_context() {
    $home_id = (int) get_option('tbs_pig_lab_page_id', 0);
    return $home_id && (int) get_queried_object_id() === $home_id ? 'home' : 'inner';
}

function tbs_lab_navigation_url($url) {
    if (strpos($url, '#') === 0 && tbs_lab_navigation_context() !== 'home') return home_url('/') . $url;
    return $url;
}

function tbs_lab_work_header_stylesheet_url() {
    $file = __DIR__ . '/work-header.css';
    return add_query_arg('ver', is_readable($file) ? (string) filemtime($file) : '0.4.69', plugins_url('work-header.css', __FILE__));
}

function tbs_lab_work_header_script_url() {
    $file = __DIR__ . '/dist/items-menu.js';
    return add_query_arg('ver', is_readable($file) ? (string) filemtime($file) : '0.4.69', plugins_url('dist/items-menu.js', __FILE__));
}

function tbs_lab_navigation_glass_markup($edge) {
    $edge = $edge === 'bottom' ? 'bottom' : 'top';
    return '<div class="tbs-edge-glass tbs-edge-glass--' . $edge . '" aria-hidden="true">' .
        str_repeat('<span class="tbs-edge-glass__layer"></span>', 3) . '</div>';
}

function tbs_lab_navigation_header_markup() {
    $settings = tbs_lab_navigation_settings();
    $projects = tbs_lab_portfolio_posts();
    $items = array();
    foreach ($projects as $project) {
        $items[] = array('href' => get_permalink($project->ID), 'textContent' => wp_strip_all_tags(get_the_title($project->ID)),
            'number' => tbs_lab_portfolio_number($project->ID));
    }
    $home = home_url('/');
    $label = $settings['projects']['label'];
    ob_start();
    ?>
    <?php echo tbs_lab_navigation_glass_markup('top'); ?>
    <header class="work-header tbs-site-header" data-tbs-context="<?php echo esc_attr(tbs_lab_navigation_context()); ?>" data-tbs-home="<?php echo esc_url($home); ?>" data-tbs-project-label="<?php echo esc_attr($label); ?>" data-tbs-items="<?php echo esc_attr(wp_json_encode($items)); ?>">
      <a class="brand" href="<?php echo esc_url($home); ?>" aria-label="TBS, inicio"><span class="brand-letter">T</span><span class="brand-letter">B</span><span class="brand-letter">S</span><sup>®</sup></a>
      <div class="items-menu-host"><a class="header-link items-toggle" href="<?php echo esc_url(tbs_lab_navigation_url($settings['projects']['url'])); ?>"><?php echo esc_html($label . ' [' . sprintf('%02d', count($projects)) . ']'); ?> <span aria-hidden="true">+</span></a></div>
    </header>
    <?php
    return ob_get_clean();
}

function tbs_lab_navigation_corners_markup() {
    // These anchors navigate sections of Index; interior pages keep only the top header.
    if (tbs_lab_navigation_context() !== 'home') return '';
    $settings = tbs_lab_navigation_settings();
    $profile = tbs_lab_navigation_url($settings['profile']['url']);
    // Contact always targets the footer of the page being read.
    $contact = '#footer';
    return tbs_lab_navigation_glass_markup('bottom') .
        '<nav class="hero-bottom micro tbs-site-corners" aria-label="Navegación principal" data-tbs-context="' . esc_attr(tbs_lab_navigation_context()) . '">' .
        '<a class="tbs-corner-link" href="' . esc_url($profile) . '">' . esc_html($settings['profile']['label']) . ' ↗</a>' .
        '<a class="tbs-corner-link" href="' . esc_url($contact) . '">' . esc_html($settings['contact']['label']) . ' ↗</a></nav>';
}

function tbs_lab_site_navigation_markup() {
    return tbs_lab_navigation_header_markup() . tbs_lab_navigation_corners_markup();
}

function tbs_lab_render_work_header() {
    echo tbs_lab_site_navigation_markup();
}

function tbs_lab_header_enhancements() {
    $block = tbs_lab_navigation_region_block('tbs-header');
    $html = '';
    if ($block && !empty($block['innerBlocks'])) {
        foreach ($block['innerBlocks'] as $child) {
            if ($child['blockName'] === 'core/html') $html .= render_block($child);
        }
    }
    return $html;
}

function tbs_lab_enqueue_navigation_assets() {
    wp_enqueue_style('tbs-site-navigation');
    wp_enqueue_script('tbs-site-navigation');
}

add_action('init', function () {
    wp_register_style('tbs-site-navigation', tbs_lab_work_header_stylesheet_url(), array(), null);
    wp_register_script('tbs-site-navigation', tbs_lab_work_header_script_url(), array(), null, true);
    $editor = __DIR__ . '/dist/navigation-editor.js';
    wp_register_script('tbs-site-navigation-editor', plugins_url('dist/navigation-editor.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n'),
        is_readable($editor) ? (string) filemtime($editor) : '0.4.69', true);
    foreach (array('tbs/site-navigation' => 'tbs_lab_site_navigation_markup', 'tbs/site-footer' => 'tbs_lab_site_footer_markup') as $name => $callback) {
        register_block_type($name, array(
            'api_version' => 3,
            'title' => $name === 'tbs/site-navigation' ? 'TBS · Navegación del sitio' : 'TBS · Pie del sitio',
            'editor_script' => 'tbs-site-navigation-editor',
            'style' => 'tbs-site-navigation',
            'view_script' => 'tbs-site-navigation',
            'supports' => array('html' => false, 'multiple' => false),
            'render_callback' => $callback,
        ));
    }
});

add_action('wp_enqueue_scripts', function () {
    if (!is_page()) return;
    $template = get_page_template_slug((int) get_queried_object_id());
    if (!in_array($template, array('legal-cpt-shell', 'legal-cabecera-y-pie'), true)) return;
    $work_css = __DIR__ . '/work.css';
    wp_enqueue_style('tbs-site-work-shell', plugins_url('work.css', __FILE__), array(), is_readable($work_css) ? (string) filemtime($work_css) : '0.4.69');
    $legal_css = __DIR__ . '/legal-shell.css';
    if (is_readable($legal_css)) {
        wp_enqueue_style('tbs-site-legal-shell', plugins_url('legal-shell.css', __FILE__), array('tbs-site-work-shell'), (string) filemtime($legal_css));
    }
    tbs_lab_enqueue_navigation_assets();
}, 15);

