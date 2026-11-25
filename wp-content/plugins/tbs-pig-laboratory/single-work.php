<?php
defined('ABSPATH') || exit;
$work = get_queried_object();
if (!$work instanceof WP_Post || $work->post_type !== 'work') return;
$id = $work->ID;
$home = home_url('/');
$title = get_the_title($id);
$category = get_post_meta($id, 'tbs_category', true);
$summary = get_post_meta($id, 'tbs_summary', true);
$image = wp_get_attachment_image_url((int) get_post_meta($id, 'tbs_image', true), 'full');
if (!$image) $image = plugin_dir_url(__FILE__) . 'dist/assets/' . basename(get_post_meta($id, '_tbs_default_image', true) ?: 'guardian.jpg');
$video = wp_get_attachment_url((int) get_post_meta($id, 'tbs_video', true));
$webm = $video ? preg_replace('/\.mp4$/i', '.webm', $video) : '';
if (!$webm || !attachment_url_to_postid($webm)) $webm = '';
$content = apply_filters('the_content', $work->post_content);
$case_layout = get_post_meta($id, 'tbs_case_layout', true);
$case_work = in_array($case_layout, array('guardian', 'lidar', 'meandme'), true) && function_exists('tbs_case_embedded_body');
$seo_title = get_post_meta($id, 'rank_math_title', true) ?: $title . ' · The Butchery Studio';
$seo_description = get_post_meta($id, 'rank_math_description', true) ?: $summary;
if (class_exists('RankMath\\Helper')) {
    $seo_title = \RankMath\Helper::replace_vars($seo_title, $work);
    $seo_description = \RankMath\Helper::replace_vars($seo_description, $work);
}
$social_image = get_post_meta($id, 'rank_math_facebook_image', true) ?: $image;
tbs_lab_use_standalone_assets();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php echo esc_attr(get_bloginfo('charset')); ?>">
<meta name="theme-color" content="#f2f2f2">
<link rel="stylesheet" href="<?php echo esc_url(plugin_dir_url(__FILE__) . 'work.css?v=0.4.97'); ?>">
<link rel="stylesheet" href="<?php echo esc_url(tbs_lab_work_header_stylesheet_url()); ?>">
<?php if ($case_work) : ?><link rel="stylesheet" href="<?php echo esc_url(tbs_case_stylesheet_url()); ?>"><?php endif; ?>
<?php wp_head(); ?>
</head>
<body class="tbs-work-page<?php echo $case_work ? ' tbs-case-page tbs-case-work tbs-' . esc_attr($case_layout) . '-case-page' : ''; ?>">
<canvas id="tbs-work-background" aria-hidden="true"></canvas>
<?php tbs_lab_render_work_header(); ?>
<main<?php echo $case_work ? ' id="case-main"' : ''; ?>>
  <?php if ($case_work) : ?>
  <?php echo tbs_case_embedded_body($id, $case_layout); ?>
  <?php else : ?>
  <section class="work-hero">
    <p class="eyebrow">THE BUTCHERY STUDIO <span>—</span> WORK</p>
    <h1><?php echo esc_html($title); ?></h1>
    <?php if ($category) : ?><p class="work-category"><?php echo esc_html($category); ?></p><?php endif; ?>
    <?php if ($summary) : ?><p class="work-summary"><?php echo esc_html($summary); ?></p><?php endif; ?>
  </section>
  <section class="work-media" aria-label="Vista del proyecto <?php echo esc_attr($title); ?>">
    <?php if ($video) : ?>
      <video controls autoplay muted loop playsinline preload="metadata" poster="<?php echo esc_url($image); ?>" data-tbs-autoplay aria-label="Vídeo de <?php echo esc_attr($title); ?>">
        <?php if ($webm) : ?><source src="<?php echo esc_url($webm); ?>" type="video/webm"><?php endif; ?>
        <source src="<?php echo esc_url($video); ?>" type="video/mp4">
      </video>
    <?php else : ?>
      <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" width="1200" height="675">
    <?php endif; ?>
  </section>
  <?php if (trim($work->post_content) !== '') : ?><section class="work-content"><?php echo $content; // WordPress-rendered blocks. ?></section><?php endif; ?>
  <a class="back-link" href="<?php echo esc_url($home . '#portfolio'); ?>">← TODOS LOS PROYECTOS</a>
  <?php endif; ?>
</main>
<?php echo tbs_lab_site_footer_markup(); ?>
<script src="<?php echo esc_url(tbs_lab_work_header_script_url()); ?>" defer></script>
<script src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'work.js?v=0.4.94'); ?>" defer></script>
<script src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'work-background.js?v=0.4.67'); ?>" defer></script>
<?php if ($case_work) : ?><script src="<?php echo esc_url(function_exists('tbs_case_script_url') ? tbs_case_script_url() : plugins_url('guardian-case-study/case.js') . '?v=0.3.13'); ?>" defer></script><?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
