<?php
/**
 * Apply one bounded text change to a live page.
 *
 *   wp eval-file kit/seed/revise.php <page-slug> <section-anchor> "<current text>" "<new text>"
 *
 * The agent turns a change request into these four arguments. The script:
 * - reads the page as it is NOW in the database, so edits people made in WordPress are the starting point,
 *   never the seed files;
 * - looks for the current text only in the visible text of the blocks inside the section with that HTML anchor:
 *   never inside tags, link addresses or block attributes;
 * - requires exactly one occurrence. Zero (someone already changed it) or several (ambiguous): it refuses and
 *   writes nothing, and a person decides;
 * - replaces those bytes of the stored page and nothing else (attributes, comments, spacing stay as stored),
 *   checks that the page parsed again differs only by that text, then saves with WordPress's slashing contract, so escaped
 *   characters in block attributes survive the save;
 * - checks the save: the update must succeed and the content read back must equal the content intended. Only
 *   then does it report success. WordPress keeps the previous version as a revision.
 *
 * What it does not do: lock the page. An edit saved by someone else between this script's read and its write
 * (a window of milliseconds) would be overwritten; the revision kept by WordPress is the way back.
 */

if ( ! function_exists( 'parse_blocks' ) ) {
	fwrite( STDERR, "Run inside WordPress (wp eval-file).\n" );
	exit( 1 );
}

/**
 * Text nodes of a leaf block's HTML: the pieces between tags. Returns array of [offset, text].
 */
function kit_revise_text_nodes( $html ) {
	$nodes = array();
	preg_match_all( '/<[^>]*>|[^<]+/', $html, $m, PREG_OFFSET_CAPTURE );
	foreach ( $m[0] as $piece ) {
		if ( '<' !== $piece[0][0] ) {
			$nodes[] = array( $piece[1], $piece[0] );
		}
	}
	return $nodes;
}

/**
 * Collect every occurrence of $needle in the visible text of leaf blocks inside the anchored section.
 * Each hit is a reference path to the block plus the byte offset in its innerHTML.
 */
function kit_revise_find( $blocks, $anchor, $needle, $inside, $path, &$hits, &$found_section ) {
	foreach ( $blocks as $i => $b ) {
		$here = $inside || ( isset( $b['attrs']['anchor'] ) && $b['attrs']['anchor'] === $anchor );
		if ( $here && ! $inside ) {
			$found_section = true;
		}
		$p = array_merge( $path, array( $i ) );
		if ( $here && empty( $b['innerBlocks'] ) && is_string( $b['innerHTML'] ) ) {
			foreach ( kit_revise_text_nodes( $b['innerHTML'] ) as $node ) {
				$from = 0;
				while ( false !== ( $pos = strpos( $node[1], $needle, $from ) ) ) {
					$hits[] = array( $p, $node[0] + $pos );
					$from   = $pos + strlen( $needle );
				}
			}
		}
		if ( ! empty( $b['innerBlocks'] ) ) {
			kit_revise_find( $b['innerBlocks'], $anchor, $needle, $here, $p, $hits, $found_section );
		}
	}
}

function kit_revise_leaves( $blocks, $path, &$out ) {
	foreach ( $blocks as $i => $b ) {
		$p = array_merge( $path, array( $i ) );
		if ( empty( $b['innerBlocks'] ) ) {
			if ( null !== $b['blockName'] && is_string( $b['innerHTML'] ) && '' !== $b['innerHTML'] ) {
				$out[] = array( $p, $b['innerHTML'] );
			}
		} else {
			kit_revise_leaves( $b['innerBlocks'], $p, $out );
		}
	}
}

function kit_revise( $slug, $anchor, $old, $new ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( ! $page ) {
		return array( false, "refused: no page with slug $slug. Nothing written." );
	}
	if ( '' === $old ) {
		return array( false, 'refused: empty current text. Nothing written.' );
	}
	// Text in block HTML is entity-encoded: search for, and write, the encoded form.
	$needle = htmlspecialchars( $old, ENT_NOQUOTES, 'UTF-8', false );
	$repl   = htmlspecialchars( $new, ENT_NOQUOTES, 'UTF-8', false );

	$blocks        = parse_blocks( $page->post_content );
	$hits          = array();
	$found_section = false;
	kit_revise_find( $blocks, $anchor, $needle, false, array(), $hits, $found_section );
	if ( ! $found_section ) {
		return array( false, "refused: no section #$anchor on $slug. Nothing written." );
	}
	if ( 1 !== count( $hits ) ) {
		$n = count( $hits );
		$why = 0 === $n ? 'the current text is not there (someone may have changed it)' : "the current text appears $n times (ambiguous)";
		return array( false, "refused: in section #$anchor, $why. Nothing written." );
	}

	// Where each leaf block's HTML sits in the stored page: leaves appear in document order, verbatim.
	$leaves = array();
	kit_revise_leaves( $blocks, array(), $leaves );
	$raw    = $page->post_content;
	$cursor = 0;
	$target = null;
	list( $path, $offset ) = $hits[0];
	foreach ( $leaves as $leaf ) {
		$at = strpos( $raw, $leaf[1], $cursor );
		if ( false === $at ) {
			return array( false, 'refused: could not locate the block in the stored page. Nothing written.' );
		}
		if ( $leaf[0] === $path ) {
			$target = $at + $offset;
			break;
		}
		$cursor = $at + strlen( $leaf[1] );
	}
	if ( null === $target || substr( $raw, $target, strlen( $needle ) ) !== $needle ) {
		return array( false, 'refused: could not locate the text in the stored page. Nothing written.' );
	}
	// Replace those bytes of the stored page and nothing else: attributes, comments and spacing stay as stored.
	$content = substr_replace( $raw, $repl, $target, strlen( $needle ) );

	// Check: parsed again, the page must equal the old page with only that block's text changed.
	$ref = &$blocks;
	foreach ( $path as $k => $i ) {
		$ref = &$ref[ $i ];
		if ( $k < count( $path ) - 1 ) {
			$ref = &$ref['innerBlocks'];
		}
	}
	$ref['innerHTML']    = substr_replace( $ref['innerHTML'], $repl, $offset, strlen( $needle ) );
	$ref['innerContent'] = array( $ref['innerHTML'] );
	unset( $ref );
	if ( serialize_blocks( $blocks ) !== serialize_blocks( parse_blocks( $content ) ) ) {
		return array( false, 'refused: the change would alter more than the requested text. Nothing written.' );
	}

	// Our own content, written by the operator: save it exactly, without the HTML filter rewriting it.
	$kses = has_filter( 'content_save_pre', 'wp_filter_post_kses' );
	if ( $kses ) {
		kses_remove_filters();
	}
	$result = wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $content ) ), true );
	if ( $kses ) {
		kses_init_filters();
	}
	if ( is_wp_error( $result ) || ! $result ) {
		$msg = is_wp_error( $result ) ? $result->get_error_message() : 'WordPress returned no post ID';
		return array( false, "failed: the save did not go through ($msg). The page is unchanged." );
	}
	clean_post_cache( $page->ID );
	if ( get_post( $page->ID )->post_content !== $content ) {
		return array( false, 'failed: the saved content is not what was intended. Check the latest revision of the page.' );
	}
	return array( true, "changed section #$anchor on $slug: \"$old\" -> \"$new\" (saved, read back, previous version kept as a revision)" );
}

if ( isset( $args ) && count( $args ) === 4 ) {
	list( $ok, $msg ) = kit_revise( $args[0], $args[1], $args[2], $args[3] );
	echo $msg . "\n";
	if ( ! $ok ) {
		exit( 1 );
	}
}
