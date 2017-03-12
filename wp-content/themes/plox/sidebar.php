<?php
/**
 * Primary widget area
 *
 *
 * @package WordPress
 * @subpackage plox
 * @since plox 1.0
 */
?>
<aside class="widget-area">
	<?php if(is_active_sidebar('primary_widget_area')) : ?>
		<?php dynamic_sidebar('primary_widget_area'); ?>
	<?php endif; ?>
</aside>