<?php
defined('ABSPATH') || exit;
$id = get_queried_object_id();
$home = home_url('/');
$lidar = get_page_by_path('lidar-case-study', OBJECT, 'work');
$next = $lidar && $lidar->post_status === 'publish' ? get_permalink($lidar) : $home . '#portfolio';
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php echo esc_attr(get_bloginfo('charset')); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#f2f2f2">
<title><?php echo esc_html(get_post_meta($id, 'rank_math_title', true)); ?></title>
<meta name="description" content="<?php echo esc_attr(get_post_meta($id, 'rank_math_description', true)); ?>">
<?php if (get_site_icon_url(512)) : ?><link rel="icon" href="<?php echo esc_url(get_site_icon_url(512)); ?>"><?php endif; ?>
<link rel="stylesheet" href="<?php echo esc_url(tbs_case_stylesheet_url()); ?>">
<?php if (function_exists('tbs_lab_work_header_stylesheet_url')) : ?><link rel="stylesheet" href="<?php echo esc_url(tbs_lab_work_header_stylesheet_url()); ?>"><?php endif; ?>
</head>
<body class="tbs-case-page tbs-meandme-case-page">
<a class="tbs-case-skip" href="#case-main">Saltar al contenido</a>
<?php tbs_case_render_header(); ?>
<main id="case-main">
  <div class="tbs-case-layout">
    <?php echo tbs_case_navigation('meandme'); ?>
    <article class="tbs-case-article"><?php echo apply_filters('the_content', get_post_field('post_content', $id)); ?></article>
  </div>
</main>
<footer class="tbs-case-footer"><a href="<?php echo esc_url($next); ?>">Explorar LiDAR App <span>↗</span></a><a href="<?php echo esc_url($home); ?>">THE BUTCHERY STUDIO</a></footer>
<script src="<?php echo esc_url(tbs_case_script_url()); ?>" defer></script>
<?php if (function_exists('tbs_lab_work_header_script_url')) : ?><script src="<?php echo esc_url(tbs_lab_work_header_script_url()); ?>" defer></script><?php endif; ?>
</body>
</html>
