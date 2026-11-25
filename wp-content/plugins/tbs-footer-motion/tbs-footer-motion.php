<?php
/**
 * Plugin Name: TBS — Footer Motion
 * Description: Lets device tilt move the TBS footer wordmark on touch devices.
 * Version: 1.0.2
 * Requires PHP: 7.4
 * Author: The Butchery Studio
 */
defined('ABSPATH') || exit;

function tbs_footer_motion_assets() {
    return <<<'HTML'
<style id="tbs-footer-motion-style">.wordmark-motion-toggle{display:none!important}</style>
<script id="tbs-footer-motion-script">
(() => {
      const boot = () => {
        const wordmark = document.querySelector('.wordmark');
        if (!wordmark) return;
        const letters = [...wordmark.querySelectorAll('.wordmark-letter')];
        if (letters.length < 3) return;

        const removeToggle = () => document.querySelectorAll('.wordmark-motion-toggle').forEach(button => button.remove());
        removeToggle();
        const observer = new MutationObserver(removeToggle);
        observer.observe(wordmark.parentElement || document.body, { childList: true, subtree: true });

        if (!matchMedia('(pointer: coarse)').matches || !window.DeviceOrientationEvent
            || matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        let origin = null;
        let active = false;
        const dispatch = (type, clientX = 0) => {
          const Pointer = window.PointerEvent || window.MouseEvent;
          wordmark.dispatchEvent(new Pointer(type, {
            bubbles: type === 'pointermove',
            clientX,
            pointerType: 'mouse'
          }));
        };
        const clear = () => {
          if (!active) return;
          dispatch('pointerleave');
          active = false;
        };
        const onOrientation = event => {
          if (!Number.isFinite(event.gamma)) return;
          if (origin === null) origin = event.gamma;
          const delta = event.gamma - origin;
          if (Math.abs(delta) < 2.5) {
            clear();
            return;
          }
          const centers = letters.map(letter => {
            const rect = letter.getBoundingClientRect();
            return (rect.left + rect.right) / 2;
          });
          const tilt = Math.max(-22, Math.min(22, delta));
          const center = centers[1];
          const clientX = tilt < 0
            ? center + (centers[0] - center) * (-tilt / 22)
            : center + (centers[2] - center) * (tilt / 22);
          dispatch('pointermove', clientX);
          active = true;
        };
        const listen = () => addEventListener('deviceorientation', onOrientation, { passive: true });
        const Motion = window.DeviceOrientationEvent;

        if (typeof Motion.requestPermission === 'function') {
          const request = () => {
            try {
              Promise.resolve(Motion.requestPermission()).then(permission => {
                if (permission === 'granted') listen();
              }).catch(() => {});
            } catch {}
          };
          addEventListener('pointerdown', request, { once: true, capture: true, passive: true });
        } else {
          listen();
        }
      };

      if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
      else boot();
})();
</script>
HTML;
}

// Some TBS templates print a complete document and intentionally bypass
// wp_head()/wp_footer(). Inject into their final HTML output so the same
// behavior works on the home page and custom project templates.
add_action('template_redirect', function () {
    if (is_admin() || is_feed() || is_robots() || is_trackback()) return;
    ob_start(function ($html) {
        $assets = tbs_footer_motion_assets();
        if (stripos($html, '</head>') !== false) {
            $html = preg_replace('/<\/head>/i', $assets . "\n</head>", $html, 1);
        } elseif (stripos($html, '</body>') !== false) {
            $html = preg_replace('/<\/body>/i', $assets . "\n</body>", $html, 1);
            return $html;
        } else {
            return $html;
        }
        return $html;
    });
}, 0);
