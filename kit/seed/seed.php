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
 *   {{url:<slug>}}  the permalink of that page on THIS install (the slug must be listed in pages.json)
 *   {{theme}}       the active theme's URL (for images shipped in the theme)
 *
 * What it guarantees:
 * - Everything is checked first (pages.json, every file, every token). If anything is wrong, nothing is created.
 * - A page is created as a draft marked "seed pending", filled, then published and unmarked. If a run stops in
 *   between, the next run finds the marked draft and finishes it, instead of skipping it as "already there".
 * - A published page that exists and is not marked is never touched: edits made in WordPress survive a reseed.
 *   To change an existing page on purpose, use kit/seed/revise.php.
 * - The front page is set only when this run publishes the page marked "front" (a first install, or the
 *   completion of an interrupted one). A reseed never changes a front page someone has chosen since.
 * - Every write is checked: the content read back from the database must equal what was meant to be saved.
 */

if ( ! function_exists( 'wp_insert_post' ) ) {
	fwrite( STDERR, "Run inside WordPress (wp eval-file, or Playground runPHP).\n" );
	exit( 1 );
}

const KIT_SEED_PENDING = '_kit_seed_pending';

/**
 * Read and check everything. Returns array( pages, html by slug ). Throws on the first problem, before any write.
 */
function kit_seed_load( $dir ) {
	$dir = rtrim( $dir, '/' );
	if ( ! is_readable( $dir . '/pages.json' ) ) {
		throw new RuntimeException( "pages.json not found in $dir" );
	}
	$pages = json_decode( file_get_contents( $dir . '/pages.json' ), true );
	if ( ! is_array( $pages ) || ! $pages ) {
		throw new RuntimeException( "pages.json in $dir is not a non-empty JSON list" );
	}
	$slugs = array();
	foreach ( $pages as $i => $p ) {
		foreach ( array( 'slug', 'title', 'file' ) as $k ) {
			if ( empty( $p[ $k ] ) || ! is_string( $p[ $k ] ) ) {
				throw new RuntimeException( "pages.json entry $i has no '$k'" );
			}
		}
		if ( ! preg_match( '/^[a-z0-9-]+$/', $p['slug'] ) || isset( $slugs[ $p['slug'] ] ) ) {
			throw new RuntimeException( "pages.json entry $i: slug '{$p['slug']}' is invalid or repeated" );
		}
		$slugs[ $p['slug'] ] = true;
	}
	$html = array();
	foreach ( $pages as $p ) {
		$file = $dir . '/' . $p['file'];
		if ( ! is_readable( $file ) ) {
			throw new RuntimeException( "{$p['file']} not found" );
		}
		$h = file_get_contents( $file );
		preg_match_all( '/\{\{([^}]*)\}\}/', $h, $m );
		foreach ( $m[1] as $token ) {
			if ( 'theme' === $token ) {
				continue;
			}
			if ( 0 === strpos( $token, 'url:' ) && isset( $slugs[ substr( $token, 4 ) ] ) ) {
				continue;
			}
			throw new RuntimeException( "Unresolvable token {{{$token}}} in {$p['file']}" );
		}
		$html[ $p['slug'] ] = $h;
	}
	return array( $pages, $html );
}

/**
 * Save post fields with WordPress's slashing contract, then read back and compare. Throws on any failure.
 */
function kit_seed_write( $fields, $expect_content = null ) {
	// The content comes from the repository and is written by the operator: store it exactly as written, without
	// the HTML filter WordPress applies to users who may not post unfiltered HTML (the case in WP-CLI and Playground).
	$kses = has_filter( 'content_save_pre', 'wp_filter_post_kses' );
	if ( $kses ) {
		kses_remove_filters();
	}
	$id = isset( $fields['ID'] ) ? wp_update_post( wp_slash( $fields ), true ) : wp_insert_post( wp_slash( $fields ), true );
	if ( $kses ) {
		kses_init_filters();
	}
	if ( is_wp_error( $id ) || ! $id ) {
		throw new RuntimeException( 'write failed: ' . ( is_wp_error( $id ) ? $id->get_error_message() : 'no post ID' ) );
	}
	if ( null !== $expect_content ) {
		clean_post_cache( $id );
		if ( get_post( $id )->post_content !== $expect_content ) {
			throw new RuntimeException( "write to post $id did not store the expected content" );
		}
	}
	return $id;
}

function kit_seed( $dir ) {
	list( $pages, $html ) = kit_seed_load( $dir );
	$report = array();
	$ids    = array();
	$todo   = array();

	// Pass 1: find or create every page (created ones as marked drafts), so links between pages resolve.
	foreach ( $pages as $p ) {
		$existing = get_posts(
			array( 'name' => $p['slug'], 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => 1 )
		);
		if ( $existing ) {
			$ids[ $p['slug'] ] = $existing[0]->ID;
			if ( get_post_meta( $existing[0]->ID, KIT_SEED_PENDING, true ) ) {
				$todo[ $p['slug'] ] = $p;
				$report[]           = "resume {$p['slug']} (left unfinished by an earlier run)";
			} else {
				$report[] = "kept   {$p['slug']} (already exists, not overwritten)";
			}
			continue;
		}
		$id = kit_seed_write(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'post_title'  => $p['title'],
				'post_name'   => $p['slug'],
				'menu_order'  => isset( $p['menu_order'] ) ? (int) $p['menu_order'] : 0,
				'meta_input'  => array( KIT_SEED_PENDING => 1 ),
			)
		);
		$ids[ $p['slug'] ]  = $id;
		$todo[ $p['slug'] ] = $p;
	}

	// Pass 2: fill, publish and unmark only the pages this run created or resumes.
	$theme = untrailingslashit( get_stylesheet_directory_uri() );
	foreach ( $todo as $slug => $p ) {
		$content = str_replace( '{{theme}}', $theme, $html[ $slug ] );
		$content = preg_replace_callback(
			'/\{\{url:([a-z0-9-]+)\}\}/',
			function ( $m ) use ( $ids ) {
				// Pretty permalinks: the page's final address, even while it is still a draft in this run.
				return get_option( 'permalink_structure' ) ? home_url( user_trailingslashit( get_page_uri( $ids[ $m[1] ] ) ) ) : get_permalink( $ids[ $m[1] ] );
			},
			$content
		);
		kit_seed_write(
			array( 'ID' => $ids[ $slug ], 'post_content' => $content, 'post_status' => 'publish', 'post_name' => $slug ),
			$content
		);
		delete_post_meta( $ids[ $slug ], KIT_SEED_PENDING );
		$report[] = "seeded {$slug} from {$p['file']}";

		if ( ! empty( $p['front'] ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $ids[ $slug ] );
			$report[] = "front page set to {$slug}";
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
