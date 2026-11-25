<?php
// Minimal WordPress boundary: include and execute the production PHP renderer.
// A fresh process per scenario also exercises its request-local static caches.
define('ABSPATH', __DIR__ . '/');
$fixture_count = (int) $argv[2];
$fixture_context = $argv[3];
$fixture_label = isset($argv[4]) ? $argv[4] : 'PROYECTOS';

function add_action() {}
function add_filter() {}
function get_option($name, $fallback = null) { return $name === 'tbs_pig_lab_page_id' ? 10 : $fallback; }
function get_queried_object_id() { global $fixture_context; return $fixture_context === 'home' ? 10 : 20; }
function get_post_field() { return ''; }
function parse_blocks() {
    global $fixture_label;
    return array(array('blockName' => 'core/group', 'attrs' => array('anchor' => 'tbs-header'),
        'innerBlocks' => array(array('blockName' => 'core/button', 'innerHTML' => '<a href="#portfolio">' .
            htmlspecialchars($fixture_label, ENT_QUOTES, 'UTF-8') . ' [88] +</a>', 'innerBlocks' => array()))));
}
function get_bloginfo() { return 'UTF-8'; }
function wp_strip_all_tags($value) { return strip_tags($value); }
function get_posts($query) {
    global $fixture_count;
    if ($query['post_type'] !== 'work' || $query['post_status'] !== 'publish' || $query['numberposts'] !== -1) {
        throw new RuntimeException('Unexpected portfolio query');
    }
    $posts = array();
    for ($number = 1; $number <= $fixture_count; $number++) $posts[] = (object) array('ID' => 100 + $number);
    return $posts;
}
function get_permalink($id) { return 'https://example.test/work/project-' . ($id - 100) . '/'; }
function get_the_title($id) { return '<em>Project ' . ($id - 100) . '</em>'; }
function home_url($suffix = '/') { return 'https://example.test' . $suffix; }
function esc_attr($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_url($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function wp_json_encode($value) { return json_encode($value); }

require $argv[1];
echo tbs_lab_site_navigation_markup();

