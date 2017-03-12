<?php
//**************************************************************************** */
//
//  1. Init
//  2. Navigation
//  3. Widget Areas
//  4. Thumbnails
//  5. Core functions
//  6. Custom functions
//
//**************************************************************************** */

/* * ******************************** Init *********************************** */

include_once 'lib/init.php';

/* * ****************************** Navigation ******************************* */

include_once 'lib/nav.php';

/* * ****************************** Widget Areas ***************************** */

include_once 'lib/widget-areas.php';

/* * ****************************** Thumbnails ******************************* */

include_once 'lib/thumbnails.php';

/* * ************************* Core Functions ******************************** */

include_once 'lib/core-functions.php';

/* * ************************ Custom Functions ******************************* */

include_once 'lib/custom-functions.php';

/* * ************************** Code Snippets ******************************** */

foreach( glob(get_template_directory() . '/lib/snippets/*', GLOB_ONLYDIR) as $dir ) {
	$files = glob($dir . '/*.php');

	foreach( $files as $file ) {
		$headers = get_file_data($file, array('type' => 'Type'));

		if( ! empty($headers['type']) && 'snippet' == $headers['type'] )
			include_once $file;
	}
}
