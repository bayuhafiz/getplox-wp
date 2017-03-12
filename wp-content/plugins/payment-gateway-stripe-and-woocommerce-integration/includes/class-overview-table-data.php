<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Eh_Stripe_Order_Datatables extends WP_List_Table
{
    public $order_data;
    function __construct()
    {
        parent::__construct(array(
            'singular' => 'Order',
            'plural' => 'Orders',
            'ajax' => true
        ));
    }
    public function input()
    {
        $order_id=  eh_stripe_overview_get_order_ids();
        $order_temp=array();
        for($i=0;$i<count($order_id);$i++)
        {
           $order= wc_get_order($order_id[$i]);
           $data= get_post_meta( $order_id[$i] , '_eh_stripe_payment_charge',true);
           $order_temp[$i]['order_id']=$order_id[$i];
           $order_temp[$i]['order_status']=$order->get_status();
           $order_temp[$i]['user_id']=($order->get_user_id())?$order->get_user_id():'guest';
           if($order_temp[$i]['user_id']==='guest')
           {
               $order_temp[$i]['user_name']='Guest';
           }
           else
           {
               $order_temp[$i]['user_name']=  get_user_meta($order->get_user_id(), 'first_name',true).' '.get_user_meta($order->get_user_id(), 'last_name',true);
           }
           $order_temp[$i]['user_email']=  $order->billing_email;
           $order_temp[$i]['ship']=$order->get_address('shipping');
           $order_temp[$i]['order_total']=$order->get_total();
           $order_temp[$i]['order_mode']=$data['mode'];
           $order_temp[$i]['refund_rem']=$order->get_remaining_refund_amount();
           $order_temp[$i]['price']=$order->get_formatted_order_total();
           $order_temp[$i]['date']=date('Y-m-d ', strtotime($order->order_date));
        }
        $this->order_data=$order_temp;
    }
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['plural'],  
            $item['order_id']
        );
    }
    function column_order_status($item)
    {
        return sprintf('<mark class="'.$item['order_status'].' tips" title='.ucfirst($item['order_status']).' >'.  ucfirst($item['order_status']).'</mark>');
    }
    function column_order($item)
    {
        return sprintf('<span><a href="'.get_admin_url().'post.php?post='.$item['order_id'].'&action=edit"><strong>#'.$item['order_id'].'</strong></a> by <a href="'.get_admin_url().'user-edit.php?user_id='.$item['user_id'].'">'.$item['user_name'].'</a><br>'.$item['user_email'].'</span>');
    }
    function column_ship($item)
    {

        return sprintf('<span>'.$item['ship']['first_name'].' '.$item['ship']['last_name'].', '.$item['ship']['company'].', '.$item['ship']['address_1'].', '.$item['ship']['address_2'].', '.$item['ship']['city'].', '.$item['ship']['state'].' - '.$item['ship']['postcode'].', '.$item['ship']['country'].'</span>');
    }
    function column_price($item)
    {

        return sprintf('<span>'.$item['price'].'</span>');
    }
    function column_order_actions($item)
    {
        $actions='';
        switch ($item['order_status'])
        {
            case 'pending':
            case 'on-hold':
                $actions='<p><span style="width:45%%" class="button processing order_act processing_button" id="'.$item['order_id'].'" title="Processing">Processing</span><span style="width:45%%" class="button complete order_act complete_button" id="'.$item['order_id'].'" title="completed">Completed</span></p>';
                break;
            case 'processing':
                $actions='<span style="width:98%%" class="button complete_button complete order_act" id="'.$item['order_id'].'" title="completed">Completed</span>';
                break;
            default :
                $actions='<span></span>';
        }
        return sprintf($actions);
    }
    function column_date($item)
    {
        $actions='';
        if($item['order_mode']==='Test')
        {
            $actions='<br><strong style="color:orangered">TEST MODE</strong>';
        }
        return sprintf('<span>'.$item['date'].'</span>'.$actions);
    }
    function get_columns()
    {

        return $columns = array(
            'cb'        => '<input type="checkbox" />',
            'order_status' => '<span class="status_head tips">Status</span>',
            'order' => 'Order',
            'ship' => 'Ship to',
            'price' => 'Price',
            'order_actions' => 'Actions',
            'date'=>'Date'
        );
    }

    function get_sortable_columns()
    {
        $sortable_columns = array();
        return $sortable_columns; 
    }
    function get_bulk_actions()
    {
        $actions = array(
            'processing' => __('Mark Processing','eh-stripe-gateway'),
            'on-hold' => __('Mark On-Hold','eh-stripe-gateway'),
            'completed' => __('Mark Completed','eh-stripe-gateway')
        );
        return $actions;
    }


    function prepare_items($page_num = '', $prepare = '')
    {
        $per_page              = (get_option('eh_order_table_row')) ? get_option('eh_order_table_row') : 20;
        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array(
            $columns,
            $hidden,
            $sortable
        );
        $data         = $this->order_data;
        $current_page = ($page_num == '') ? $this->get_pagenum() : $page_num;
        $total_items  = count($data);
        $data         = array_slice($data, (($current_page - 1) * $per_page), $per_page);
        $this->items  = $data;
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

    function display()
    {
        parent::display();
    }
    function ajax_response($page_num='')
    {
        
        $this->prepare_items($page_num);

        extract($this->_args);
        extract($this->_pagination_args, EXTR_SKIP);

        ob_start();
        if (!empty($_REQUEST['no_placeholder'])) {
            $this->display_rows();
        } else {
            $this->display_rows_or_placeholder();
        }
        $rows = ob_get_clean();

        ob_start();
        $this->print_column_headers();
        $headers = ob_get_clean();

        ob_start();
        $this->pagination('top');
        $pagination_top = ob_get_clean();

        ob_start();
        $this->pagination('bottom');
        $pagination_bottom = ob_get_clean();

        $response                         = array(
            'rows' => $rows
        );
        $response['pagination']['top']    = $pagination_top;
        $response['pagination']['bottom'] = $pagination_bottom;
        $response['column_headers']       = $headers;

        if (isset($total_items)) {
            $response['total_items_i18n'] = sprintf(_n('1 item', '%s items', $total_items), number_format_i18n($total_items));
        }

        if (isset($total_pages)) {
            $response['total_pages']      = $total_pages;
            $response['total_pages_i18n'] = number_format_i18n($total_pages);
        }

        die(json_encode($response));
    }
}

