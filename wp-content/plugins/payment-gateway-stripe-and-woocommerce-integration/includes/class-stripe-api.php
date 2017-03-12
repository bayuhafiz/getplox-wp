<?php
if (!defined('ABSPATH')) {
    exit;
}
class EH_Stripe_Payment extends WC_Payment_Gateway
{
    public function __construct()
    {
            $this->id                   = 'eh_stripe_pay';
            $this->method_title         = __( 'Stripe Payment', 'eh-stripe-gateway' );
            $this->has_fields           = true;
            $this->supports             = array(
                    'products',
                    'refunds'
            );
            $this->init_form_fields();
            $this->init_settings();
            $this->enabled                           = $this->get_option( 'enabled' );
            $this->title                             = $this->get_option( 'title' );
            $this->description                       = $this->get_option( 'description' );
            $this->eh_stripe_order_button            = $this->get_option( 'eh_stripe_order_button' );
            $this->eh_stripe_mode                    = $this->get_option( 'eh_stripe_mode' );
            $this->eh_stripe_test_secret_key         = $this->get_option('eh_stripe_test_secret_key');
            $this->eh_stripe_test_publishable_key    = $this->get_option('eh_stripe_test_publishable_key');
            $this->eh_stripe_live_secret_key         = $this->get_option('eh_stripe_live_secret_key');
            $this->eh_stripe_live_publishable_key    = $this->get_option('eh_stripe_live_publishable_key');
            $this->eh_stripe_checkout_cards          = $this->get_option('eh_stripe_checkout_cards');
            $this->eh_stripe_email_receipt           = 'yes' === $this->get_option( 'eh_stripe_email_receipt', 'yes' );
            $this->eh_stripe_bitcoin                 = 'USD' === strtoupper( get_woocommerce_currency() ) && 'yes' === $this->get_option( 'eh_stripe_bitcoin' );
            $this->eh_stripe_alipay                  = 'USD' === strtoupper( get_woocommerce_currency() ) && 'yes' === $this->get_option( 'eh_stripe_alipay' );
            $this->eh_stripe_billing_address_check   = 'yes' === $this->get_option( 'eh_stripe_billing_address_check' );
            $this->eh_stripe_checkout_image_check    = 'yes' === $this->get_option( 'eh_stripe_checkout_image_check', 'yes' );
            $this->eh_stripe_checkout_image          = ('' == $this->get_option( 'eh_stripe_checkout_image'))?EH_STRIPE_MAIN_URL_PATH."assets/img/stripe.png":$this->get_option( 'eh_stripe_checkout_image');
            $this->eh_stripe_enable_save_cards       = 'yes' === $this->get_option( 'eh_stripe_enable_save_cards' );
            $this->eh_stripe_logging                 = 'yes' === $this->get_option( 'eh_stripe_logging' );
            $this->eh_stripe_zerocurrency            = array("BIF","CLP","DJF","GNF","JPY","KMF","KRW","MGA","PYG","RWF","VND","VUV","XAF","XOF","XPF");

            $this->order_button_text = __($this->eh_stripe_order_button, 'eh-stripe-gateway' );

            if('yes' === $this->enabled && ('' != $this->eh_stripe_test_secret_key || '' != $this->eh_stripe_live_secret_key ) &&('' != $this->eh_stripe_test_publishable_key || '' != $this->eh_stripe_live_publishable_key ))
            {
                $this->method_description   = sprintf(__( "Accept credit card payments directly on your website via Stripe payment gateway.", 'eh-stripe-gateway' ));
            }
            else
            {
                $this->method_description   = sprintf(__( '<div class="updated inline notice is-dismissible" ><table><tr><td><span style="font-size: 1.2em;">Stripe provides payment services over 25 countries </span></td><td> - </td><td><button class="button-primary" style="width:100%%"><a href="https://stripe.com/global" target="_blank" style="color: antiquewhite; text-decoration: none;">Available Countries</a></button></td></tr><tr><td><span style="font-size: 1.2em;">If you dont have Stripe Account, get it for free easily </span></td><td> - </td><td><button class="button-primary" style="width:100%%"><a href="https://dashboard.stripe.com/register" target="_blank" style="color: antiquewhite; text-decoration: none;">Register Now</a></button></td></tr><tr><td><span style="font-size: 1.2em;">Get your Access Keys and put it here </span></td><td> - </td><td><button class="button-primary" style="width:100%%"><a href="https://dashboard.stripe.com/account/apikeys" target="_blank" style="color: antiquewhite; text-decoration: none;">Get API Keys</a></button></td></tr></table></div>', 'eh-stripe-gateway' ));
            }
            if ( 'test' === $this->eh_stripe_mode ) {
                $this->description  = sprintf( __( '<strong>Stripe TEST MODE Enabled: </strong> Use these <a href="https://stripe.com/docs/testing" target="_blank"> Test Card Details </a> for Testing.', 'eh-stripe-gateway' ));
                $this->description  = trim( $this->description );
            }
            if('test'  == $this->eh_stripe_mode  )
                { \Stripe\Stripe::setApiKey($this->eh_stripe_test_secret_key);  }
            else
                { \Stripe\Stripe::setApiKey($this->eh_stripe_live_secret_key);  }

            if (is_admin()) 
            {
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            }
            // Hooks
            add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
    }
    public function get_icon() 
    {
            $ext   = version_compare( WC()->version, '2.6', '>=' ) ? '.svg' : '.png';
            $style = version_compare( WC()->version, '2.6', '>=' ) ? 'style="margin-left: 0.3em"' : '';
            $icon='';
            if(is_array($this->eh_stripe_checkout_cards))
            {
                if (in_array('Visa', $this->eh_stripe_checkout_cards)) {
                    $icon .='<img src="' . WC_HTTPS::force_https_url(WC()->plugin_url() . '/assets/images/icons/credit-cards/visa' . $ext) . '" alt="Visa" width="32" title="VISA" ' . $style . ' />';   
                }
                if (in_array('MasterCard', $this->eh_stripe_checkout_cards)) {
                    $icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/mastercard' . $ext ) . '" alt="Mastercard" width="32" title="Master Card" ' . $style . ' />';
                }
                if (in_array('American Express', $this->eh_stripe_checkout_cards)) {
                    $icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/amex' . $ext ) . '" alt="Amex" width="32" title="American Express" ' . $style . ' />';
                }
                if ( 'USD' === get_woocommerce_currency() ) {
                        if (in_array('Discover', $this->eh_stripe_checkout_cards)) {
                            $icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/discover' . $ext ) . '" alt="Discover" width="32" title="Discover" ' . $style . ' />';
                        }
                        if (in_array('JCB', $this->eh_stripe_checkout_cards)) {
                            $icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/jcb' . $ext ) . '" alt="JCB" width="32" title="JCB" ' . $style . ' />';
                        }
                        if (in_array('Diners Club', $this->eh_stripe_checkout_cards)) {
                            $icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/diners' . $ext ) . '" alt="Diners" width="32" title="Diners Club" ' . $style . ' />';
                        }
                }
            }
            if ( $this->eh_stripe_bitcoin ) {
                    $icon .= '<img src="' . WC_HTTPS::force_https_url( EH_STRIPE_MAIN_URL_PATH. 'assets/img/bitcoin.png') . '" alt="Bitcoin" width="52" title="Bitcoin" ' . $style . ' />';
            }
            if ( $this->eh_stripe_alipay ) {
                    $icon .= '<img src="' . WC_HTTPS::force_https_url( EH_STRIPE_MAIN_URL_PATH. 'assets/img/alipay.png' ) . '" alt="Alipay" width="52" title="Alipay" ' . $style . ' />';
            }
            return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
    }
    public function is_available() 
    {
            if ( 'yes' === $this->enabled ) {
                    if ( ! $this->eh_stripe_mode && is_checkout() ) {
                            return false;
                    }
                    if('test'===$this->eh_stripe_mode)
                    {
                        if ( ! $this->eh_stripe_test_secret_key || ! $this->eh_stripe_test_publishable_key ) {
                            return false;
                        }
                    }
                    else 
                    {
                        if ( ! $this->eh_stripe_live_secret_key || ! $this->eh_stripe_live_publishable_key ) {
                            return false;
                         }
                    }
                    return true;
            }
            return false;
    }
    public function admin_options() {
			include('market.php');
            parent::admin_options();
	}
    public function init_form_fields() 
    {
        $this->form_fields = include( 'eh-stripe-settings-page.php' );
        wp_enqueue_media();
        $page = (isset($_GET['page'])) ? esc_attr($_GET['page']) : false;
        $tab=(isset($_GET['tab'])) ? esc_attr($_GET['tab']) : false;
        $section=(isset($_GET['section'])) ? esc_attr($_GET['section']) : false;
        if ('wc-settings' != $page&&'checkout'!=$tab&&'eh_stripe_pay'!=$section)
            return;
        wc_enqueue_js( "
                jQuery( '#woocommerce_eh_stripe_pay_eh_stripe_mode' ).on( 'change', function() {
                                var test    = jQuery( '#woocommerce_eh_stripe_pay_eh_stripe_test_publishable_key, #woocommerce_eh_stripe_pay_eh_stripe_test_secret_key' ).closest( 'tr' ),
                                live = jQuery( '#woocommerce_eh_stripe_pay_eh_stripe_live_publishable_key, #woocommerce_eh_stripe_pay_eh_stripe_live_secret_key' ).closest( 'tr' );

                                if ('test' === jQuery( this ).val()) {
                                        test.show();
                                        live.hide();
                                } else {
                                        test.hide();
                                        live.show();
                                }
                        }).change();
                jQuery( document ).ready( function( $ ) {
                        var file_frame;
                        jQuery('#eh_stripe_preview').on('click', function( event ){
                            file_frame = wp.media.frames.file_frame = wp.media({
                                    title: 'Select a image to set Stripe Checkout image',
                                    button: {
                                            text: 'Use this image',
                                    },
                                    multiple: false
                            });
                            file_frame.on( 'select', function() {
                                    attachment = file_frame.state().get('selection').first().toJSON();
                                    console.log(attachment);
                                    $( '#eh_stripe_preview' ).attr( 'src', attachment.url );
                                    $( '#woocommerce_eh_stripe_pay_eh_stripe_checkout_image' ).val( attachment.url );
                            });
                            file_frame.open();
                        });
                });
                jQuery( '#woocommerce_eh_stripe_pay_eh_stripe_checkout_image_check' ).on( 'change', function() {
                    var checkout_image    = jQuery( '#woocommerce_eh_stripe_pay_eh_stripe_checkout_image').closest( 'tr' );
                    if ( jQuery( this ).is( ':checked' ) ) {
                            checkout_image.show();
                    } else {
                            checkout_image.hide();
                    }
                }).change();
        ");
    }
    public function payment_scripts() 
    {
        wp_enqueue_script( 'stripe', 'https://checkout.stripe.com/v2/checkout.js', '', '2.0', true );
        wp_enqueue_script( 'eh_stripe_checkout', plugins_url( 'assets/js/eh_stripe_checkout.js', EH_STRIPE_MAIN_FILE ), array( 'stripe' ), true );
        if('test'  == $this->eh_stripe_mode  ){
            $public_key=$this->eh_stripe_test_publishable_key;  }
        else{
            $public_key=$this->eh_stripe_live_publishable_key;  }
        $stripe_params = array(
                'key'                  => $public_key,
                'i18n_terms'           => __( 'Please accept the terms and conditions first', 'eh-stripe-gateway' ),
                'i18n_required_fields' => __( 'Please fill in required checkout fields first', 'eh-stripe-gateway' ),
        );
        wp_localize_script( 'eh_stripe_checkout', 'eh_stripe_val', apply_filters( 'eh_stripe_val', $stripe_params ) );
    }
    public function payment_fields() 
    {
        $user                 = wp_get_current_user();
        if ( $user->ID ) {
                $user_email = get_user_meta( $user->ID, 'billing_email', true );
                $user_email = $user_email ? $user_email : $user->user_email;
        } else {
                $user_email = '';
        }
        echo '<div class="status-box">';
        if ( $this->description ) {
                echo apply_filters( 'eh_stripe_desc', wpautop( wp_kses_post("<span>". $this->description."</span>" ) ) );
        }
        echo "</div>";
        $pay_button_text = __( 'Pay', 'eh-stripe-gateway' );
        echo '<div
                id="eh-stripe-pay-data"
                data-panel-label="' . esc_attr( $pay_button_text ) . '"
                data-description="'.  esc_attr('Verify your Email address ').'"
                data-email="' . esc_attr( $user_email ) . '"
                data-amount="' . esc_attr( $this->get_stripe_amount( WC()->cart->total ) ) . '"
                data-name="' . esc_attr( sprintf( __( '%s', 'eh-stripe-gateway' ), get_bloginfo( 'name', 'display' ) ) ) . '"
                data-currency="' . esc_attr( strtolower( get_woocommerce_currency() ) ) . '"
                data-image="' . esc_attr($this->eh_stripe_checkout_image_check?$this->eh_stripe_checkout_image:''). '"
                data-bitcoin="' . esc_attr( $this->eh_stripe_bitcoin ? 'true' : 'false' ) . '"
                data-alipay="' . esc_attr( $this->eh_stripe_alipay ? 'true' : 'false' ) . '"    
                data-allow-remember-me="' . esc_attr( $this->eh_stripe_enable_save_cards ? 'true' : 'false' ) . '"
                data-billing-address="'.  esc_attr($this->eh_stripe_billing_address_check  ? 'true' : 'false').'"
                data-locale="auto">';  
        echo '</div>';
    }
    public function get_stripe_amount( $total, $currency = '' ) {
        if ( ! $currency ) {
                $currency = get_woocommerce_currency();
        }
        if (in_array(strtoupper( $currency ),$this->eh_stripe_zerocurrency) ) {
            // Zero decimal currencies
            $total = absint( $total );
        }
        else{
            $total = round( $total, 2 ) * 100; // In cents
        }
         return $total;
    }
    public function reset_stripe_amount( $total, $currency = '' ) {
        if ( ! $currency ) {
                $currency = get_woocommerce_currency();
        }
        if (in_array(strtoupper( $currency ),$this->eh_stripe_zerocurrency) ) {
            // Zero decimal currencies
            $total = absint( $total );
        }
        else{
            $total = round( $total, 2 ) / 100; // In cents
        }
        return $total;
    }
    public function get_clients_details() 
    {
        return array(
            'IP' => $_SERVER['REMOTE_ADDR'],
            'Agent' => $_SERVER['HTTP_USER_AGENT'],
            'Referer' => $_SERVER['HTTP_REFERER']
            );
    }

    private function get_charge_details($wc_order,$token_id,$client,$currency,$amount)
    {
        $charge = array( 
            'amount'                    => $amount, 
            'currency'                  => $currency,
            'capture'                   => true,
            'metadata'                  => array(
                    'order_id'          => $wc_order->get_order_number(),
                    'Total Tax'         => $wc_order->get_total_tax(),
                    'Total Shipping'    => $wc_order->get_total_shipping(),
                    'Customer IP'       => $client['IP'],
                    'Agent'             => $client['Agent'],
                    'Referer'           => $client['Referer'],
                    'WP customer #'     => $wc_order->user_id,
                    'Billing Email'     => $wc_order->billing_email,
                    ) ,
            'description'               => get_bloginfo('blogname').' Order #'.$wc_order->get_order_number(),
        );
        if($this->eh_stripe_email_receipt)
        {
            $charge['receipt_email'] = $wc_order->billing_email;
        }
        $charge['shipping'] = array(
            'address' => array(
                    'line1'                 => $wc_order->shipping_address_1,
                    'line2'                 => $wc_order->shipping_address_2,
                    'city'                  => $wc_order->shipping_city,
                    'state'                 => $wc_order->shipping_state,
                    'country'               => $wc_order->shipping_country,
                    'postal_code'   => $wc_order->shipping_postcode         
                    ),
            'name' => $wc_order->shipping_first_name.' '.$wc_order->shipping_last_name,
            'phone'=> $wc_order->billing_phone 
        );
        $charge['card']      = $token_id;
        return $charge ;
    }
    public function make_charge_params($charge_value,$order_id)
    {
        $wc_order   = wc_get_order($order_id);
        $charge_data =  json_decode(json_encode($charge_value));
        $origin_time=date('Y-m-d H:i:s',time()+get_option( 'gmt_offset' ) * 3600);
        $charge_parsed = array(
            "id"                       => $charge_data->id,
            "source_id"                => $charge_data->source->id,
            "amount"                   => $this->reset_stripe_amount($charge_data->amount,$charge_data->currency),
            "amount_refunded"          => $this->reset_stripe_amount($charge_data->amount_refunded,$charge_data->currency),
            "currency"                 => strtoupper($charge_data->currency),
            "order_amount"             => $wc_order->order_total,
            "order_currency"           => $wc_order->order_currency,
            "captured"                 => $charge_data->captured?"Captured":"Uncaptured",
            "transaction_id"           => $charge_data->balance_transaction,
            "mode"                     => (false==$charge_data->livemode)?'Test':'Live',
            "metadata"                 => $charge_data->metadata,
            "created"                  => date('Y-m-d H:i:s', $charge_data->created),
            "paid"                     => $charge_data->paid?'Paid':'Not Paid',
            "receiptemail"             => (null==$charge_data->receipt_email)?'Receipt not send':$charge_data->receipt_email,
            "receiptnumber"            => (null==$charge_data->receipt_number)?'No Receipt Number':$charge_data->receipt_number,
            "source_type"              => ( 'card' == $charge_data->source->object ) ? ($charge_data->source->brand . "( " .$charge_data->source->funding. " )") : (( 'bitcoin_receiver' == $charge_data->source->object ) ? 'Bitcoin' : (( 'alipay_account' == $charge_data->source->object ) ? 'Alipay' : 'Undefined')),
            "status"                   => $charge_data->status,
            "origin_time"              => $origin_time
        );
        $trans_time=date('Y-m-d H:i:s',time()+((get_option( 'gmt_offset' ) * 3600)+10));
        $tranaction_data=array(
            "id"                        =>$charge_data->id,
            "total_amount"              =>$charge_parsed['amount'],
            "currency"                  =>$charge_parsed['currency'],
            "balance_amount"            =>0,
            "origin_time"               =>$trans_time
                        );
        if(0===count(get_post_meta($order_id, '_eh_stripe_payment_balance')))
        {
            if($charge_parsed['captured']==='Captured')
            {
                $tranaction_data['balance_amount']=$charge_parsed['amount'];
            }
            add_post_meta($order_id, '_eh_stripe_payment_balance',$tranaction_data);
        }
        else
        {
            $tranaction_data['balance_amount']=$charge_parsed['amount'];
            update_post_meta($order_id, '_eh_stripe_payment_balance',$tranaction_data);
        }
        return $charge_parsed;
    }
    public function make_refund_params($refund_value,$amount,$currency,$order_id)
    {
        $refund_data =  json_decode(json_encode($refund_value));
        $origin_time=date('Y-m-d H:i:s',time()+get_option( 'gmt_offset' ) * 3600);
        $refund_parsed = array(
            "id"              => $refund_data->id,
            "object"          => $refund_data->object,
            "amount"          => $this->reset_stripe_amount($refund_data->amount,$refund_data->currency),
            "transaction_id"  => $refund_data->balance_transaction,
            "currency"        => strtoupper($refund_data->currency),
            "order_amount"    => $amount,
            "order_currency"  => $currency,
            "metadata"        => $refund_data->metadata,
            "created"         => date('Y-m-d H:i:s', $refund_data->created+get_option( 'gmt_offset' ) * 3600),
            "charge_id"       => $refund_data->charge,
            "receiptnumber"   => (null==$refund_data->receipt_number)?'No Receipt Number':$refund_data->receipt_number,
            "reason"          => $refund_data->reason,
            "status"          => $refund_data->status,
            "origin_time"     => $origin_time
        );
        $trans_time=date('Y-m-d H:i:s',time()+((get_option( 'gmt_offset' ) * 3600)+10));
        $transaction_data=get_post_meta($order_id, '_eh_stripe_payment_balance',true);
        $transaction_data['balance_amount']=  floatval($transaction_data['balance_amount'])-floatval($refund_parsed['amount']);
        $transaction_data['origin_time']=$trans_time;
        update_post_meta($order_id, '_eh_stripe_payment_balance', $transaction_data);
        return $refund_parsed;
    }
    public function process_payment( $order_id) 
    {
        try 
        {
            $token      = sanitize_text_field($_POST['eh_stripe_pay_token']);
            $currency   = sanitize_text_field($_POST['eh_stripe_pay_currency']);
            $amount     = sanitize_text_field($_POST['eh_stripe_pay_amount']);
            $wc_order   = wc_get_order($order_id);
            $client     = $this->get_clients_details();
            $charge_response     = \Stripe\Charge::create($this->get_charge_details($wc_order, $token,$client,$currency,$amount));
            $data                = $this->make_charge_params($charge_response,$order_id);
            $utc_time            = '';
            $order_time          = date('Y-m-d H:i:s',time()+get_option( 'gmt_offset' ) * 3600);
            if($charge_response->paid==true)
            {
                $wc_order->payment_complete($data['id']);
                if (!$charge_response->captured) {
                    $wc_order->update_status('on-hold');
                }
                $wc_order->add_order_note(__( 'Payment Status : '.ucfirst($data['status']). ' [ '.$order_time.' ] . Source : '.$data['source_type'].'. Charge Status : '.$data['captured'].(is_null($data['transaction_id'])?'':'. Transaction ID : '.$data['transaction_id']),'woocommerce'));
                WC()->cart->empty_cart();
                add_post_meta( $order_id, '_eh_stripe_payment_charge', $data);
                EH_Stripe_Log::log_update('live',$data,get_bloginfo('blogname').' - Charge - Order #'.$wc_order->get_order_number());
                return array (
                    'result'   => 'success',
                    'redirect' => $this->get_return_url( $wc_order ),
                );                            
            }     
            else
            {
                $wc_order->add_order_note( __( 'Charge '.$data['status']. ' at '.$order_time, 'woocommerce' ) );
                EH_Stripe_Log::log_update('dead',$charge_response,get_bloginfo('blogname').' - Charge - Order #'.$wc_order->get_order_number());
            }
        } 
        catch (Exception $error) 
        {
            $user         = wp_get_current_user();
            $user_detail=array(
                'name'  =>get_user_meta( $user->ID, 'first_name', true ),
                'email' =>$user->user_email,
                'phone' =>get_user_meta( $user->ID, 'billing_phone', true ),
            );
            $oops         = $error->getJsonBody();
            EH_Stripe_Log::log_update('dead',array_merge( $user_detail, $oops ),get_bloginfo('blogname').' - Charge - Order #'.$wc_order->get_order_number());
            return array (
                'result'   => 'failure'
                ); 

        }
    }
    public function process_refund($order_id, $amount = NULL, $reason = '' ) 
    {
        $client     = $this->get_clients_details();
        if($amount > 0 )
        {
            $data               = get_post_meta( $order_id , '_eh_stripe_payment_charge', true );
            $status             = $data['captured'];
            if('Captured'===$status)
            {
                $charge_id          = $data['id'];
                $currency           = $data['currency'];
                $total_amount       = $data['amount'];
                $wc_order           = new WC_Order( $order_id );
                $div = $amount*($total_amount/$wc_order->order_total);
                $refund_params=array(
                                        'amount'        => $this->get_stripe_amount($div, $currency), 
                                        'reason'        => 'requested_by_customer',
                                        'metadata'      => array(
                                                'order_id'          => $wc_order->get_order_number(),
                                                'Total Tax'         => $wc_order->get_total_tax(),
                                                'Total Shipping'    => $wc_order->get_total_shipping(),
                                                'Customer IP'       => $client['IP'],
                                                'Agent'             => $client['Agent'],
                                                'Referer'           => $client['Referer'],
                                                'Reaon for Refund'  => $reason
                                            )
                                    );
                if('Bitcoin'===$data['source_type']||'Alipay'===$data['source_type'])
                {
                    $refund_params['refund_address']=$data['source_id'];
                }
                try
                {
                    $charge_response    = \Stripe\Charge::retrieve($charge_id);
                    $refund_response    = $charge_response->refunds->create($refund_params);
                    if($refund_response)     
                    {
                        $refund_time  = date('Y-m-d H:i:s',time()+get_option( 'gmt_offset' ) * 3600);
                        $data  = $this->make_refund_params($refund_response,$amount,$wc_order->order_currency,$order_id);
                        add_post_meta( $order_id, '_eh_stripe_payment_refund', $data); 
                        $wc_order->add_order_note(__('Reason : '.$reason.'.<br> Amount : '.get_woocommerce_currency_symbol().$amount.'.<br> Status : '.(($data['status']==='succeeded')?'Success':'Failed'). ' [ '.$refund_time.' ] '.(is_null($data['transaction_id'])?'':'<br> Transaction ID : '.$data['transaction_id']),'woocommerce'));
                        EH_Stripe_Log::log_update('live',$data,get_bloginfo('blogname').' - Refund - Order #'.$wc_order->get_order_number());
                        return true;
                    }
                    else
                    {
                        EH_Stripe_Log::log_update('dead',$refund_response,get_bloginfo('blogname').' - Refund Error - Order #'.$wc_order->get_order_number());
                        $wc_order->add_order_note(__('Reason : '.$reason.'.<br> Amount : '.get_woocommerce_currency_symbol().$amount.'.<br> Status : Failed ','woocommerce'));
                        return new WP_Error( 'error', $refund_response->message );
                    }
                } catch (Exception $error) {
                    $oops         = $error->getJsonBody();
                    EH_Stripe_Log::log_update('dead',$oops['error'],get_bloginfo('blogname').' - Refund Error - Order #'.$wc_order->get_order_number());
                    $wc_order->add_order_note(__('Reason : '.$reason.'.<br> Amount : '.get_woocommerce_currency_symbol().$amount.'.<br> Status : '.  $oops['error']['message'],'woocommerce'));
                    return new WP_Error( 'error', $oops['error']['message'] );
                }
            }
            else
            {
                return new WP_Error( 'error', 'Uncaptured Amount cannot be refunded' );
            }
        }
        else
        {
                return false;
        }
    }
    public function file_size($bytes)
    {
        $result=0;
        $bytes = floatval($bytes);
            $arBytes = array(
                0 => array(
                    "UNIT" => "TB",
                    "VALUE" => pow(1024, 4)
                ),
                1 => array(
                    "UNIT" => "GB",
                    "VALUE" => pow(1024, 3)
                ),
                2 => array(
                    "UNIT" => "MB",
                    "VALUE" => pow(1024, 2)
                ),
                3 => array(
                    "UNIT" => "KB",
                    "VALUE" => 1024
                ),
                4 => array(
                    "UNIT" => "B",
                    "VALUE" => 1
                ),
            );

        foreach($arBytes as $arItem)
        {
            if($bytes >= $arItem["VALUE"])
            {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", "." , strval(round($result, 2)))." ".$arItem["UNIT"];
                break;
            }
        }
        return $result;
    }
}