<?php
/**
 * Mayfly-specific helper: the mark as an inline SVG.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The mayfly, redrawn in line from Mayfly's published 512 px mark.
 * The tail ends at the bottom centre of the drawing (x = 251 of the original grid), where the axis takes over.
 * Replace with the master vector file when Mayfly supplies it (question 12 in runs/mayfly-digital-2026-10-01/questions-for-mayfly.md).
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