class Eh_Stripe_Datatables extends WP_List_Table
{
    public $stripe_data;
    function __construct()
    {
        parent::__construct(array(
            'singular' => 'Stripe',
            'plural' => 'Stripe',
            'ajax' => true
        ));
    }
    public function input()
    {
        $order_id=  eh_stripe_overview_get_order_ids();
        $stripe_temp=array();
        for($i=0,$j=0;$i<count($order_id);$i++)
        {
            $charge_count=count(get_post_meta( $order_id[$i] , '_eh_stripe_payment_charge'));
            $refund_count=count(get_post_meta( $order_id[$i] , '_eh_stripe_payment_refund'));
            $balance_count=count(get_post_meta( $order_id[$i] , '_eh_stripe_payment_balance'));
            for($k=0;$k<$charge_count;$k++)
            {
                $data= get_post_meta( $order_id[$i] , '_eh_stripe_payment_charge');
                $order= wc_get_order($order_id[$i]);
                $stripe_temp[$j]['order_id']=$order_id[$i];
                $stripe_temp[$j]['stripe_way']='Charge';
                $stripe_temp[$j]['order_status']=$order->get_status();
                $stripe_temp[$j]['user_id']=($order->get_user_id())?$order->get_user_id():'guest';
                if($stripe_temp[$j]['user_id']==='guest')
                {
                    $stripe_temp[$j]['user_name']='Guest';
                }
                else
                {
                    $stripe_temp[$j]['user_name']=  get_user_meta($order->get_user_id(), 'first_name',true).' '.get_user_meta($order->get_user_id(), 'last_name',true);
                }
                $stripe_temp[$j]['user_email']=  $order->billing_email;
                $stripe_temp[$j]['type']=$data[$k]['captured'];
                $stripe_temp[$j]['source']=$data[$k]['source_type'];
                $stripe_temp[$j]['transaction_id']=$data[$k]['transaction_id'];
                $stripe_temp[$j]['status']=$data[$k]['status'];
                $stripe_temp[$j]['amount']=$data[$k]['amount'];
                $stripe_temp[$j]['amount_refunded']=$data[$k]['amount_refunded'];
                $stripe_temp[$j]['currency']= $data[$k]['currency'];
                $stripe_temp[$j]['created']=$data[$k]['origin_time'];
                $j++;
            }
            for($k=0;$k<$refund_count;$k++)    
            {
                $data= get_post_meta( $order_id[$i] , '_eh_stripe_payment_refund');
                $order= wc_get_order($order_id[$i]);
                $stripe_temp[$j]['order_id']=$order_id[$i];
                $stripe_temp[$j]['stripe_way']='Refund';
                $stripe_temp[$j]['order_status']=$order->get_status();
                $stripe_temp[$j]['order_total']=$order->order_total;
                $stripe_temp[$j]['user_id']=($order->get_user_id())?$order->get_user_id():'guest';
                if($stripe_temp[$j]['user_id']==='guest')
                {
                    $stripe_temp[$j]['user_name']='Guest';
                }
                else
                {
                    $stripe_temp[$j]['user_name']=  get_user_meta($order->get_user_id(), 'first_name',true).' '.get_user_meta($order->get_user_id(), 'last_name',true);
                }
                $stripe_temp[$j]['user_email']=  $order->billing_email;
                $stripe_temp[$j]['transaction_id']=$data[$k]['transaction_id'];
                $stripe_temp[$j]['status']=$data[$k]['status'];
                $stripe_temp[$j]['amount']=$data[$k]['amount'];
                $stripe_temp[$j]['currency']= $data[$k]['currency'];
                $stripe_temp[$j]['created']=$data[$k]['origin_time'];
                $j++;
            }
            for($k=0;$k<$balance_count;$k++)    
            {
                $data= get_post_meta( $order_id[$i] , '_eh_stripe_payment_balance');
                $order= wc_get_order($order_id[$i]);
                $stripe_temp[$j]['order_id']=$order_id[$i];
                $stripe_temp[$j]['stripe_way']='Balance';
                $stripe_temp[$j]['order_status']=$order->get_status();
                $stripe_temp[$j]['order_total']=$order->order_total;
                $stripe_temp[$j]['user_id']=($order->get_user_id())?$order->get_user_id():'guest';
                if($stripe_temp[$j]['user_id']==='guest')
                {
                    $stripe_temp[$j]['user_name']='Guest';
                }
                else
                {
                    $stripe_temp[$j]['user_name']=  get_user_meta($order->get_user_id(), 'first_name',true).' '.get_user_meta($order->get_user_id(), 'last_name',true);
                }
                $stripe_temp[$j]['user_email']=  $order->billing_email;
                $stripe_temp[$j]['transaction_id']='Balance Transaction';
                $stripe_temp[$j]['status']='succeeded';
                $stripe_temp[$j]['amount']=$data[$k]['balance_amount'];
                $stripe_temp[$j]['currency']= $data[$k]['currency'];
                $stripe_temp[$j]['created']=$data[$k]['origin_time'];
                $j++;
            }
        }
        $this->stripe_data=$stripe_temp;
    }
    function column_order_status($item)
    {
        return sprintf('<mark class="'.$item['order_status'].' tips" title='.ucfirst($item['order_status']).' >'.  ucfirst($item['order_status']).'</mark>');
    }
    function column_order($item)
    {
        return sprintf('<span><a href="'.get_admin_url().'post.php?post='.$item['order_id'].'&action=edit"><strong>#'.$item['order_id'].'</strong></a> by <a href="'.get_admin_url().'user-edit.php?user_id='.$item['user_id'].'">'.$item['user_name'].'</a><br>'.$item['user_email'].'</span>');
    }
    function column_id($item)
    {
        return sprintf('<span>'.(is_null($item['transaction_id'])?'-':$item['transaction_id']).'</span>');
    }
    function column_status($item)
    {
        $actions='';
        switch($item['stripe_way'])
        {
            case 'Charge':
                if($item['type']==='Captured')
                {
                    $actions='<span class="table-type-text" style="color:#7ad03a !important">Payment Complete</span>';
                }
                break;
            case 'Refund':
                if($item['amount']===$item['order_total'])
                {
                    $actions='<span class="table-type-text" style="color:#39beef !important">Fully Refunded</span>';
                }
                else
                {
                    $actions='<span class="table-type-text">Partially Refunded</span>';
                }
                break;
            case 'Balance':
                $actions='<span class="table-type-text" style="color:#7ad03a !important">Transaction Successful</span>';
                break;
        }
        return sprintf($actions);
    }
    function column_amount($item)
    {
        switch ($item['stripe_way'])
        {
            case 'Charge':
                $actions='<span class="table-type-text">Amount</span><br> ' .  get_woocommerce_currency_symbol(strtoupper($item['currency'])). ' ' . $item['amount']. ' ' .strtoupper($item['currency']).(($item['amount_refunded']!=0)?'<br><span class="table-type-text">Refunded : </span> ' .get_woocommerce_currency_symbol(strtoupper($item['currency'])). ' ' . $item['amount_refunded']. ' ' .strtoupper($item['currency']):'');
                break;
            case 'Refund':
                $actions='<span class="table-type-text">Refund</span><br>' .get_woocommerce_currency_symbol(strtoupper($item['currency'])). ' ' . $item['amount']. ' ' .strtoupper($item['currency']);
                break;
            case 'Balance':
                $actions='<span class="table-type-text">Balance</span><br>' .get_woocommerce_currency_symbol(strtoupper($item['currency'])). ' ' . $item['amount']. ' ' .strtoupper($item['currency']);
                break;
        }
        return sprintf($actions);
    }
    function column_date($item)
    {
        return sprintf('<span>'.$item['created'].'</span>');
    }
    function column_thumb($item)
    {
        $ext   = version_compare( WC()->version, '2.6', '>=' ) ? '.svg' : '.png';
        $style = version_compare( WC()->version, '2.6', '>=' ) ? 'style="margin-left: 0.3em"' : '';
        $icon='';
        if($item['stripe_way']==='Charge')
        {
            if (strpos($item['source'], 'Visa') !== false) {
                $icon ='<img src="' . WC_HTTPS::force_https_url(WC()->plugin_url() . '/assets/images/icons/credit-cards/visa' . $ext) . '" alt="Visa" width="32" title="VISA" ' . $style . ' />';   
            }
            if (strpos($item['source'], 'MasterCard') !== false) {
                $icon = '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/mastercard' . $ext ) . '" alt="Mastercard" width="32" title="Master Card" ' . $style . ' />';
            }
            if (strpos($item['source'], 'American Express') !== false) {
                $icon = '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/amex' . $ext ) . '" alt="Amex" width="32" title="American Express" ' . $style . ' />';
            }
            if (strpos($item['source'], 'Discover') !== false) {
                $icon = '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/discover' . $ext ) . '" alt="Discover" width="32" title="Discover" ' . $style . ' />';
            }
            if (strpos($item['source'], 'JCB') !== false) {
                $icon = '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/jcb' . $ext ) . '" alt="JCB" width="32" title="JCB" ' . $style . ' />';
            }
            if (strpos($item['source'], 'Diners Club') !== false) {
                $icon = '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/diners' . $ext ) . '" alt="Diners" width="32" title="Diners Club" ' . $style . ' />';
            }

            if (strpos($item['source'], 'Bitcoin') !== false) {
                    $icon = '<img src="' . WC_HTTPS::force_https_url( EH_STRIPE_MAIN_URL_PATH. 'assets/img/bitcoin.png') . '" alt="Bitcoin" width="52" title="Bitcoin" ' . $style . ' />';
            }
            if (strpos($item['source'], 'Alipay') !== false) {
                    $icon = '<img src="' . WC_HTTPS::force_https_url( EH_STRIPE_MAIN_URL_PATH. 'assets/img/alipay.png' ) . '" alt="Alipay" width="52" title="Alipay" ' . $style . ' />';
            }
        }
        return sprintf($icon);
    }
    function get_columns()
    {

        return $columns = array(
            'thumb' => '<span class="wc-image">Image</span>',
            'order_status' => '<span class="status_head tips">Status</span>',
            'order' => 'Order',
            'id' => 'Transaction ID',
            'status' => 'Status',
            'amount' => 'Amount',
            'date'=>'Date'
        );
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
             );
        return $sortable_columns; 
    }
    function date_compare($a, $b)
    {
        $t1 = strtotime($a['created']);
        $t2 = strtotime($b['created']);
        return $t1 < $t2?1:-1;
    }    
    function prepare_items($page_num = '', $prepare = '', $page_count = '')
    {
        $per_page              = (get_option('eh_stripe_table_row')) ? get_option('eh_stripe_table_row') : 20;
        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array(
            $columns,
            $hidden,
            $sortable
        );
        $data         = $this->stripe_data;
        usort($data, array($this,'date_compare'));
        $current_page = ($page_num == '') ? $this->get_pagenum() : $page_num;
        $total_items  = count($data);
        $data         = array_slice($data, (($current_page - 1) * $per_page), $per_page);
        $this->items  = $data;
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

    function display()
    {
        parent::display();
    }
    function ajax_response()
    {
        
        $this->prepare_items();

        extract($this->_args);
        extract($this->_pagination_args, EXTR_SKIP);

        ob_start();
        if (!empty($_REQUEST['no_placeholder'])) {
            $this->display_rows();
        } else {
            $this->display_rows_or_placeholder();
        }
        $rows = ob_get_clean();

        ob_start();
        $this->print_column_headers();
        $headers = ob_get_clean();

        ob_start();
        $this->pagination('top');
        $pagination_top = ob_get_clean();

        ob_start();
        $this->pagination('bottom');
        $pagination_bottom = ob_get_clean();

        $response                         = array(
            'rows' => $rows
        );
        $response['pagination']['top']    = $pagination_top;
        $response['pagination']['bottom'] = $pagination_bottom;
        $response['column_headers']       = $headers;

        if (isset($total_items)) {
            $response['total_items_i18n'] = sprintf(_n('1 item', '%s items', $total_items), number_format_i18n($total_items));
        }

        if (isset($total_pages)) {
            $response['total_pages']      = $total_pages;
            $response['total_pages_i18n'] = number_format_i18n($total_pages);
        }

        die(json_encode($response));
    }
}
function eh_spg_order_ajax_data_callback()
{
    check_ajax_referer('ajax-eh-spg-nonce', '_ajax_eh_spg_nonce');
    $obj = new Eh_Stripe_Order_Datatables();
    $obj->input();
    $obj->ajax_response();
}
add_action('wp_ajax_eh_spg_order_ajax_table_data', 'eh_spg_order_ajax_data_callback');

