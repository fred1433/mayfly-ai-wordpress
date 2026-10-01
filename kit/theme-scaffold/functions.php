<?php
/**
 * Kit scaffold: the same functions.php for every site the kit builds.
 *
 * What changes per site lives elsewhere:
 * - design tokens in theme.json, layout in assets/css/site.css,
 * - header, footer and signature elements in patterns/,
 * - organisation facts in schema.json (every value must come from the run's brief),
 * - site-specific helpers in inc/*.php.
 * Page content is never in the theme: it lives in WordPress Pages (see kit/seed/seed.php).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

foreach ( glob( __DIR__ . '/inc/*.php' ) as $kit_inc ) {
	require_once $kit_inc;
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'site', get_theme_file_uri( 'assets/css/site.css' ), array(), wp_get_theme()->get( 'Version' ) );
} );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/site.css' );
} );

add_action( 'init', function () {
	register_block_pattern_category( get_stylesheet(), array( 'label' => wp_get_theme()->get( 'Name' ) ) );
} );

/**
 * URL of a page by slug. Works under any permalink setting and any install path (Playground included).
 */
function kit_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
}

/**
 * JSON-LD from schema.json. "{{theme}}" is replaced by the theme's URL.
 */
add_action( 'wp_head', function () {
	$file = get_theme_file_path( 'schema.json' );
	if ( ! is_readable( $file ) ) {
		return;
	}
	$json = str_replace( '{{theme}}', untrailingslashit( get_theme_file_uri() ), file_get_contents( $file ) );
	$data = json_decode( $json, true );
	if ( is_array( $data ) ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
	}
} );
