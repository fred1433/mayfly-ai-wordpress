<?php
/**
 * Plugin Name: Prototype notice
 * Description: Demo only. Marks this WordPress as a fictional test site and keeps it out of search engines.
 * Not part of the theme: a real install does not get this file.
 */
add_action( 'wp_body_open', function () {
	echo '<p class="kit-prototype-notice" style="margin:0;padding:.5rem 1rem;text-align:center;font:500 13px/1.4 system-ui,sans-serif;background:#26201B;color:#F3F1EC">Fictional business, invented to test the kit. Not a real company.</p>';
} );
add_filter( 'wp_robots', function ( $robots ) {
	$robots['noindex']  = true;
	$robots['nofollow'] = true;
	return $robots;
} );
