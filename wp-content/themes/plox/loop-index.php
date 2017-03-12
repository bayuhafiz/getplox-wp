<?php
/**
 * Loop for home page.
 *
 * @package WordPress
 * @subpackage plox
 * @since plox 1.0
 */
?>
<section class="posts">
	<?php while (have_posts()) : the_post(); ?>
		<article class="post">
			<header>
				<h2 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			</header>
			<div class="post-entry">
				<?php the_excerpt(); ?>    
			</div>
		</article>
	<?php endwhile; ?>
</section>