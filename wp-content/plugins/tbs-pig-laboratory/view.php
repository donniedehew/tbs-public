<?php
defined('ABSPATH') || exit;
// The standalone layout keeps its own styles, while Rank Math owns the
// homepage title, description and social preview stored on this page.
$page_id = (int) get_queried_object_id();
$regions = tbs_lab_regions($page_id);
// Keep both editorial paragraphs in the original scene, and the ID after it.
$regions['identity'] = '';
if (isset($regions['intro']) && preg_match('~<div\b[^>]*class="[^"]*\btbs-id-card\b[^"]*"[^>]*>~', $regions['intro'], $id_match, PREG_OFFSET_CAPTURE)) {
    $id_offset = $id_match[0][1];
    $regions['identity'] = substr($regions['intro'], $id_offset);
    $regions['intro'] = str_replace('#presentation .intro-copy', '#intro .intro-copy', substr($regions['intro'], 0, $id_offset));
}

$file = __DIR__ . (isset($regions['hero']) ? '/dist/index.html' : '/dist/legacy-index.html');
if (!is_readable($file)) {
    status_header(503);
    echo 'El laboratorio no está disponible temporalmente.';
    return;
}
$base = plugin_dir_url(__FILE__) . 'dist/';
$html = file_get_contents($file);
$seo_title = get_post_meta($page_id, 'rank_math_title', true);
$seo_description = get_post_meta($page_id, 'rank_math_description', true);
$seo_title = $seo_title ?: 'Diseño de producto digital · Estrategia, sistemas y experiencia de usuario';
$seo_description = $seo_description ?: 'Estrategia y diseño para transformar retos complejos en productos digitales. De la estructura del sistema a cada interacción, con atención a la claridad, la utilidad y la experiencia de uso.';
if (class_exists('RankMath\\Helper')) {
    $post = get_post($page_id);
    $seo_title = \RankMath\Helper::replace_vars($seo_title, $post);
    $seo_description = \RankMath\Helper::replace_vars($seo_description, $post);
}
$seo_title = wp_strip_all_tags($seo_title);
$seo_description = wp_strip_all_tags($seo_description);
$facebook_title = get_post_meta($page_id, 'rank_math_facebook_title', true) ?: $seo_title;
$facebook_description = get_post_meta($page_id, 'rank_math_facebook_description', true) ?: $seo_description;
$facebook_image = get_post_meta($page_id, 'rank_math_facebook_image', true) ?: get_site_icon_url(512);
$twitter_title = get_post_meta($page_id, 'rank_math_twitter_title', true) ?: $facebook_title;
$twitter_description = get_post_meta($page_id, 'rank_math_twitter_description', true) ?: $facebook_description;
$twitter_image = get_post_meta($page_id, 'rank_math_twitter_image', true) ?: $facebook_image;
if (class_exists('RankMath\\Helper')) {
    foreach (array('facebook_title', 'facebook_description', 'twitter_title', 'twitter_description') as $field) {
        $$field = \RankMath\Helper::replace_vars($$field, $post);
    }
}
$seo_head = $facebook_image ? '<meta property="og:image" content="' . esc_url($facebook_image) . '">' . "\n" : '';
$html = str_replace('<!-- TBS:seo-head -->', $seo_head, $html);
$html = preg_replace('~<meta name="viewport"[^>]*>~i', '', $html);
$html = preg_replace('~<details id="lab">.*?</details>~s', '', $html);
if (isset($regions['hero']) || get_option('tbs_portfolio_seed_v1')) {
    list($cards, $links) = tbs_portfolio_markup();
    if (isset($regions['hero'])) {
        $html = str_replace('<!-- TBS:work-cards -->', $cards, $html);
        $html = str_replace('<!-- TBS:work-links -->', $links, $html);
    } else {
        $html = preg_replace_callback('~(<section id="portfolio"[^>]*>).*?(</section>)~s',
            function ($m) use ($cards) { return $m[1] . $cards . $m[2]; }, $html);
        $html = preg_replace_callback('~(<nav aria-label="Proyectos"><h2>Proyectos</h2>).*?(</nav>)~s',
            function ($m) use ($links) { return $m[1] . $links . $m[2]; }, $html);
    }
}
if (isset($regions['hero'])) {
    foreach (array('header', 'hero', 'nav', 'intro', 'identity', 'marquee', 'services',
        'footer-title', 'profile', 'contact', 'copyright', 'legal') as $region) {
        $html = str_replace('<!-- TBS:' . $region . ' -->', isset($regions[$region]) ? $regions[$region] : '', $html);
    }
}
$html = str_replace(
    array('<head>', 'href="style.css"', 'src="items-menu.js?v=0.4.36"', '<script src="app.js" defer></script>', 'src="assets/'),
    array(
        '<head><noscript><style>#portfolio video[data-tbs-lazy]{display:none!important}</style></noscript><script>window.TBS_ASSETS=' . wp_json_encode($base . 'assets/') . ';</script>',
        'href="' . esc_url($base . 'style.css?v=0.4.97') . '"',
        'src="' . esc_url(tbs_lab_work_header_script_url()) . '"',
        '<link rel="preload" as="script" fetchpriority="low" href="' . esc_url($base . 'app.js?v=0.4.94') . '"><script>(function(){var start=function(){requestAnimationFrame(function(){setTimeout(function(){var s=document.createElement("script");s.src=' . wp_json_encode($base . 'app.js?v=0.4.94') . ';document.head.appendChild(s);},0);});};if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",start,{once:true});}else{start();}})();</script>',
        'src="' . esc_url($base . 'assets/'),
    ),
    $html
);
$html = str_replace(
    array('<!-- TBS:site-header -->', '<!-- TBS:site-corners -->', '<!-- TBS:header-enhancements -->', '<!-- TBS:navigation-styles -->', '<!-- TBS:site-footer -->'),
    array(tbs_lab_navigation_header_markup(), tbs_lab_navigation_corners_markup(), tbs_lab_header_enhancements(), '<link rel="stylesheet" href="' . esc_url(tbs_lab_work_header_stylesheet_url()) . '">', tbs_lab_site_footer_markup()),
    $html
);
tbs_lab_use_standalone_assets();
ob_start();
wp_head();
$tbs_wp_head = ob_get_clean();
ob_start();
wp_footer();
$tbs_wp_footer = ob_get_clean();
$html = str_replace('</head>', $tbs_wp_head . '</head>', $html);
$html = str_replace('</body>', $tbs_wp_footer . '</body>', $html);
echo $html;


