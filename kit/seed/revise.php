<?php
/**
 * Apply one bounded change to a live page, without touching anything else on it.
 *
 *   wp eval-file kit/seed/revise.php <page-slug> <section-anchor> "<current text>" "<new text>"
 *
 * The agent turns a change request into these four arguments. The script:
 * - finds the section (a block with that HTML anchor) on the page as it is NOW in the database,
 *   so edits people made in WordPress are the starting point, never the seed files;
 * - replaces the exact current text inside that section only;
 * - refuses, and writes nothing, if that text is no longer there (someone changed it: a person decides);
 * - saves through wp_update_post, so WordPress keeps a revision that can be restored.
 */

if ( ! function_exists( 'parse_blocks' ) ) {
	fwrite( STDERR, "Run inside WordPress (wp eval-file).\n" );
	exit( 1 );
}

function kit_revise_walk( &$blocks, $anchor, $old, $new, $inside, &$hits ) {
	foreach ( $blocks as &$b ) {
		$here = $inside || ( isset( $b['attrs']['anchor'] ) && $b['attrs']['anchor'] === $anchor );
		if ( $here && empty( $b['innerBlocks'] ) && strpos( (string) $b['innerHTML'], $old ) !== false ) {
			$b['innerHTML'] = str_replace( $old, $new, $b['innerHTML'] );
			foreach ( $b['innerContent'] as &$chunk ) {
				if ( is_string( $chunk ) ) {
					$chunk = str_replace( $old, $new, $chunk );
				}
			}
			unset( $chunk );
			$hits++;
		}
		if ( ! empty( $b['innerBlocks'] ) ) {
			kit_revise_walk( $b['innerBlocks'], $anchor, $old, $new, $here, $hits );
		}
	}
	unset( $b );
}

function kit_revise( $slug, $anchor, $old, $new ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( ! $page ) {
		return array( false, "no page with slug $slug" );
	}
	$blocks = parse_blocks( $page->post_content );
	$hits   = 0;
	kit_revise_walk( $blocks, $anchor, $old, $new, false, $hits );
	if ( 1 !== $hits ) {
		return array( false, "refused: found the current text $hits times in section #$anchor (expected exactly 1). Nothing written." );
	}
	wp_update_post( array( 'ID' => $page->ID, 'post_content' => serialize_blocks( $blocks ) ) );
	return array( true, "changed section #$anchor on $slug: \"$old\" -> \"$new\" (revision kept)" );
}

if ( isset( $args ) && count( $args ) === 4 ) {
	list( $ok, $msg ) = kit_revise( $args[0], $args[1], $args[2], $args[3] );
	echo $msg . "\n";
	if ( ! $ok ) {
		exit( 1 );
	}
}
