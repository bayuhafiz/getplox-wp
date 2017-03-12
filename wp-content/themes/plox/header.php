<?php
/**
* The Header for theme.
*
* Displays all of the <head> section and page header
*
* @package WordPress
* @subpackage plox
* @since plox 1.0
*/
?>
<!DOCTYPE html>
<!--[if IE 8]><html <?php language_attributes(); ?> class="no-js ie ie8 lte8 lt9"><![endif]-->
<!--[if IE 9]><html <?php language_attributes(); ?> class="no-js ie ie9 lte9 "><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html <?php language_attributes(); ?> class="no-js"><!--<![endif]-->
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable="no"/>
<script type="text/javascript">var switchTo5x=true;</script>
<script type="text/javascript" src="https://ws.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">stLight.options({publisher: "e2e06423-4767-4574-8643-ea50e3a48a1e", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>
	<?php $key = get_field('google_maps_key', 'options'); ?>
	<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $key; ?>"></script>

	<?php wp_head(); ?>
  
<!-- Hotjar Tracking Code for https://www.getplox.com -->
<script>
    (function(h,o,t,j,a,r){
        h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
        h._hjSettings={hjid:341308,hjsv:5};
        a=o.getElementsByTagName('head')[0];
        r=o.createElement('script');r.async=1;
        r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
        a.appendChild(r);
    })(window,document,'//static.hotjar.com/c/hotjar-','.js?sv=');
</script>
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-83600507-1', 'auto');
ga('send', 'pageview');

</script>
</head>
<body class="<?php echo custom_body_class(); ?>" >
	<div id="page">
		<header class="main-header">
			<div class="container">
				<a class="main-logo" href="<?php echo get_home_url(); ?>">
					<img src="<?php echo get_bloginfo('template_directory'); ?>/images/main-logo.png" alt="<?php bloginfo('name') ?>">
				</a>
				<nav class="main-nav">
					<?php wp_nav_menu(array("theme_location" => 'primary', 'container' => false)); ?>
				</nav>
	            <a href="<?php shop_get_cart_url(); ?>" class="cart-link">
	                <span class="items-count"><?php echo shop_get_cart_total(); ?></span>
	            </a>
				<div class="mobile-nav-wrap">
					<div class="menu-trigger">
						<span></span>
						<span></span>
						<span></span>
						<span></span>
					</div>
					<nav class="mobile-nav">
						<?php wp_nav_menu(array("theme_location" => 'primary', 'container' => false)); ?>
			            <a href="<?php shop_get_cart_url(); ?>" class="cart-link">
			                <span class="items-count"><?php echo shop_get_cart_total(); ?></span>
			            </a>
					</nav>
				</div>
			</div>
		</header>
		<section class="main-section">

