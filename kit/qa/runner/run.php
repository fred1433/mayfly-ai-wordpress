<?php
/**
 * QA only, local only: lets the test scripts call the seeder and the reviser inside a running local Playground,
 * as `wp eval-file` would on a normal install. Mounted by kit/scripts/serve.sh --qa, never shipped in a blueprint.
 */
require '/wordpress/wp-load.php';
header( 'Content-Type: text/plain; charset=utf-8' );
$in = json_decode( file_get_contents( 'php://input' ), true );
if ( 'seed' === $in['op'] ) {
	require_once '/wordpress/wp-content/kit/seed/seed.php';
	echo implode( "\n", kit_seed( $in['dir'] ) ) . "\n";
} elseif ( 'revise' === $in['op'] ) {
	require_once '/wordpress/wp-content/kit/seed/revise.php';
	list( $ok, $msg ) = kit_revise( $in['slug'], $in['anchor'], $in['old'], $in['new'] );
	echo ( $ok ? 'OK ' : 'REFUSED ' ) . $msg . "\n";
} elseif ( 'content' === $in['op'] ) {
	$p = get_page_by_path( $in['slug'], OBJECT, 'page' );
	echo $p ? $p->post_content : '';
} elseif ( 'id' === $in['op'] ) {
	$p = get_page_by_path( $in['slug'], OBJECT, 'page' );
	echo $p ? $p->ID : 0;
} elseif ( 'revisions' === $in['op'] ) {
	$p = get_page_by_path( $in['slug'], OBJECT, 'page' );
	echo count( wp_get_post_revisions( $p->ID ) ) . "\n";
}
