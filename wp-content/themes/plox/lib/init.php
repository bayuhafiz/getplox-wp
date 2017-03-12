<?php

function get_settings_json() {

	$path = get_template_directory() . '/settings.json';
	$json = file_get_contents($path);
	$json = preg_replace("/\/\*(?s).*?\*\//", "", $json);
	$json = json_decode($json);
	if(empty($json)) add_action( 'admin_notices', 'json_error' );
	return $json;
}

$json = get_settings_json();

/* * ******************************** Init ************************************* */

$path = (dirname(__FILE__)).'/widgets/';
foreach (glob($path.'*.php') as $filename) {
	if(basename($filename)!="index.php")
	include_once($filename);
}


/* Add Theme support */

add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ) );
add_theme_support( 'title-tag' );

/* Add theme options page by default */

if( (!isset($json->acf_options)) || ( $json->acf_options->init == true) ){
	if( function_exists('acf_add_options_page') ) {

		acf_add_options_page(array(
			'page_title'    => $json->acf_options->page_title,
			'menu_title'    => $json->acf_options->menu_title,
			'menu_slug'     => $json->acf_options->menu_slug
		));
		if( isset($json->acf_options->subpages) ){
			foreach( $json->acf_options->subpages as $subpage ){
				acf_add_options_sub_page(array(
					'page_title'    => $subpage->page_title,
					'menu_title'    => $subpage->menu_title,
					'parent_slug'   => $json->acf_options->menu_slug,
				));
			}
		}
	}
}

add_filter('wp_footer', 'localize_scripts');
add_filter('widget_text', 'do_shortcode');
add_post_type_support( 'page', 'excerpt' );


function get_script_url($value) {

	if(preg_match("@\/\/@", $value)) {
		$url = $value;
	} else {
		$url = get_template_directory_uri()."/".$value;
	}

	return $url;
}


/* Enqueue styles and scripts */

add_action('wp_enqueue_scripts', 'theme_enqueue_scripts');
function theme_enqueue_scripts() {
	global $json;

	//scripts
	wp_enqueue_script("jquery");
	if( is_singular() && get_option('thread_comments') ) {
		wp_enqueue_script('comment-reply');
	}

	//scripts
	if ( $json->enqueue_script != false ) {
		foreach( $json->enqueue_script as $handle => $script ) {
			$src = '';
			$deps = array();
			$ver = false;
			$in_footer = true;

			if( is_object($script) ) {
				if( isset($script->src) )
					$src = get_script_url($script->src);

				if( isset($script->deps) )
					$deps = $script->deps;

				if( isset($script->ver) )
					$ver = $script->ver;

				if( isset($script->in_footer ) )
					$in_footer = $script->in_footer;
			} else {
				$src = get_script_url($script);
			}

			if( empty($src) )
				continue;

			wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
		}
	}

	//styles
	if ( $json->enqueue_style != false ) {
		foreach( $json->enqueue_style as $handle => $style ) {
			$src = '';
			$deps = array();
			$ver = false;
			$media = 'all';

			if( is_object($style) ) {
				if( isset($style->src) )
					$src = get_script_url($style->src);

				if( isset($style->deps) )
					$deps = $style->deps;

				if( isset($style->ver) )
					$ver = $style->ver;

				if( isset($style->media) )
					$media = $style->media;
			} else {
				$src = get_script_url($style);
			}

			if( empty($src) )
				continue;

			wp_enqueue_style($handle, $src, $deps, $ver, $media);
		}
	}

}



/* * ***************************** Amin UI ************************************* */

add_filter('manage_posts_columns', 'posts_columns_id', 5);
add_action('manage_posts_custom_column', 'posts_custom_id_columns', 5, 2);
add_filter('manage_pages_columns', 'posts_columns_id', 5);
add_action('manage_pages_custom_column', 'posts_custom_id_columns', 5, 2);
add_filter('manage_media_columns', 'column_id');
add_filter('manage_media_custom_column', 'column_id_row', 10, 2);

function posts_columns_id($defaults) {
	$defaults['wps_post_id'] = __('ID');
	return $defaults;
}

function posts_custom_id_columns($column_name, $id) {
	if ($column_name === 'wps_post_id') {
	  echo $id;
	}
}

function column_id($columns) {
	$columns['colID'] = __('ID');
	return $columns;
}

function column_id_row($columnName, $columnID) {
	if ($columnName == 'colID') {
	  echo $columnID;
	}
}

function json_error() {
	?>
	<div class="error">
		<p><?php _e( 'Settings.json is invalid.', 'plox-admin' ); ?></p>
	</div>
	<?php
}


/* * *************************** TGM ACtivation Plugin ********************** */
require_once dirname( __FILE__ ) . '/plugins/TGM-Plugin-Activation/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'defualt_register_required_plugins' );

function defualt_register_required_plugins() {

	$plugins = array(

		array(
			'name'               => 'Custom Post Types, Taxonomies and Shortcodes', // The plugin name.
			'slug'               => 'cptts', // The plugin slug (typically the folder name).
			'source'             => get_stylesheet_directory() . '/lib/plugins/cptts.zip', // The plugin source.
			'required'           => true, // If false, the plugin is only 'recommended' instead of required.
			'version'            => '0.5', // E.g. 1.0.0. If set, the active plugin must be this version or higher.
			'force_activation'   => true, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
			'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
			'external_url'       => '', // If set, overrides default API URL and points to an external URL.
		)

	);

	$config = array(
		'default_path' => '',                      // Default absolute path to pre-packaged plugins.
		'menu'         => 'tgmpa-install-plugins', // Menu slug.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => false,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.
		'strings'      => array(
			'page_title'                      => __( 'Install Required Plugins', 'tgmpa' ),
			'menu_title'                      => __( 'Install Plugins', 'tgmpa' ),
			'installing'                      => __( 'Installing Plugin: %s', 'tgmpa' ), // %s = plugin name.
			'oops'                            => __( 'Something went wrong with the plugin API.', 'tgmpa' ),
			'notice_can_install_required'     => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.' ), // %1$s = plugin name(s).
			'notice_can_install_recommended'  => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.' ), // %1$s = plugin name(s).
			'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ), // %1$s = plugin name(s).
			'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s).
			'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s).
			'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ), // %1$s = plugin name(s).
			'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.' ), // %1$s = plugin name(s).
			'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ), // %1$s = plugin name(s).
			'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
			'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins' ),
			'return'                          => __( 'Return to Required Plugins Installer', 'tgmpa' ),
			'plugin_activated'                => __( 'Plugin activated successfully.', 'tgmpa' ),
			'complete'                        => __( 'All plugins installed and activated successfully. %s', 'tgmpa' ), // %s = dashboard link.
			'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
		)
	);

	tgmpa( $plugins, $config );

}

add_filter( 'wp_nav_menu_items', 'prefix_remove_menu_item_whitespace' );
function prefix_remove_menu_item_whitespace( $html_content ) {
    return preg_replace( '/>\s+</', '><', $html_content );
}

remove_filter('the_content', 'shortcode_unautop');
remove_filter('the_content', 'wpautop');
add_filter('the_content', 'wpautop', 12);

if( array_key_exists('acf_the_content', $GLOBALS['wp_filter']) ) {
    remove_filter('acf_the_content', 'shortcode_unautop');
    remove_filter('acf_the_content', 'wpautop');
    add_filter('acf_the_content', 'wpautop', 12);
}
