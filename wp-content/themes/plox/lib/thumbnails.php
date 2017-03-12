<?php
$json = get_settings_json();

/* * ******************************* Thumbnails ******************************** */

if( $json->enable_thumbnails != false ) {
    if (function_exists('add_theme_support')) {

        add_theme_support('post-thumbnails');

        if( isset($json->thumbnails) ) {
            foreach( $json->thumbnails as $thumb_params ) {
                add_image_size($thumb_params[0], $thumb_params[1], $thumb_params[2], $thumb_params[3]);
            }
        }    
    }
}