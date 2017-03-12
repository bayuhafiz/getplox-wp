<?php
/**
 * The main template file.
 *
 * @package WordPress
 * @subpackage plox
 * @since plox 1.0
 */
get_header();
?>
<?php $sHeadText = null; ?>
<?php if(is_archive()) { 
	if(is_day()) :
		$sHeadText = __('Daily Archives:', 'plox') . get_the_date();
	elseif(is_month()) :
		$sHeadText = __('Monthly Archives:', 'plox') . get_the_date(_x('F Y', 'monthly archives date format', 'plox'));
	elseif(is_year()) :
		$sHeadText = __('Yearly Archives:', 'plox') . get_the_date(_x('Y', 'yearly archives date format', 'plox'));
	else :
		$sHeadText = __('Blog Archives', 'plox');
	endif;
} ?>
<?php if(is_category()) { 
	$sHeadText = __("Category:", "plox").' '.single_cat_title( '', false ); 
} ?>
<?php if(is_author()) { 
	$authordata = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));
	$sHeadText = __("Author:", "plox").' '.$authordata->display_name; 
} ?>
<?php if(is_tag()) { 
	$sHeadText = __("Tag:", "plox").' '.single_tag_title( '', false ); 
} ?>
<?php if(is_search()) { 
	$sHeadText = __("Search:", "plox").' '.get_search_query(); 
} ?>

			<main class="content">
				<?php if (have_posts()) : ?>

					<?php if ($sHeadText) { ?>
						<h1><?php echo $sHeadText; ?></h1>
					<?php } ?>

					<?php get_template_part('loop', 'index'); ?>

				<?php else: ?>
					
					<h2><?php _e('Sorry, nothing found.','plox'); ?></h2>

				<?php endif;  ?>

			</main>

			<?php get_template_part('sidebar') ?>

			<?php
			$args = array(
	            'mid_size'           => 3,
	            'prev_text'          => __( 'Prev' ),
	            'next_text'          => __( 'Next' ),
	            'screen_reader_text' => __( 'Posts navigation' )
	        )
			?>
			<?php the_posts_pagination($args); ?>

<?php get_footer(); ?>