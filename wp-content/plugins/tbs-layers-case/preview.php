<?php
defined('ABSPATH') || exit;

// Select the shared case branch for this render only; no database changes.
$render_id = (int) ($GLOBALS['tbs_layers_render_id'] ?? 0);
$layout_filter = static function ($value, $id, $key, $single) use ($render_id) {
    if ($render_id && (int) $id === $render_id && $key === 'tbs_case_layout') {
        return $single ? 'guardian' : array('guardian');
    }
    return $value;
};
add_filter('get_post_metadata', $layout_filter, 10, 4);
ob_start();
try {
    include $GLOBALS['tbs_layers_shared_template'];
    $html = ob_get_contents();
} finally {
    ob_end_clean();
    remove_filter('get_post_metadata', $layout_filter, 10);
}
$stylesheet = '<link rel="stylesheet" id="tbs-layers-case-css" href="' . esc_url(plugins_url('layers.css', __FILE__)) . '?ver=1.0.3">';
$html = str_replace('</head>', $stylesheet . '</head>', $html);
echo str_replace('tbs-guardian-case-page', 'tbs-layers-case-page', $html);
