<?php
/**
 * The template for displaying Comments
 *
 * The area of the page that contains comments and the comment form.
 *
 *
 * @package WordPress
 * @subpackage plox
 * @since plox 1.0
 */
?>


<?php

class Custom_Comments_Walker extends Walker_Comment {
	var $tree_type = 'comment';
	var $db_fields = array( 'parent' => 'comment_parent', 'id' => 'comment_ID' );

	
	/**
	 *  Wrapper for comments list.
	 */

	function __construct() { ?>
		
		<section class="comments-list">

	<?php }

	
	
	/**
	 *  Wrapper for the list of child comments.
	 */

	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$GLOBALS['comment_depth'] = $depth + 2; ?>

		<section class="child-comments comments-list">

	<?php }

	
	
	/**
	 *  Closing tag for list of child comments wrapper.
	 */

	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$GLOBALS['comment_depth'] = $depth + 2; ?>

		</section>

	<?php }

	
	
	/**
	 *  Comment markup.
	 */

	function start_el( &$output, $comment, $depth=0, $args=array(), $id = 0 ) {
		$depth++;
		$GLOBALS['comment_depth'] = $depth;
		$GLOBALS['comment'] = $comment;
		$parent_class = ( empty( $args['has_children'] ) ? '' : 'parent' ); 

		$tag = 'article';
		$add_below = 'comment';

		?>

		<article <?php comment_class(empty( $args['has_children'] ) ? '' : 'parent' ) ?> id="comment-<?php comment_ID() ?>">
			<figure class="gravatar"><?php echo get_avatar( $comment, 65); ?></figure>

			<div class="comment-meta post-meta">
				<h2 class="comment-author">
					<a class="comment-author-link" href="<?php comment_author_url(); ?>"><?php comment_author(); ?></a>
				</h2>

				<time class="comment-meta-item" datetime="<?php comment_date('Y-m-d') ?>T<?php comment_time('H:iP') ?>"><?php comment_date('jS F Y') ?>, <a href="#comment-<?php comment_ID() ?>"><?php comment_time() ?></a></time>
				<?php edit_comment_link('<p class="comment-meta-item">Edit this comment</p>','',''); ?>
				<?php if ($comment->comment_approved == '0') : ?>
				<p class="comment-meta-item">Your comment is awaiting moderation.</p>
				<?php endif; ?>
			</div>

			<div class="comment-content post-content">
				<?php comment_text() ?>
				<?php comment_reply_link(array_merge( $args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
			</div>

	<?php }

	
	
	/**
	 *  Closing tag for a single comment.
	 */

	function end_el(&$output, $comment, $depth = 0, $args = array() ) { ?>

		</article>

	<?php }

	
	
	/**
	 *  Closing tag for the comments list wrapper.
	 */

	function __destruct() { ?>

		</section>

	<?php }

}


?>


<div id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>

		<h2 class="comments-title">
			<?php
				printf( _nx( '1 comment', '%1$s comments', get_comments_number(), 'sas' ),
				number_format_i18n( get_comments_number() ), '<span>' . get_the_title() . '</span>' );
			?>
		</h2>

		
		<ul class="comments-list">
			<?php
				wp_list_comments( array(
					'style' => 'ul'
				) );
			?>
		</ul>


		<div class="nav-comments">
			<?php paginate_comments_links(); ?> 
		</div>


		<?php if ( ! comments_open() && get_comments_number() ) : ?>
			<p class="no-comments"><?php _e( 'Comments are closed.' , 'sas' ); ?></p>
		<?php endif; ?>

	<?php endif; ?>


	<?php

	$comment_form_args = array(
		'comment_notes_after' => ''
	);

	comment_form( $comment_form_args );
	
	?>

</div>