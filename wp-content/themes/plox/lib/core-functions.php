<?php 
$json = get_settings_json();

/**
 * Extract page name from page template file
 */
function custom_body_class($s_class = null) {
	$classes = get_body_class();
	$s_classes_text = '';
	foreach ($classes as $class) {
		$new_class = str_replace('page-template-', '', $class);
		$new_class = str_replace('page-tpl-', '', $new_class);
		$new_class = str_replace('-php', '', $new_class);
		$s_classes_text .= ' ' . $new_class;
	}
	return trim($s_classes_text) . ' ' . $s_class;
}

/**
 * Cut text to specific number of signs
 * 
 * @param string $s_content
 * @param int $i_length_limit
 * @return string Cut text
 */
function stripteaser($s_content, $i_length_limit) {
	$a_content = split(" ", $s_content);
	$i_current_length = 0;
	$s_result = '';

	if (strlen($s_content) <= $i_length_limit) {
		return $s_content;
	}

	for ($i = 0; $i < count($a_content); $i++) {
		$i_current_length += (int) (strlen($a_content[$i]));
		if ($i_current_length < $i_length_limit) {
			$s_result .= $a_content[$i] .= " ";
		} else {
			break;
		}
	}

	return trim($s_result) . "...";
}

/**
 * Retrieve theme images url without trailing slash
 * 
 *
 * @return string Theme images url without trailing slash
 */
function get_the_images_url() {
	return get_stylesheet_directory_uri()."/images";
}

/**
 * Display theme images url without trailing slash
 * 
 *
 * @return null
 */
function the_images_url() {
	echo get_the_images_url();
}


function my_search_posts_filter($query) {
	if ($query->is_search) {
		$query->set('post_type', array('post', 'page'));
	}
	return $query;
}


if ( ! function_exists( '_wp_render_title_tag' ) ) {
	function theme_slug_render_title() {
		?>
		<title><?php wp_title( '|', true, 'right' ); ?></title>
		<?php
	}
	add_action( 'wp_head', 'theme_slug_render_title' );
}