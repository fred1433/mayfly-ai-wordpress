<?php
/**
 * Plugin Name: Prototype notice
 * Description: Demo only. Marks this WordPress as an independent prototype and keeps it out of search engines.
 * Not part of the theme: a real install does not get this file.
 */
add_action( 'wp_body_open', function () {
	echo '<p class="kit-prototype-notice" style="margin:0;padding:.5rem 1rem;text-align:center;font:500 13px/1.4 system-ui,sans-serif;background:#17213A;color:#F5F6F3">Independent prototype from Mayfly&#8217;s public material.</p>';
} );
add_filter( 'wp_robots', function ( $robots ) {
	$robots['noindex']  = true;
	$robots['nofollow'] = true;
	return $robots;
} );
