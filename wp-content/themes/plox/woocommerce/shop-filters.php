<?php if(is_subcategory()): ?>

	<nav class="products-filtering">
		<p class="caption">Narrow by:</p>
		<?php if(is_active_sidebar('shop_filters_nav')) : ?>
			<?php dynamic_sidebar('shop_filters_nav'); ?>
		<?php endif; ?>
	</nav>

<?php endif; ?>
