<?php
/**
 * Fixtures for kit/seed/revise.php and kit/seed/seed.php, run inside a local WordPress (serve.sh --qa).
 * Each case builds its own throwaway pages, checks one behaviour, and deletes what it made.
 * Called by kit/scripts/test_fixtures.py. Prints JSON: [{case, result, detail}].
 */
require '/wordpress/wp-load.php';
require_once '/wordpress/wp-content/kit/seed/revise.php';
require_once '/wordpress/wp-content/kit/seed/seed.php';
header( 'Content-Type: application/json; charset=utf-8' );
kses_remove_filters(); // fixtures are written as an administrator would; the scripts under test handle their own writes

$out  = array();
$made = array();
function fx( &$out, $case, $ok, $detail ) {
	$out[] = array( 'case' => $case, 'result' => $ok ? 'Passed' : 'Failed', 'detail' => $detail );
}
function fx_page( &$made, $slug, $content, $status = 'publish' ) {
	$id     = wp_insert_post( wp_slash( array( 'post_type' => 'page', 'post_status' => $status, 'post_title' => $slug, 'post_name' => $slug, 'post_content' => $content ) ) );
	$made[] = $id;
	return $id;
}
function fx_content( $id ) {
	clean_post_cache( $id );
	return get_post( $id )->post_content;
}
function fx_section( $inner ) {
	return "<!-- wp:group {\"anchor\":\"sec\",\"className\":\"mf-row mf-row\\u002d\\u002dintro\"} -->\n<div id=\"sec\" class=\"wp-block-group mf-row mf-row--intro\">$inner</div>\n<!-- /wp:group -->";
}
function fx_par( $html, $attrs = '' ) {
	return "<!-- wp:paragraph$attrs -->\n<p>$html</p>\n<!-- /wp:paragraph -->";
}

// R1 repeated text inside one block: refused, nothing written.
$c  = fx_section( fx_par( 'Booking 10 to 14 weeks out, quotes in 10 to 14 days.' ) );
$id = fx_page( $made, 'fx-repeat', $c );
list( $ok, $msg ) = kit_revise( 'fx-repeat', 'sec', '10 to 14', '6 to 8' );
fx( $out, 'revise: repeated text is refused', ! $ok && fx_content( $id ) === $c, $msg );

// R2 inline link: only the visible text changes; the same word inside the link address is not touched.
$c  = fx_section( fx_par( 'See <a href="https://example.com/services/">our services</a> today.' ) );
$id = fx_page( $made, 'fx-link', $c );
list( $ok, $msg ) = kit_revise( 'fx-link', 'sec', 'services', 'work' );
$after = fx_content( $id );
fx( $out, 'revise: inline link, text changed, address untouched',
	$ok && $after === str_replace( 'our services</a>', 'our work</a>', $c ) && false !== strpos( $after, 'href="https://example.com/services/"' ), $msg );

// R3 escaped block attributes (-, <, ") survive byte for byte.
$attrs = ' {"placeholder":"\u003cem\u003eNote\u003c/em\u003e \u0022quoted\u0022"}';
$c     = fx_section( fx_par( 'Lead time is 10 to 14 weeks.', $attrs ) );
$id    = fx_page( $made, 'fx-escaped', $c );
$stored_before = fx_content( $id );
list( $ok, $msg ) = kit_revise( 'fx-escaped', 'sec', '10 to 14', '6 to 8' );
$after = fx_content( $id );
fx( $out, 'revise: escaped block attributes survive the save',
	$stored_before === $c && $ok && $after === str_replace( '10 to 14', '6 to 8', $c ), $msg );

// R3 control: the same save without wp_slash, as the old script did, would have corrupted those attributes.
$id2 = fx_page( $made, 'fx-escaped-control', $c );
wp_update_post( array( 'ID' => $id2, 'post_content' => str_replace( '10 to 14', '6 to 8', $c ) ) );
fx( $out, 'control: an unslashed save strips the escapes (why the slashing matters)',
	false !== strpos( fx_content( $id2 ), 'mf-rowu002du002dintro' ), 'an unslashed save stored the class as: mf-rowu002du002dintro' );

// R4 entities: text with & is found in its encoded form and written encoded.
$c  = fx_section( fx_par( 'Content &amp; Channel Execution' ) );
$id = fx_page( $made, 'fx-amp', $c );
list( $ok, $msg ) = kit_revise( 'fx-amp', 'sec', 'Content & Channel', 'Content & Social' );
fx( $out, 'revise: text with an ampersand', $ok && fx_content( $id ) === str_replace( 'Content &amp; Channel', 'Content &amp; Social', $c ), $msg );

// R5 failed write: WordPress refuses the update; the script must say so and the page must be unchanged.
$c  = fx_section( fx_par( 'Lead time is 10 to 14 weeks.' ) );
$id = fx_page( $made, 'fx-fail', $c );
add_filter( 'wp_insert_post_empty_content', '__return_true' );
list( $ok, $msg ) = kit_revise( 'fx-fail', 'sec', '10 to 14', '6 to 8' );
remove_filter( 'wp_insert_post_empty_content', '__return_true' );
fx( $out, 'revise: a failed write is reported as a failure', ! $ok && 0 === strpos( $msg, 'failed' ) && fx_content( $id ) === $c, $msg );

