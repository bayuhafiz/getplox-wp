<?php
/**
 * The 404 page template.
 *
 *
 * @package WordPress
 * @subpackage plox
 * @since plox 1.0
 */

get_header(); ?>

<main class="full-width light-section">
	<div class="container">
		<h1><?php _e('404 - Not found.'); ?></h1>
		<h2>
			<?php _e("We're sorry, but the page you are looking for cannot be found. What should you do at this point? Here are some options:", "plox"); ?>
		</h2>
		<ul>
			<li><?php _e("If you typed in a URL, check that it is typed in correctly.", "plox"); ?></li>
			<li><?php _e("Perhaps it was just a fluke, and if you try again by clicking refresh, it'll pop right up!", "plox"); ?></li>
			<li><?php _e("Or head back to our home page", "plox"); ?> <a href="<?php echo esc_url( home_url() ); ?>"><?php echo esc_url( home_url() ); ?></a> <?php _e("and navigate from there.", "plox"); ?> </li>
		</ul>
	</div>
</main>

<?php get_footer(); ?>