function eh_spg_stripe_ajax_data_callback()
{
    check_ajax_referer('ajax-eh-spg-nonce', '_ajax_eh_spg_nonce');
    $obj = new Eh_Stripe_Datatables();
    $obj->input();
    $obj->ajax_response();
}
add_action('wp_ajax_eh_spg_stripe_ajax_table_data', 'eh_spg_stripe_ajax_data_callback');

/**
 * This function adds the jQuery script to the plugin's page footer
 */
function eh_spg_admin_header()
{
    $page = (isset($_GET['page'])) ? esc_attr($_GET['page']) : false;
    if ('eh-stripe-overview' != $page)
        return;
    $tab = (!empty($_GET['tab']))? esc_attr($_GET['tab']) : 'orders';
    if($tab==='orders')
    {
        echo '<style type="text/css">';
        echo '.wp-list-table { text-align:center ;}';
        echo 'table th{ text-align:center !important;}';
        echo '.wp-list-table .column-date { width: 10%;}';
        echo '.wp-list-table .column-order_actions { width: 10%; vertical-align:middle;}';
        echo '.wp-list-table .column-price { width: 20%;}';
        echo '</style>';
    }
    else
    {
        echo '<style type="text/css">';
        echo '.wp-list-table { text-align:center ;}';
        echo 'table th{ text-align:center !important;}';
        echo '.wp-list-table .column-date { width: 10%;}';
        echo '.wp-list-table .column-status { width: 15%;}';
        echo '.wp-list-table .column-amount { width: 15%;}';
        echo '</style>';
    }
    
}
function eh_spg_ajax_table_script()
{
    $screen = get_current_screen();
    if( 'woocommerce_page_eh-stripe-overview' != $screen->id )
        return false;
    $tab = (!empty($_GET['tab']))? esc_attr($_GET['tab']) : 'orders';
    if($tab==='orders')
    {
        ?>
        <script type="text/javascript">
            (function(jQuery) {

                list = {
                    init: function() {

                        // This will have its utility when dealing with the page number input
                        var timer;
                        var delay = 500;

                        // Pagination links, sortable link
                        jQuery('.tablenav-pages a, .manage-column.sortable a, .manage-column.sorted a').on('click', function(e) {
                            // We don't want to actually follow these links
                            e.preventDefault();
                            // Simple way: use the URL to extract our needed variables
                            var query = this.search.substring(1);

                            var data = {
                                paged: list.__query(query, 'paged') || '1',
                            };
                            list.update(data);
                        });

                        // Page number input
                        jQuery('input[name=paged]').on('keyup', function(e) {
                            if (13 == e.which)
                                e.preventDefault();

                            // This time we fetch the variables in inputs
                            var data = {
                                paged: parseInt(jQuery('input[name=paged]').val()) || '1',
                            };
                            window.clearTimeout(timer);
                            timer = window.setTimeout(function() {
                                list.update(data);
                            }, delay);
                        });
                    },
                    update: function(data) {
                        jQuery("#order_section  .loader").css("display", "block");
                        jQuery.ajax({

                            // /wp-admin/admin-ajax.php
                            url: ajaxurl,
                            // Add action and nonce to our collected data
                            data: jQuery.extend({
                                    _ajax_eh_spg_nonce: jQuery('#_ajax_eh_spg_nonce').val(),
                                    action: 'eh_spg_order_ajax_table_data',
                                },
                                data
                            ),
                            // Handle the successful result
                            success: function(response) {
                                jQuery("#order_section .loader").css("display", "none");
                                // WP_List_Table::ajax_response() returns json
                                var response = jQuery.parseJSON(response);
                                // Add the requested rows
                                if (response.rows.length)
                                    jQuery('#the-list').html(response.rows);
                                // Update column headers for sorting
                                if (response.column_headers.length)
                                    jQuery('thead tr, tfoot tr').html(response.column_headers);
                                // Update pagination for navigation
                                if (response.pagination.bottom.length)
                                    jQuery('.tablenav.top .tablenav-pages').html(jQuery(response.pagination.top).html());
                                if (response.pagination.top.length)
                                    jQuery('.tablenav.bottom .tablenav-pages').html(jQuery(response.pagination.bottom).html());

                                // Init back our event handlers
                                list.init();
                            }
                        });
                    },
                    __query: function(query, variable) {

                        var vars = query.split("&");
                        for (var i = 0; i < vars.length; i++) {
                            var pair = vars[i].split("=");
                            if (pair[0] == variable)
                                return pair[1];
                        }
                        return false;
                    },
                }

                // Show time!
                list.init();

            })(jQuery);
        </script>
        <?php
    }
    else
    {
        ?>
        <script type="text/javascript">
            (function(jQuery) {

                list = {
                    init: function() {

                        // This will have its utility when dealing with the page number input
                        var timer;
                        var delay = 500;

                        // Pagination links, sortable link
                        jQuery('.tablenav-pages a, .manage-column.sortable a, .manage-column.sorted a').on('click', function(e) {
                            // We don't want to actually follow these links
                            e.preventDefault();
                            // Simple way: use the URL to extract our needed variables
                            var query = this.search.substring(1);

                            var data = {
                                paged: list.__query(query, 'paged') || '1',
                            };
                            list.update(data);
                        });

                        // Page number input
                        jQuery('input[name=paged]').on('keyup', function(e) {
                            if (13 == e.which)
                                e.preventDefault();

                            // This time we fetch the variables in inputs
                            var data = {
                                paged: parseInt(jQuery('input[name=paged]').val()) || '1',
                            };
                            window.clearTimeout(timer);
                            timer = window.setTimeout(function() {
                                list.update(data);
                            }, delay);
                        });
                    },
                    update: function(data) {
                        jQuery("#stripe_section  .loader").css("display", "block");
                        jQuery.ajax({

                            // /wp-admin/admin-ajax.php
                            url: ajaxurl,
                            // Add action and nonce to our collected data
                            data: jQuery.extend({
                                    _ajax_eh_spg_nonce: jQuery('#_ajax_eh_spg_nonce').val(),
                                    action: 'eh_spg_stripe_ajax_table_data',
                                },
                                data
                            ),
                            // Handle the successful result
                            success: function(response) {
                                jQuery("#stripe_section .loader").css("display", "none");
                                // WP_List_Table::ajax_response() returns json
                                var response = jQuery.parseJSON(response);
                                // Add the requested rows
                                if (response.rows.length)
                                    jQuery('#the-list').html(response.rows);
                                // Update column headers for sorting
                                if (response.column_headers.length)
                                    jQuery('thead tr, tfoot tr').html(response.column_headers);
                                // Update pagination for navigation
                                if (response.pagination.bottom.length)
                                    jQuery('.tablenav.top .tablenav-pages').html(jQuery(response.pagination.top).html());
                                if (response.pagination.top.length)
                                    jQuery('.tablenav.bottom .tablenav-pages').html(jQuery(response.pagination.bottom).html());

                                // Init back our event handlers
                                list.init();
                            }
                        });
                    },
                    __query: function(query, variable) {

                        var vars = query.split("&");
                        for (var i = 0; i < vars.length; i++) {
                            var pair = vars[i].split("=");
                            if (pair[0] == variable)
                                return pair[1];
                        }
                        return false;
                    },
                }

                // Show time!
                list.init();

            })(jQuery);
        </script>

        <?php
    }
}
function eh_stripe_overview_get_order_ids()
{
    $args = array(
        'post_type'   => 'shop_order',
        'fields' => 'ids',
        'numberposts' => -1,
        'post_status'      => array('wc-pending','wc-processing','wc-on-hold','wc-completed','wc-cancelled','wc-refunded','wc-failed')
    );
    $id=get_posts($args);
    $order_all_id=array();
    for ($i=0,$count=0;$i<count($id);$i++)
    {
        if('eh_stripe_pay'===get_post_meta( $id[$i] , '_payment_method', true ))
        {
            $order_all_id[$count]=$id[$i];
            $count++;
        }                    
    }
    return $order_all_id;
}
?>
<?php
add_action('admin_head', 'eh_spg_admin_header');
add_action('admin_footer', 'eh_spg_ajax_table_script');