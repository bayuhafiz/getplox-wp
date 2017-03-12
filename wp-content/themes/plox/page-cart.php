<?php
/**
 * Template Name: Cart Page
 * The statict page template.
 *
 *
 * @package WordPress
 * @subpackage plox
 * @since plox 1.0
 */

get_header(); the_post(); ?>

	<main class="cart-page-content light-section">

	<?php if (get_the_content()): ?>

		<div class="container">
			<div class="content">
				<?php the_content(); ?>
			</div>
		</div>

	<?php endif; ?>

	</main>

<?php get_footer(); ?>
