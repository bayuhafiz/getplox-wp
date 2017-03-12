<?php
if (!defined('ABSPATH')) {
    exit;
}
return array(
    'enabled' => array(
        'title' => __('Stripe Payment', 'eh-stripe-gateway'),
        'label' => __('Enable', 'eh-stripe-gateway'),
        'type' => 'checkbox',
        'default' => 'no'
    ),
    
    'overview' => array(
        'title' => __('Stripe Overview Page', 'eh-stripe-gateway'),
        'label' => __('Enable', 'eh-stripe-gateway'),
        'type' => 'checkbox',
        'description' => sprintf(__('<a href="' . admin_url('admin.php?page=eh-stripe-overview') . '"> Stripe Overview </a>', 'eh-stripe-gateway')),
        'default' => 'yes'
    ),
    'title' => array(
        'title' => __('Title', 'eh-stripe-gateway'),
        'type' => 'text',
        'description' => __('Enter the title of the checkout which the user can see.', 'eh-stripe-gateway'),
        'default' => __('Stripe', 'eh-stripe-gateway'),
        'desc_tip' => true
    ),
    'description' => array(
        'title' => __('Description', 'eh-stripe-gateway'),
        'type' => 'textarea',
        'css' => 'width:25em',
        'description' => __('Description which the user sees during checkout.', 'eh-stripe-gateway'),
        'default' => __('Secure payment via Stripe.', 'eh-stripe-gateway'),
        'desc_tip' => true
    ),
    'eh_stripe_order_button' => array(
        'title' => __('Order Button Text', 'eh-stripe-gateway'),
        'type' => 'text',
        'description' => __('Enter the Order Button Text of the payment page.', 'eh-stripe-gateway'),
        'default' => __('Pay via Stripe', 'eh-stripe-gateway'),
        'desc_tip' => true
    ),
    'eh_stripe_checkout_cards' => array(
        'title' => __('Prefered Cards', 'woocommerce'),
        'type' => 'multiselect',
        'class' => 'chosen_select',
        'css' => 'width: 350px;',
        'desc_tip' => __('Select the card types to display the card logo in the checkout page as prefered card.', 'woocommerce'),
        'options' => array(
            'MasterCard' => 'MasterCard',
            'Visa' => 'Visa',
            'American Express' => 'American Express',
            'Discover' => 'Discover',
            'JCB' => 'JCB',
            'Diners Club' => 'Diners Club'
        ),
        'default' => array(
            'MasterCard',
            'Visa',
            'Diners Club',
            'Discover',
            'American Express',
            'JCB'
        )
    ),
    'eh_stripe_credit_title' => array(
        'title' => sprintf(__('<span style="text-decoration: underline;color:brown;">Stripe Credentials<span>', 'eh-stripe-gateway')),
        'type' => 'title'
    ),
    'eh_stripe_mode' => array(
        'title' => __('Transaction Mode', 'eh-stripe-gateway'),
        'type' => 'select',
        'options' => array(
            'test' => __('Test Mode', 'eh-stripe-gateway'),
            'live' => __('Live Mode', 'eh-stripe-gateway')
        ),
        'description' => sprintf(__('Check appropriate Stripe mode is checked in Stripe <a href="https://dashboard.stripe.com/dashboard" target="_blank">Dashboard</a>.', 'eh-stripe-gateway')),
        'default' => 'test'
    ),
    'eh_stripe_test_secret_key' => array(
        'title' => __('Test Secret Key', 'eh-stripe-gateway'),
        'type' => 'text',
        'description' => __('Enter Stripe Test mode Secret Key.', 'eh-stripe-gateway'),
        'placeholder' => 'Test Secret Key',
        'desc_tip' => true
    ),
    'eh_stripe_test_publishable_key' => array(
        'title' => __('Test Publishable Key', 'eh-stripe-gateway'),
        'type' => 'text',
        'description' => __('Enter Stripe Test mode Publishable Key.', 'eh-stripe-gateway'),
        'placeholder' => 'Test Publishable Key',
        'desc_tip' => true
    ),
    'eh_stripe_live_secret_key' => array(
        'title' => __('Live Secret Key', 'eh-stripe-gateway'),
        'type' => 'text',
        'description' => __('Enter Stripe Live mode Publishable Key.', 'eh-stripe-gateway'),
        'placeholder' => 'Live Secret Key',
        'desc_tip' => true
    ),
    'eh_stripe_live_publishable_key' => array(
        'title' => __('Live Publishable Key', 'eh-stripe-gateway'),
        'type' => 'text',
        'description' => __('Enter Stripe Live mode Publishable Key.', 'eh-stripe-gateway'),
        'placeholder' => 'Live Publishable Key',
        'desc_tip' => true
    ),
    'eh_stripe_pay_actions_title' => array(
        'title' => sprintf(__('<span style="text-decoration: underline;color:brown;">Stripe Actions<span>', 'eh-stripe-gateway')),
        'type' => 'title'
    ),
    'eh_stripe_email_receipt' => array(
        'title' => __('Email Transaction Receipt', 'eh-stripe-gateway'),
        'label' => __('Enable ', 'eh-stripe-gateway'),
        'type' => 'checkbox',
        'description' => __('If enabling Email Transaction Receipt, the tranaction recipt will send as email to the customers by Stripe.', 'eh-stripe-gateway'),
        'default' => 'no',
        'desc_tip' => true
    ),
    'eh_stripe_stripe_form_title' => array(
        'title' => sprintf(__('<span style="text-decoration: underline;color:brown;">Stripe Abilities<span>', 'eh-stripe-gateway')),
        'type' => 'title'
    ),
    'eh_stripe_bitcoin' => array(
        'title' => __('Bitcoin Currency', 'eh-stripe-gateway'),
        'label' => __('Enable', 'eh-stripe-gateway'),
        'type' => 'checkbox',
        'description' => __('If enabled, an option to accept bitcoin will show on the checkout modal. Note: Store currency must be set to USD.', 'eh-stripe-gateway'),
        'default' => 'no',
        'desc_tip' => true
    ),
    'eh_stripe_alipay' => array(
        'title' => __('Alipay Currency', 'eh-stripe-gateway'),
        'label' => __('Enable', 'eh-stripe-gateway'),
        'type' => 'checkbox',
        'description' => __('If enabled, an option to accept Alipay will show on the checkout modal. Note: Store currency must be set to USD.', 'eh-stripe-gateway'),
        'default' => 'no',
        'desc_tip' => true
    ),
    'eh_stripe_billing_address_check' => array(
        'title' => __('Ask Billing Address in Stripe', 'eh-stripe-gateway'),
        'label' => __('Enable', 'eh-stripe-gateway'),
        'type' => 'checkbox',
        'description' => __('If enabled, the billing address will be asked to fill in Stripe Payment form.', 'eh-stripe-gateway'),
        'default' => 'no',
        'desc_tip' => true
    ),
    'eh_stripe_checkout_image_check' => array(
        'title' => __('Display Checkout Logo', 'eh-stripe-gateway'),
        'label' => __('Enable', 'eh-stripe-gateway'),
        'type' => 'checkbox',
        'description' => __('If enabled, the logo image provided in the Stripe Checkout Logo will be displayed in the Stripe Checkout', 'eh-stripe-gateway'),
        'default' => 'yes',
        'desc_tip' => true
    ),
    'eh_stripe_checkout_image' => array(
        'title' => __('Stripe Checkout Logo', 'eh-stripe-gateway'),
        'description' => sprintf('<img src="%s" width="128px" height="128px" style="cursor:pointer" title="Click the image to Choose a Stripe Checkout Logo" id="eh_stripe_preview">', ('' == $this->get_option('eh_stripe_checkout_image')) ? EH_STRIPE_MAIN_URL_PATH . "assets/img/stripe.png" : $this->get_option('eh_stripe_checkout_image')),
        'type' => 'text',
        'placeholder' => 'Click the Image to set Logo (Default : Stripe Logo)'
    ),
    'eh_stripe_enable_save_cards' => array(
        'title' => __('Stripe Save Cards', 'eh-stripe-gateway'),
        'label' => __('Enable', 'eh-stripe-gateway'),
        'type' => 'checkbox',
        'description' => __('If enabled, users will be able to check Remember Me option in Stripe checkout for future transactions. Card details are saved on Stripe servers, not on your store.', 'eh-stripe-gateway'),
        'default' => 'no',
        'desc_tip' => true
    ),
    'eh_stripe_log_title' => array(
        'title' => sprintf(__('<span style="text-decoration: underline;color:brown;">Debugging<span>', 'eh-stripe-gateway')),
        'type' => 'title',
        'description' => __('Enable Logging to save Stripe payment logs into log file.', 'eh-stripe-gateway')
    ),
    'eh_stripe_logging' => array(
        'title' => __('Logging', 'eh-stripe-gateway'),
        'label' => __('Enable', 'eh-stripe-gateway'),
        'type' => 'checkbox',
        'description' => sprintf(__('<span style="color:green">Success Log File</span>: ' . strstr(wc_get_log_file_path('eh_stripe_pay_live'), 'wp-content') . ' ( ' . $this->file_size(filesize(wc_get_log_file_path('eh_stripe_pay_live'))) . ' ) <br><span style="color:red">Failure Log File</span >: ' . strstr(wc_get_log_file_path('eh_stripe_pay_dead'), 'wp-content') . ' ( ' . $this->file_size(filesize(wc_get_log_file_path('eh_stripe_pay_dead'))) . ' ) ', 'eh-stripe-gateway')),
        'default' => 'yes'
    )
);
