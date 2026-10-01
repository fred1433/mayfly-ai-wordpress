<?php
/**
 * Seed a site's pages from content/<site>/ into real WordPress Pages.
 *
 * Run inside WordPress, for example:
 *   wp eval-file kit/seed/seed.php content/mayfly            (any ordinary install, with WP-CLI)
 *   or the runPHP step of blueprint.json                      (WordPress Playground)
 *
 * content/<site>/pages.json lists the pages: slug, title, file, menu_order, and "front": true for the home page.
 * Each file is core block markup. Two tokens are replaced at seed time:
 *   {{url:<slug>}}  the permalink of that page on THIS install
 *   {{theme}}       the active theme's URL (for images shipped in the theme)
 *
 * Safe to run again: a page whose slug already exists is left untouched, so editorial changes made in WordPress
 * are never overwritten by a reseed. To change an existing page on purpose, use kit/seed/revise.php.
 */

if ( ! function_exists( 'wp_insert_post' ) ) {
	fwrite( STDERR, "Run inside WordPress (wp eval-file, or Playground runPHP).\n" );
	exit( 1 );
}

function kit_seed( $dir ) {
	$dir   = rtrim( $dir, '/' );
	$pages = json_decode( file_get_contents( $dir . '/pages.json' ), true );
	if ( ! is_array( $pages ) ) {
		throw new RuntimeException( "pages.json missing or invalid in $dir" );
	}
	$report = array();
	$ids    = array();
	$new    = array();

	// Pass 1: make sure every page exists, so links between pages can be resolved.
	foreach ( $pages as $p ) {
		$existing = get_page_by_path( $p['slug'], OBJECT, 'page' );
		if ( $existing ) {
			$ids[ $p['slug'] ] = $existing->ID;
			$report[]          = "kept   {$p['slug']} (already exists, not overwritten)";
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => $p['title'],
				'post_name'   => $p['slug'],
				'menu_order'  => isset( $p['menu_order'] ) ? (int) $p['menu_order'] : 0,
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			throw new RuntimeException( $id->get_error_message() );
		}
		$ids[ $p['slug'] ] = $id;
		$new[ $p['slug'] ] = $p;
	}

	// Pass 2: write content only into the pages created by this run.
	$theme = untrailingslashit( get_stylesheet_directory_uri() );
	foreach ( $new as $slug => $p ) {
		$html = file_get_contents( $dir . '/' . $p['file'] );
		$html = str_replace( '{{theme}}', $theme, $html );
		$html = preg_replace_callback(
			'/\{\{url:([a-z0-9-]+)\}\}/',
			function ( $m ) use ( $ids ) {
				return isset( $ids[ $m[1] ] ) ? get_permalink( $ids[ $m[1] ] ) : home_url( '/' . $m[1] . '/' );
			},
			$html
		);
		if ( preg_match( '/\{\{[^}]+\}\}/', $html, $left ) ) {
			throw new RuntimeException( "Unresolved token {$left[0]} in {$p['file']}" );
		}
		wp_update_post( array( 'ID' => $ids[ $slug ], 'post_content' => $html ) );
		$report[] = "seeded {$slug} from {$p['file']}";
	}

	foreach ( $pages as $p ) {
		if ( ! empty( $p['front'] ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $ids[ $p['slug'] ] );
		}
	}
	return $report;
}

// Content directory: first CLI argument (wp eval-file), or $kit_seed_dir set by the caller (Playground).
$kit_dir = isset( $args[0] ) ? $args[0] : ( isset( $kit_seed_dir ) ? $kit_seed_dir : null );
if ( $kit_dir ) {
	foreach ( kit_seed( $kit_dir ) as $line ) {
		echo $line . "\n";
	}
}
