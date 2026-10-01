<?php
/**
 * Title: Home
 * Slug: mayfly/home
 * Categories: mayfly
 * Description: Front page. The mayfly drawn large, its tail becoming the axis.
 */
$services = mayfly_page_url( 'services' );
$process  = mayfly_page_url( 'process' );
$about    = mayfly_page_url( 'about' );
?>
<!-- wp:group {"className":"mf-hero","layout":{"type":"default"}} -->
<div class="wp-block-group mf-hero">
<!-- wp:html -->
<div class="mf-hero__mark"><?php echo mayfly_mark_svg(); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
<!-- /wp:html -->
<!-- wp:group {"className":"mf-row","layout":{"type":"default"}} -->
<div class="wp-block-group mf-row">
<!-- wp:heading {"level":1,"className":"mf-left"} -->
<h1 class="wp-block-heading mf-left">A digital partner to ambitious Irish brands.</h1>
<!-- /wp:heading -->
<!-- wp:group {"className":"mf-right","layout":{"type":"default"}} -->
<div class="wp-block-group mf-right">
<!-- wp:paragraph {"className":"mf-lead"} -->
<p class="mf-lead">Digital marketing and AI consulting, from Dublin since 2012.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="mailto:productionteam@mayflydigital.ie">Book a consultation</a></div>
<!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $process ); ?>">How we work</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"mf-row","layout":{"type":"default"}} -->
<div class="wp-block-group mf-row">
<!-- wp:heading {"className":"mf-left"} -->
<h2 class="wp-block-heading mf-left">The agency</h2>
<!-- /wp:heading -->
<!-- wp:group {"className":"mf-right","layout":{"type":"default"}} -->
<div class="wp-block-group mf-right">
<!-- wp:paragraph -->
<p>Mayfly Digital is an online marketing agency based in Dublin. Founded in 2012, we specialize in providing results driven integrated online marketing solutions for medium and large businesses across the country.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>We are a team of creative, self-disciplined, self-motivated professionals with a passion to provide your business with a more sophisticated data-driven approach to online marketing and advertising.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p><a href="<?php echo esc_url( $about ); ?>">About</a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"mf-row","layout":{"type":"default"}} -->
<div class="wp-block-group mf-row">
<!-- wp:heading {"className":"mf-left"} -->
<h2 class="wp-block-heading mf-left">What we do</h2>
<!-- /wp:heading -->
<!-- wp:group {"className":"mf-right","layout":{"type":"default"}} -->
<div class="wp-block-group mf-right">
<!-- wp:paragraph -->
<p>Developing an integrated, cross-channel strategy to ensure SEO, PPC, video, social and email marketing deliver measurable ROI, while embedding AI into day-to-day business operations.</p>
<!-- /wp:paragraph -->
<!-- wp:list {"className":"mf-names"} -->
<ul class="wp-block-list mf-names">
<!-- wp:list-item --><li><a href="<?php echo esc_url( $services ); ?>#strategy">Integrated Digital Marketing Strategy</a></li><!-- /wp:list-item -->
<!-- wp:list-item --><li><a href="<?php echo esc_url( $services ); ?>#performance">Performance Advertising &amp; Analytics</a></li><!-- /wp:list-item -->
<!-- wp:list-item --><li><a href="<?php echo esc_url( $services ); ?>#content">Content &amp; Channel Execution</a></li><!-- /wp:list-item -->
<!-- wp:list-item --><li><a href="<?php echo esc_url( $services ); ?>#ai">AI Integration &amp; Automation</a></li><!-- /wp:list-item -->
</ul>
<!-- /wp:list -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"mf-row","layout":{"type":"default"}} -->
<div class="wp-block-group mf-row">
<!-- wp:heading {"className":"mf-left"} -->
<h2 class="wp-block-heading mf-left">How we work</h2>
<!-- /wp:heading -->
<!-- wp:group {"className":"mf-right","layout":{"type":"default"}} -->
<div class="wp-block-group mf-right">
<!-- wp:list {"ordered":true,"className":"mf-names"} -->
<ol class="wp-block-list mf-names">
<!-- wp:list-item --><li><a href="<?php echo esc_url( $process ); ?>#assessment"><span class="mf-n">1</span>Assessment</a></li><!-- /wp:list-item -->
<!-- wp:list-item --><li><a href="<?php echo esc_url( $process ); ?>#strategy"><span class="mf-n">2</span>Strategy</a></li><!-- /wp:list-item -->
<!-- wp:list-item --><li><a href="<?php echo esc_url( $process ); ?>#delivery"><span class="mf-n">3</span>Delivery and optimisation</a></li><!-- /wp:list-item -->
</ol>
<!-- /wp:list -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"mayfly/conversation"} /-->
