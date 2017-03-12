<?php
$json = get_settings_json();

/* * ******************************* Navigation ******************************** */

if( !isset($json->register_nav_menus) ){
    register_nav_menus(array(
        "primary" => "Main Navigation"
    ));

} elseif ( ( $json->register_nav_menus != false) ){

    $nav_array = array();

    foreach( $json->register_nav_menus as $key => $value ){
        $nav_array[$key] = $value;
    }

    register_nav_menus($nav_array);

}