<?php
/*
Plugin Name: Custom Post Types, Taxonomies and Shortcodes
Plugin URI: -
Description: Register all necessary Custom Post Types, Taxonomies and Shortcodes
Version: 0.5
Author: Theme Owner
Author URI:
License: GPL
Copyright: Theme Owner
*/

include_once 'shortcodes.php';

if (!class_exists('Cptts')):

class Cptts {

	private $settings_file = 'settings.json';
	private $json;


	/**
	 * Cptts constructor.
     */
	public function __construct()
	{
		$this->json = $this->get_settings_json();
		if ($this->json === null) {
			add_action('admin_notices', array($this, 'json_error'));
			return;
		}

		add_action('admin_init', array($this, 'load_textdomain'));
		add_action('init', array( $this, 'create_post_types' ));
	}

	/**
	 * Loads the plugin textdomain.
     */
	public function load_textdomain() {
		load_plugin_textdomain('cptts', false, 'cptts/languages');
	}

	/**
	 * Iterates parsed json file and creates post types.
     */
	public function create_post_types()
	{
		$json_arrayfied = (array) $this->json;
		if (empty($json_arrayfied) || !isset($this->json->create_post_type)) {
			return;
		}

		foreach($this->json->create_post_type as $name => $post_type) {
			$labels = array(
				'name' => _x($post_type->label_single, 'post type general name', 'cptts-admin'),
				'singular_name' => _x($post_type->label_single, 'post type singular name', 'cptts-admin'),
				'add_new' => _x('Add New', $post_type->label_single, 'cptts-admin'),
				'add_new_item' => __('Add New ' . $post_type->label_single, 'cptts-admin'),
				'edit_item' => __('Edit ' . $post_type->label_single, 'cptts-admin'),
				'new_item' => __('New ' . $post_type->label_single, 'cptts-admin'),
				'all_items' => __('All ' . $post_type->label_multi, 'cptts-admin'),
				'view_item' => __('View ' . $post_type->label_single, 'cptts-admin'),
				'search_items' => __('Search ' . $post_type->label_multi, 'cptts-admin'),
				'not_found' => __('No ' . $post_type->label_multi . ' found', 'cptts-admin'),
				'not_found_in_trash' => __('No ' . $post_type->label_multi . ' found in Trash', 'cptts-admin'),
				'parent_item_colon' => __('Parent ' . $post_type->label_multi . ':', 'cptts-admin'),
				'menu_name' => __($post_type->menu_name, 'cptts-admin')
			);

			$args = array(
				'labels' => $labels,
				'rewrite' => array('slug' => _x($post_type->slug, 'URL slug', 'cptts-admin')),
				'public' => true
			);

			$atts_to_skip = array('slug', 'tax', 'label_single', 'label_multi', 'menu_name');
			$this->merge_atts($post_type, $args, $atts_to_skip);

			register_post_type($name, $args);

			if (isset($post_type->tax) && is_array($post_type->tax)) {
				$this->create_taxonomies($name, $post_type);
			}
		}
	}

	/**
	 * Iterates taxonomies of single post type, registers them, and assigns them to the post type.
	 *
	 * @param string $post_name Post Type name to assign taxonomy to
	 * @param object $post Post Type object parsed from JSON file
     */
	private function create_taxonomies($post_name, $post)
	{
		foreach ($post->tax[0] as $tax_name => $taxonomy) {
			$tax_labels = array(
				'name' => _x($taxonomy->tax_multi, 'taxonomy general name'),
				'singular_name' => _x($taxonomy->tax_single, 'taxonomy singular name'),
				'search_items' =>  __('Search '.$taxonomy->tax_multi),
				'all_items' => __('All '.$taxonomy->tax_multi),
				'parent_item' => __('Parent '.$taxonomy->tax_single),
				'parent_item_colon' => __('Parent '.$taxonomy->tax_single.':'),
				'edit_item' => __('Edit '.$taxonomy->tax_single),
				'update_item' => __('Update '.$taxonomy->tax_single),
				'add_new_item' => __('Add New '.$taxonomy->tax_single),
				'new_item_name' => __('New '.$taxonomy->tax_single.' Name'),
				'menu_name' => __($taxonomy->tax_single)
			);

			$args = array(
				'labels' => $tax_labels,
				'rewrite' => array('slug' => $taxonomy->slug)
			);

			$atts_to_skip = array('tax_single', 'tax_multi', 'slug');
			$this->merge_atts($taxonomy, $args, $atts_to_skip);

			register_taxonomy($tax_name, array($post_name), $args);
		}
	}

	/**
	 * Merges object properties into an array, optionally skipping some of them.
	 *
	 * @param object $object Object to grab properties from.
	 * @param array $array Array to put properties into.
	 * @param array $skip Array of names of properties to skip
     */
	private function merge_atts($object, &$array, $skip = array())
	{
		foreach ($object as $attribute => $value) {
			if (in_array($attribute, $skip)) {
				continue;
			}

			$array[$attribute] = $value;
		}
	}

	/**
	 * Reads, corrects, and parses plugins json file file.
	 *
	 * @return mixed The same as php function json_decode
     */
	private function get_settings_json()
	{
		$url = dirname(__FILE__) . '/' . $this->settings_file;
		$json = file_get_contents($url);
		$json = preg_replace("/\/\*(?s).*?\*\//", "", $json);
		$json = json_decode($json);

		return $json;
	}

	/**
	 * Callback of admin_notices action.
     */
	public function json_error()
	{
		?>
		<div class="error">
			<p><?php _e('Settings.json in plugins/cptts is invalid.', 'cptts-admin'); ?></p>
		</div>
		<?php
	}

}

new Cptts();

endif;