// R6 text outside the section, and a missing section: refused.
$c  = fx_section( fx_par( 'Inside.' ) ) . "\n\n" . fx_par( 'Outside 10 to 14.' );
$id = fx_page( $made, 'fx-scope', $c );
list( $ok, $msg )   = kit_revise( 'fx-scope', 'sec', '10 to 14', '6 to 8' );
list( $ok2, $msg2 ) = kit_revise( 'fx-scope', 'nosuch', 'Inside', 'In' );
fx( $out, 'revise: text outside the section, or no such section, is refused', ! $ok && ! $ok2 && fx_content( $id ) === $c, "$msg | $msg2" );

// Seeder fixtures use a throwaway content directory.
$dir = WP_CONTENT_DIR . '/kit-fixtures';
wp_mkdir_p( $dir );
function fx_dir( $dir, $pages, $files ) {
	foreach ( glob( $dir . '/*' ) as $f ) {
		unlink( $f );
	}
	file_put_contents( $dir . '/pages.json', wp_json_encode( $pages ) );
	foreach ( $files as $name => $html ) {
		file_put_contents( $dir . '/' . $name, $html );
	}
}
function fx_exists( $slug ) {
	return (bool) get_posts( array( 'name' => $slug, 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => 1 ) );
}

// S1 an unresolvable token in the second page: nothing at all is created.
fx_dir( $dir, array( array( 'slug' => 'fx-s1-a', 'title' => 'A', 'file' => 'a.html' ), array( 'slug' => 'fx-s1-b', 'title' => 'B', 'file' => 'b.html' ) ),
	array( 'a.html' => fx_par( 'A' ), 'b.html' => fx_par( '<a href="{{url:nowhere}}">x</a>' ) ) );
try { kit_seed( $dir ); $err = 'no error'; } catch ( Exception $e ) { $err = $e->getMessage(); }
fx( $out, 'seed: bad input stops the run before anything is created', ! fx_exists( 'fx-s1-a' ) && ! fx_exists( 'fx-s1-b' ), $err );

// S2 a missing file: nothing created.
fx_dir( $dir, array( array( 'slug' => 'fx-s2', 'title' => 'S2', 'file' => 'missing.html' ) ), array() );
try { kit_seed( $dir ); $err = 'no error'; } catch ( Exception $e ) { $err = $e->getMessage(); }
fx( $out, 'seed: a missing file stops the run before anything is created', ! fx_exists( 'fx-s2' ), $err );

// S3 an interrupted run left a marked, empty draft: the next run finishes it.
$id = fx_page( $made, 'fx-s3', '', 'draft' );
update_post_meta( $id, KIT_SEED_PENDING, 1 );
fx_dir( $dir, array( array( 'slug' => 'fx-s3', 'title' => 'S3', 'file' => 's3.html' ) ), array( 's3.html' => fx_par( 'Finished.' ) ) );
$rep = kit_seed( $dir );
$p   = get_post( $id );
fx( $out, 'seed: an interrupted creation is resumed on the next run',
	'publish' === $p->post_status && fx_content( $id ) === fx_par( 'Finished.' ) && ! get_post_meta( $id, KIT_SEED_PENDING, true ), implode( '; ', $rep ) );

// S4 an existing page and a front page chosen since: a reseed changes neither.
$front_before = array( get_option( 'show_on_front' ), get_option( 'page_on_front' ) );
$other        = fx_page( $made, 'fx-s4-other', fx_par( 'Chosen front page.' ) );
$existing     = fx_page( $made, 'fx-s4', fx_par( 'Edited in WordPress.' ) );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $other );
fx_dir( $dir, array( array( 'slug' => 'fx-s4', 'title' => 'S4', 'file' => 's4.html', 'front' => true ) ), array( 's4.html' => fx_par( 'Seed text.' ) ) );
$rep = kit_seed( $dir );
fx( $out, 'seed: a reseed keeps existing pages and the chosen front page',
	(int) get_option( 'page_on_front' ) === $other && fx_content( $existing ) === fx_par( 'Edited in WordPress.' ), implode( '; ', $rep ) );

// S5 first install of a front page: it is set.
fx_dir( $dir, array( array( 'slug' => 'fx-s5', 'title' => 'S5', 'file' => 's5.html', 'front' => true ) ), array( 's5.html' => fx_par( 'New home.' ) ) );
$rep   = kit_seed( $dir );
$s5    = get_posts( array( 'name' => 'fx-s5', 'post_type' => 'page', 'numberposts' => 1 ) );
$made[] = $s5 ? $s5[0]->ID : 0;
fx( $out, 'seed: a first install sets the front page', $s5 && (int) get_option( 'page_on_front' ) === $s5[0]->ID, implode( '; ', $rep ) );

// Clean up.
update_option( 'show_on_front', $front_before[0] );
update_option( 'page_on_front', $front_before[1] );
foreach ( $made as $m ) {
	if ( $m ) {
		wp_delete_post( $m, true );
	}
}
foreach ( glob( $dir . '/*' ) as $f ) {
	unlink( $f );
}
rmdir( $dir );
echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
