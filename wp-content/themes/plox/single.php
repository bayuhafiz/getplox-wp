<?php
/**
 * The single post page template.
 *
 *
 * @package WordPress
 * @subpackage plox
 * @since plox 1.0
 */

get_header(); the_post(); ?>

	<main class="content full-width light-section">
		<div class="container">
			<h1><?php the_title(); ?></h1>
			<div class="entry">
				<?php the_content(); ?>
				<?php wp_link_pages(); ?>
			</div>
			<?php comments_template(); ?>
		</div>

	</main>

	<?php get_template_part('sidebar') ?>

<?php get_footer(); ?>
