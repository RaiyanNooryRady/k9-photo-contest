<?php
// Retrieve PayPal credentials from the database
function k9_get_paypal_credentials() {
    return [
        'client_id' => get_option('k9_paypal_client_id', ''),
        'secret' => get_option('k9_paypal_secret', ''),
        'api_url' => get_option('k9_paypal_api_url', 'https://api-m.sandbox.paypal.com')
    ];
}

// Add a settings page for PayPal API credentials
function k9_paypal_settings_page() {
    add_menu_page(
        'PayPal Settings', // Page title
        'PayPal Settings', // Menu title
        'manage_options',  // Capability
        'k9-paypal-settings', // Menu slug
        'k9_paypal_settings_page_html', // Callback function
        'dashicons-admin-generic', // Icon
        100 // Position
    );
}
add_action('admin_menu', 'k9_paypal_settings_page');

// HTML for the PayPal settings page
function k9_paypal_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save settings if form is submitted
    if (isset($_POST['k9_paypal_settings_nonce'])) {
        if (wp_verify_nonce($_POST['k9_paypal_settings_nonce'], 'k9_paypal_settings')) {
            update_option('k9_paypal_client_id', sanitize_text_field($_POST['k9_paypal_client_id']));
            update_option('k9_paypal_secret', sanitize_text_field($_POST['k9_paypal_secret']));
            update_option('k9_paypal_api_url', sanitize_text_field($_POST['k9_paypal_api_url']));
            echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
        }
    }

    // Retrieve saved settings
    $client_id = get_option('k9_paypal_client_id', '');
    $secret = get_option('k9_paypal_secret', '');
    $api_url = get_option('k9_paypal_api_url', 'https://api-m.sandbox.paypal.com');

    // Display the settings form
    ?>
    <div class="wrap">
        <h1>PayPal Settings</h1>
        <form method="post" action="">
            <?php wp_nonce_field('k9_paypal_settings', 'k9_paypal_settings_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="k9_paypal_client_id">PayPal Client ID</label></th>
                    <td>
                        <input name="k9_paypal_client_id" type="text" id="k9_paypal_client_id" value="<?php echo esc_attr($client_id); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="k9_paypal_secret">PayPal Secret</label></th>
                    <td>
                        <input name="k9_paypal_secret" type="text" id="k9_paypal_secret" value="<?php echo esc_attr($secret); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="k9_paypal_api_url">PayPal API URL</label></th>
                    <td>
                        <input name="k9_paypal_api_url" type="text" id="k9_paypal_api_url" value="<?php echo esc_attr($api_url); ?>" class="regular-text">
                        <p class="description">Use <code>https://api-m.sandbox.paypal.com</code> for sandbox or <code>https://api-m.paypal.com</code> for live.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save Settings'); ?>
        </form>
    </div>
    <?php
}

// Generate PayPal Access Token
function k9_get_paypal_access_token() {
    $paypal_credentials = k9_get_paypal_credentials();
    $client_id = $paypal_credentials['client_id'];
    $secret = $paypal_credentials['secret'];
    $api_url = $paypal_credentials['api_url'];

    if (empty($client_id) || empty($secret)) {
        error_log('PayPal Client ID or Secret is missing.');
        return false;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url . "/v1/oauth2/token");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/json",
        "Accept-Language: en_US"
    ]);
    curl_setopt($ch, CURLOPT_USERPWD, $client_id . ":" . $secret);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        error_log('PayPal API request failed.');
        return false;
    }

    $data = json_decode($response, true);
    if (isset($data['access_token'])) {
        error_log('PayPal Access Token: ' . $data['access_token']);
        return $data['access_token'];
    } else {
        error_log('PayPal Access Token not found in response: ' . print_r($data, true));
        return false;
    }
}

// Create PayPal Order
function k9_create_paypal_order() {
    if (!isset($_POST['votes']) || !is_numeric($_POST['votes'])) {
        wp_send_json_error('Invalid vote amount.');
    }

    $votes = intval($_POST['votes']);
    $total_price = number_format($votes * 1.00, 2, '.', '');

    $access_token = k9_get_paypal_access_token();
    if (!$access_token) {
        wp_send_json_error('Could not retrieve PayPal access token.');
    }

    $paypal_credentials = k9_get_paypal_credentials();
    $api_url = $paypal_credentials['api_url'];

    $order_data = [
        'intent' => 'CAPTURE',
        'purchase_units' => [[
            'amount' => [
                'currency_code' => 'USD',
                'value' => $total_price
            ]
        ]]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url . "/v2/checkout/orders");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $access_token"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    if (isset($result['id'])) {
        wp_send_json_success(['orderID' => $result['id']]);
    } else {
        wp_send_json_error('Failed to create PayPal order.');
    }
}
add_action('wp_ajax_k9_create_paypal_order', 'k9_create_paypal_order');
add_action('wp_ajax_nopriv_k9_create_paypal_order', 'k9_create_paypal_order');

// Capture PayPal Order
function k9_capture_paypal_order() {
    if (!isset($_POST['orderID'])) {
        wp_send_json_error('Invalid request.');
    }

    $order_id = sanitize_text_field($_POST['orderID']);
    $access_token = k9_get_paypal_access_token();
    if (!$access_token) {
        wp_send_json_error('Could not retrieve PayPal access token.');
    }

    $paypal_credentials = k9_get_paypal_credentials();
    $api_url = $paypal_credentials['api_url'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url . "/v2/checkout/orders/$order_id/capture");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $access_token"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['status']) && $result['status'] == 'COMPLETED') {
        $user_id = get_current_user_id();
        $votes = intval($_POST['votes']);
        $current_votes = get_user_meta($user_id, 'k9_purchased_votes', true);
        update_user_meta($user_id, 'k9_purchased_votes', intval($current_votes) + $votes);

        wp_send_json_success('Payment successful! ' . $votes . ' votes added.');
    } else {
        wp_send_json_error('Payment verification failed.');
    }
}
add_action('wp_ajax_k9_capture_paypal_order', 'k9_capture_paypal_order');
add_action('wp_ajax_nopriv_k9_capture_paypal_order', 'k9_capture_paypal_order');

?>