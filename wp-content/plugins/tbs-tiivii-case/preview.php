<?php
defined('ABSPATH') || exit;
// Select the native case branch for this template invocation only.
$render_id = (int) ($GLOBALS['tbs_tiivii_render_id'] ?? 0);
$layout_filter = static function ($value, $id, $key, $single) use ($render_id) {
    if ($render_id && (int) $id === $render_id && $key === 'tbs_case_layout') return $single ? 'guardian' : array('guardian');
    return $value;
};
add_filter('get_post_metadata', $layout_filter, 10, 4);
ob_start();
try {
    include $GLOBALS['tbs_tiivii_shared_template'];
    $html = ob_get_contents();
} finally {
    ob_end_clean();
    remove_filter('get_post_metadata', $layout_filter, 10);
}
$chapters = array('intro'=>'Introducción','reto'=>'El reto','estructura'=>'Enfoque','visualizacion'=>'Solución','rutas'=>'Interacción','conclusiones'=>'Conclusiones');
$nav = '<nav class="tbs-case-nav" aria-label="Capítulos del caso de estudio">';
$i = 0;
foreach ($chapters as $anchor => $label) {
    $num = sprintf('%02d', ++$i);
    $nav .= '<a href="#'.esc_attr($anchor).'" aria-label="'.esc_attr($num.' — '.$label).'"><span class="tbs-case-nav-number" aria-hidden="true">'.$num.'</span><span class="tbs-case-nav-label" aria-hidden="true"><span>'.esc_html($label).'</span></span></a>';
}
$html = str_replace('</head>', '<link rel="stylesheet" href="' . esc_url(plugins_url('screens.css', __FILE__)) . '?v=1.2"></head>', $html);
echo str_replace(array('tbs-guardian-case-page', tbs_case_navigation('guardian')), array('tbs-tiivii-case-page', $nav.'</nav>'), $html);

