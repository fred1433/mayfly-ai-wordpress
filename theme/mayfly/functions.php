<?php
/**
 * Mayfly theme functions.
 *
 * Three things only: the axis stylesheet, a helper for links between pages,
 * and the JSON-LD schema. Everything else is theme.json, templates and patterns.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', function () {
	$ver = wp_get_theme()->get( 'Version' );
	wp_enqueue_style( 'mayfly-axis', get_theme_file_uri( 'assets/css/axis.css' ), array(), $ver );
} );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/axis.css' );
} );

add_action( 'init', function () {
	register_block_pattern_category( 'mayfly', array( 'label' => __( 'Mayfly pages', 'mayfly' ) ) );
} );

/**
 * URL of a page by its slug, so links survive any permalink setting and any install path.
 */
function mayfly_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
}

/**
 * Organisation schema. Every value is a fact from runs/mayfly-digital-2026-10-01/01-brief.md.
 */
add_action( 'wp_head', function () {
	$schema = array(
		'@context'     => 'https://schema.org',
		'@type'        => 'ProfessionalService',
		'@id'          => 'https://mayflydigital.ie/#organization',
		'name'         => 'Mayfly Digital',
		'legalName'    => 'Mayfly Marketing Ltd',
		'url'          => 'https://mayflydigital.ie/',
		'email'        => 'productionteam@mayflydigital.ie',
		'foundingDate' => '2012',
		'description'  => 'Digital Marketing & AI Consulting. An online marketing agency based in Dublin.',
		'logo'         => get_theme_file_uri( 'assets/img/mayfly-digital-wordmark.png' ),
		'address'      => array(
			'@type'           => 'PostalAddress',
			'addressLocality' => 'Dublin',
			'addressCountry'  => 'IE',
		),
		'areaServed'   => array( '@type' => 'Country', 'name' => 'Ireland' ),
		'knowsAbout'   => array(
			'Integrated Digital Marketing Strategy',
			'Performance Advertising & Analytics',
			'Content & Channel Execution',
			'AI Integration & Automation',
		),
		'employee'     => array(
			'@type'    => 'Person',
			'name'     => 'Liam Dennehy',
			'jobTitle' => 'Managing Director',
			'url'      => 'https://liamdennehy.com/',
			'sameAs'   => array( 'https://www.linkedin.com/in/ldennehy/' ),
		),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
} );

/**
 * The mayfly, redrawn in line from Mayfly's published 512 px mark.
 * The tail ends at the bottom centre of the drawing (x = 251 of the original grid), where the axis takes over.
 * Replace with the master vector file when Mayfly supplies it (questions-for-mayfly.md, question 12).
 */
function mayfly_mark_svg( $class = 'mf-mark' ) {
	$paths = array(
		'M130 48 L232 96',
		'M370 46 L270 96',
		'M232 55 L271 55 L281 75 L251 96 L221 75 Z',
		'M22 118 L64 94 L240 96 L188 154 Z',
		'M480 118 L437 94 L262 96 L312 154 Z',
		'M251 96 L212 136 L236 190 L251 197 L266 190 L290 136 Z',
		'M251 96 L251 190',
		'M236 197 L245 345 L256 345 L266 197',
		'M251 345 L251 480',
	);
	$out = '<svg class="' . esc_attr( $class ) . '" viewBox="14 40 484 440" role="img" aria-label="Mayfly Digital" focusable="false">';
	foreach ( $paths as $i => $d ) {
		$out .= '<path d="' . $d . '" pathLength="1" style="--i:' . $i . '"/>';
	}
	return $out . '</svg>';
}
