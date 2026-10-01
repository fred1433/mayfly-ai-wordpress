<?php
/**
 * Title: Header
 * Slug: mayfly/header
 * Categories: mayfly
 * Inserter: false
 */
?>
<!-- wp:group {"className":"mf-header","layout":{"type":"flex","justifyContent":"space-between","flexWrap":"nowrap"}} -->
<div class="wp-block-group mf-header">
<!-- wp:html -->
<a class="mf-wordmark" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/mayfly-digital-wordmark.png' ) ); ?>" width="350" height="90" alt="Mayfly Digital"></a>
<!-- /wp:html -->
<!-- wp:navigation {"overlayMenu":"mobile","layout":{"type":"flex","justifyContent":"right"},"style":{"spacing":{"blockGap":"2rem"}}} -->
<!-- wp:navigation-link {"label":"Services","url":"<?php echo esc_url( kit_page_url( 'services' ) ); ?>","kind":"custom","isTopLevelLink":true} /-->
<!-- wp:navigation-link {"label":"Process","url":"<?php echo esc_url( kit_page_url( 'process' ) ); ?>","kind":"custom","isTopLevelLink":true} /-->
<!-- wp:navigation-link {"label":"About","url":"<?php echo esc_url( kit_page_url( 'about' ) ); ?>","kind":"custom","isTopLevelLink":true} /-->
<!-- wp:navigation-link {"label":"Contact","url":"<?php echo esc_url( kit_page_url( 'contact' ) ); ?>","kind":"custom","isTopLevelLink":true} /-->
<!-- /wp:navigation -->
</div>
<!-- /wp:group -->
