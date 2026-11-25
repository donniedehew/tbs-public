<?php
defined('ABSPATH') || exit;

function tbs_lab_site_footer_markup() {
    static $rendering = false;
    if ($rendering) return '';
    $rendering = true;
    try {
        static $regions = null;
        if ($regions === null) {
            $home_id = (int) get_option('tbs_pig_lab_page_id', 0);
            $regions = $home_id ? tbs_lab_regions($home_id) : array();
        }
        $works = tbs_lab_portfolio_posts();
        $id = (int) get_queried_object_id();
        $home = home_url('/');
        $home_link = tbs_lab_navigation_context() === 'home' ? '#hero' : $home;
        ob_start();
        ?>
<footer id="footer" class="tbs-site-footer">
  <div class="footer-top">
    <nav aria-label="Proyectos">
      <?php echo isset($regions['footer-title']) ? $regions['footer-title'] : '<h2>Proyectos</h2>'; ?>
      <?php foreach ($works as $item) : ?><a href="<?php echo esc_url(get_permalink($item->ID)); ?>"<?php echo (int) $item->ID === $id ? ' aria-current="page"' : ''; ?>><?php echo esc_html(get_the_title($item->ID)); ?><span>↗</span></a><?php endforeach; ?>
    </nav>
    <div class="contact"><?php echo isset($regions['contact']) ? $regions['contact'] : ''; ?></div>
  </div>
  <a class="wordmark" href="<?php echo esc_url($home_link); ?>" aria-label="Volver al inicio"><span class="wordmark-letter">T</span><span class="wordmark-letter">B</span><span class="wordmark-lockup"><span class="wordmark-letter">S</span><sup aria-hidden="true">®</sup></span></a>
  <div class="footer-base">
    <?php echo isset($regions['copyright']) ? $regions['copyright'] : '<p>© ' . esc_html(wp_date('Y')) . '</p>'; ?>
    <div><?php echo isset($regions['legal']) ? $regions['legal'] : ''; ?></div>
  </div>
</footer>
        <?php
        return ob_get_clean();
    } finally {
        $rendering = false;
    }
}
