<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class='wrapper' id='eh_stripe_overview'>
    <div style='height: 50%;position:relative' id='analytics'>
        <div class="loader" style="padding-top: 10px">
            <span style="position: absolute; width: 100%; vertical-align: middle; text-align: center; margin: 15% 0px;">
                <h2>
                    <?php _e('Please Wait ...', 'eh-stripe-gateway'); ?>
                </h2>
            </span>
        </div>
        
        <div style="width:30%;" class="top-analytics">
            
                <div class="status-box status-box-main">
                    <h3 style="float: left;"> Overview of Stripe </h3><h4 style="float: left;padding-left: 5px;"> ( <a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=checkout&section=eh_stripe_pay'); ?>">Stripe Settings</a> ) </h4><hr>
                    <ul class="details">
                        <?php $data=  eh_stripe_overview_get_captured_uncaptured_amount(); ?>
                        <li><span id="captured_status">Captured   : <?php echo get_woocommerce_currency_symbol().$data['cap']; echo ' '.  get_woocommerce_currency();?> </span></li><br>
                        <li><span id="uncaptured_status">Uncaptured : <?php echo get_woocommerce_currency_symbol().$data['uncap']; echo ' '.  get_woocommerce_currency();?> </span></li><br>
                        <li><span id="refund_status">Refund : <?php echo get_woocommerce_currency_symbol().eh_stripe_overview_get_refund_amount(); echo ' '.  get_woocommerce_currency();?> </span></li>
                    </ul>

                    <form class="form-horizontal">
                         <fieldset>
                             <div class="input-prepend">
                               <span class="add-on"><i class="icon-calendar"></i></span><input type="text" name="range" id="range" />
                             </div>
                         </fieldset>
                     </form>
                     <h4> Provide date range for displaying overview. </h4>
                </div> 
        </div>
        <div style="width:63%;" class="top-analytics">
            <div class="status-box status-box-main" id='chart'>
                
            </div>
        </div>
        </div>
    </div>
    <br>
<script>
    jQuery('#eh_stripe_overview .input-daterange').datepicker({        
    orientation: "auto",
    endDate: "today",
    clearBtn: true,
    autoclose: true,
    todayHighlight: true
});
</script>
<div>
<?php
function eh_stripe_overview_get_captured_uncaptured_amount()
{
    $amount=array(
        'cap' => 0,
        'uncap'=>0
    );
    $id=  eh_stripe_overview_get_order_ids();
    for($i=0;$i<count($id);$i++)
    {
        $data=get_post_meta( $id[$i] , '_eh_stripe_payment_charge', true );
        if($data!=='')
        {
            if('succeeded'===$data['status'])
            {
                switch($data['captured'])
                {
                    case 'Captured':
                        $amount['cap']+=$data['order_amount'];
                        break;
                    case 'Uncaptured':
                        $amount['uncap']+=$data['order_amount'];
                        break;
                }
            }
        }
    }
    return $amount;
}
function eh_stripe_overview_get_refund_amount()
{
    $amount=0;
    $id=  eh_stripe_overview_get_order_ids();
    for($i=0;$i<count($id);$i++)
    {
        $data=get_post_meta( $id[$i] , '_eh_stripe_payment_refund');
        if($data!=='')
        {
            for($j=0;$j<count($data);$j++)
            {            
                if('succeeded'===$data[$j]['status'])
                {
                    $amount+=$data[$j]['order_amount'];
                }
            }
        }
    }
    return $amount;
}
function eh_stripe_overview_page_tabs($current = 'orders') {
    $tabs = array(
        'orders'   => __("Order Details", 'eh-stripe-payment'), 
        'stripe'  => __("Transaction Details", 'eh-stripe-payment')
    );
    $html =  '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $tab => $name ){
        $class = ($tab == $current) ? 'nav-tab-active' : '';
        $html .=  '<a class="nav-tab ' . $class . '" href="?page=eh-stripe-overview&tab=' . $tab . '">' . $name . '</a>';
    }
    $html .= '</h2>';
    echo $html;
}
$tab = (!empty($_GET['tab']))? esc_attr($_GET['tab']) : 'orders';
eh_stripe_overview_page_tabs($tab);

if($tab == 'orders' ) {
    ?>
    <div class="table-box table-box-main" id='order_section'>
        <div class="loader">
        </div>
        <?php include 'template-order-overview.php'; ?>
    </div>
    <?php
}
else {
    ?>
    <div class="table-box table-box-main" id='stripe_section'>
        <div class="loader">
        </div>
        <?php include 'template-stripe-overview.php'; ?>
    </div>    
    <?php
}
// Code after the tabs (outside)
?>
</div>