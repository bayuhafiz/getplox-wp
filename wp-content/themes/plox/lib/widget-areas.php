<?php 
$json = get_settings_json();

/* * ********************************* Widget Areas ********************************* */

if( $json->theme_widgets_init != false ){


    add_action('init', 'theme_widgets_init');
    function theme_widgets_init() {

        global $json;

        if( isset($json->theme_widgets_init) ){

            foreach( $json->theme_widgets_init as $area ){

                $area_args = array();

                foreach( $area as $key => $value ){
                    $area_args[$key] = $value;

                }
                if(!array_key_exists('before_widget',$area_args) || !array_key_exists('after_widget',$area_args)) {
                    $area_args['before_widget'] = '<div id="%1$s" class="widget %2$s">';
                    $area_args['after_widget'] = '</div>';
                }
                if(!array_key_exists('before_title',$area_args) || !array_key_exists('after_title',$area_args)) {
                    $area_args['before_title'] = '<h3 class="widget-title">';
                    $area_args['after_title'] = '</h3>';
                }

                register_sidebar($area_args);

            }

        } else {

            register_sidebar(array(
                'name'  => __( 'Primary Widget Area', $json->theme_name ),
                'id'            => 'primary_widget_area',
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="widget-title">',
                'after_title'   => '</h3>'
                ));

        }
    }

}