<?php
/*
 * Plugin Name: Stripe Payment Gateway for WooCommerce ( Basic )
 * Plugin URI: http://www.xadapter.com/product/stripe-payment-gateway-for-woocommerce/
 * Description: Make your Shop Orders with Credit Cards, Alipay and Bitcoin via Stripe.
 * Author: ExtensionHawk
 * Author URI: http://www.xadapter.com/vendor/extensionhawk/
 * Version: 1.0.5
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if (!defined('EH_STRIPE_MAIN_URL_PATH')) {
    define('EH_STRIPE_MAIN_URL_PATH', plugin_dir_url(__FILE__));
}
if (!defined('EH_STRIPE_MAIN_PATH')) {
    define('EH_STRIPE_MAIN_PATH', plugin_dir_path(__FILE__));
}
if (!defined('EH_STRIPE_VERSION')) {
    define('EH_STRIPE_VERSION', '1.0.5');
}
if (!defined('EH_STRIPE_MAIN_FILE')) {
    define('EH_STRIPE_MAIN_FILE', __FILE__);
}
require_once(ABSPATH."wp-admin/includes/plugin.php");
// Change the Pack IF BASIC  mention switch('BASIC') ELSE mention switch('PREMIUM')
switch('BASIC')
{
    case 'PREMIUM':
        $conflict   = 'basic';
        $base       = 'premium';
        break;
    case 'BASIC':
        $conflict   = 'premium';
        $base       = 'basic';
        break;
}
// Enter your plugin unique option name below $option_name variable
$option_name='eh_stripe_pack';
if(get_option($option_name)==$conflict)
{
    add_action('admin_notices','eh_wc_admin_notices', 99);
    deactivate_plugins(plugin_basename(__FILE__));
    function eh_wc_admin_notices()
    {
        is_admin() && add_filter('gettext', function($translated_text, $untranslated_text, $domain)
        {
            $old = array(
                "Plugin <strong>activated</strong>.",
                "Selected plugins <strong>activated</strong>."
            );
            $error_text='';
            // Change the Pack IF BASIC  mention switch('BASIC') ELSE mention switch('PREMIUM')
            switch('BASIC')
            {
                case 'PREMIUM':
                    $error_text="BASIC Version of this Plugin Installed. Please uninstall the BASIC Version before activating PREMIUM.";
                    break;
                case 'BASIC':
                    $error_text="PREMIUM Version of this Plugin Installed. Please uninstall the PREMIUM Version before activating BASIC.";
                    break;
            }
            $new = "<span style='color:red'>".$error_text."</span>";
            if (in_array($untranslated_text, $old, true)) {
                $translated_text = $new;
            }
            return $translated_text;
        }, 99, 3);
    }
    return;
}
else
{
    update_option($option_name, $base);	
    register_deactivation_hook(__FILE__, 'eh_stripe_deactivate_work');
    // Enter your plugin unique option name below update_option function
    function eh_stripe_deactivate_work()
    {
        update_option('eh_stripe_pack', '');
    }
    register_activation_hook(__FILE__, 'eh_stripe_init_log');
    add_action('plugins_loaded', 'eh_stripe_check',99);
    include(EH_STRIPE_MAIN_PATH."includes/log.php");
    function eh_stripe_check()
    {
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) 
        {
            if(!class_exists('Stripe\Stripe'))
            {	
                include(EH_STRIPE_MAIN_PATH."vendor/stripe/init.php");
                add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ),'eh_stripe_plugin_action_links');
                eh_stripe_init();
            }
            else
            {
                add_action( 'admin_notices','eh_stripe_admin_notices', 15 );
            }

        }
        else
        {
            add_action( 'admin_notices','eh_stripe_wc_admin_notices', 99 );
            deactivate_plugins( plugin_basename( __FILE__ ) );
        }

    }
    function eh_stripe_plugin_action_links( $links ) 
    {
            $setting_link = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=eh_stripe_pay');
            $plugin_links = array(
                    '<a href="' . $setting_link . '">' . __( 'Settings', 'eh-stripe-gateway' ) . '</a>',
                    '<a href="http://www.xadapter.com/support/forum/stripe-payment-gateway-for-woocommerce/" target="_blank">' . __( 'Support', 'eh-stripe-gateway' ) . '</a>',
                    '<a href="http://www.xadapter.com/product/stripe-payment-gateway-for-woocommerce/" target="_blank">' . __( 'Premium Upgrade', 'eh-stripe-gateway' ) . '</a>',
            );
            return array_merge( $plugin_links, $links );
    }
    function eh_stripe_init()
    {
        function eh_section_add_stripe_gateway( $methods ) 
        {
                $methods[] = 'EH_Stripe_Payment'; 
                return $methods;
        }
        add_filter( 'woocommerce_payment_gateways', 'eh_section_add_stripe_gateway' );
        if ( ! class_exists( 'EH_Stripe_Payment' ) ) 
        {
            include(EH_STRIPE_MAIN_PATH."includes/class-stripe-api.php");        
            $eh_stripe=get_option("woocommerce_eh_stripe_pay_settings");
            if('yes'===$eh_stripe['overview'])
            {
                include(EH_STRIPE_MAIN_PATH."includes/class-overview-table-data.php");
                include(EH_STRIPE_MAIN_PATH."includes/class-stripe-overview.php");
            }
            include(EH_STRIPE_MAIN_PATH."includes/include-ajax-functions.php");
        }
    }

    function eh_stripe_admin_notices()
    {
            is_admin() && add_filter( 'gettext', 
        function( $translated_text, $untranslated_text, $domain)
        {
            $old = array(
                "Plugin <strong>activated</strong>.",
                "Selected plugins <strong>activated</strong>." 
            );

            $new = "<span style='color:red'>Stripe Payment for Woocommerce (ExtensionHawk) may not work properly.</span> Beacause another Stripe Payment plugin is active.Please Deactivate the corresponding plugin.";

            if (in_array($untranslated_text, $old, true)) {
                        $translated_text = $new;
                    }

            return $translated_text;
        }
            , 99, 3 );
    }
    function eh_stripe_wc_admin_notices()
    {
        is_admin() && add_filter( 'gettext', 
        function( $translated_text, $untranslated_text, $domain)
        {
            $old = array(
                "Plugin <strong>activated</strong>.",
                "Selected plugins <strong>activated</strong>." 
            );

            $new = "<span style='color:red'>Stripe Payment for Woocommerce (ExtensionHawk)-</span> Plugin Needs Woocommerce to Work.";

            if (in_array($untranslated_text, $old, true)) {
                        $translated_text = $new;
                    }

            return $translated_text;
        }
            , 99, 3 );
    }
    function eh_stripe_init_log()
    {
        $log=new WC_Logger();
        $init_msg=EH_STRIPE_LOG::init_live_log();
        $log->add("eh_stripe_pay_live", $init_msg);
        $init_msg=EH_STRIPE_LOG::init_dead_log();
        $log->add("eh_stripe_pay_dead", $init_msg);
    }
}
