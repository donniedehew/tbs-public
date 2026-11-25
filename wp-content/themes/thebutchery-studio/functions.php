<?php
/**
 * Funciones del tema The Butchery Studio.
 */

/**
 * Carga del sistema de movimiento.
 */
function tbs_enqueue_motion_assets() {
	$version = static function( $relative_path ) {
		$file = get_theme_file_path( $relative_path );

		return file_exists( $file )
			? (string) filemtime( $file )
			: wp_get_theme()->get( 'Version' );
	};

	wp_enqueue_style(
		'tbs-motion',
		get_theme_file_uri( '/assets/css/tbs-motion.css' ),
		array(),
		$version( '/assets/css/tbs-motion.css' )
	);

	wp_enqueue_script(
		'tbs-lenis',
		get_theme_file_uri( '/assets/js/vendor/lenis.min.js' ),
		array(),
		'1.3.26',
		true
	);

	wp_enqueue_script(
		'tbs-gsap',
		get_theme_file_uri( '/assets/js/vendor/gsap.min.js' ),
		array(),
		'3.15.0',
		true
	);

	wp_enqueue_script(
		'tbs-scrolltrigger',
		get_theme_file_uri( '/assets/js/vendor/ScrollTrigger.min.js' ),
		array( 'tbs-gsap' ),
		'3.15.0',
		true
	);

	wp_enqueue_script(
		'tbs-splittext',
		get_theme_file_uri( '/assets/js/vendor/SplitText.min.js' ),
		array( 'tbs-gsap' ),
		'3.15.0',
		true
	);

	wp_enqueue_script(
		'tbs-motion',
		get_theme_file_uri( '/assets/js/tbs-motion.js' ),
		array(
			'tbs-lenis',
			'tbs-scrolltrigger',
			'tbs-splittext',
		),
		$version( '/assets/js/tbs-motion.js' ),
		true
	);
}

add_action( 'wp_enqueue_scripts', 'tbs_enqueue_motion_assets', 20 );