<?php
/*
Plugin Name: K9 Photo Contest
Description: A plugin for managing K9 photo contests.
Version: 1.0
Author: B-STA
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin directory.
define('K9_PHOTO_CONTEST_DIR', plugin_dir_path(__FILE__));
define('K9_PHOTO_CONTEST_URL', plugin_dir_url(__FILE__));

// Include necessary files.
include_once K9_PHOTO_CONTEST_DIR . 'includes/k9-cpt.php';
include_once K9_PHOTO_CONTEST_DIR . 'includes/k9-form-handler.php';
include_once K9_PHOTO_CONTEST_DIR . 'includes/k9-post-display.php';

// Enqueue styles and scripts.
function k9_enqueue_scripts() {

    //Enqueue Styles
    wp_enqueue_style('k9-bootstrap-css', K9_PHOTO_CONTEST_URL . 'assets/css/bootstrap.min.css' );
    wp_enqueue_style('k9-styles', K9_PHOTO_CONTEST_URL . 'assets/css/style.css');

    // Enqueue Font Awesome
    wp_enqueue_style('k9-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css', false, '6.4.2', 'all');

    // Enqueue google fonts
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Fira+Sans+Condensed:wght@400;700&family=Open+Sans:wght@400;700&display=swap', false);

    //Enqueue Scripts
    wp_enqueue_script('k9-bootstrap-js', K9_PHOTO_CONTEST_URL . 'assets/js/bootstrap.bundle.min.js');
    wp_enqueue_script('k9-scripts', K9_PHOTO_CONTEST_URL . 'assets/js/script.js', ['jquery'], false, true);    

    //Enqueue Scripts
    wp_enqueue_script('k9-voting', K9_PHOTO_CONTEST_URL . 'assets/js/k9-voting.js', array('jquery'), null, true);
    wp_localize_script('k9-voting', 'k9_voting_ajax', array(
        'ajax_url'    => admin_url('admin-ajax.php'),
        'nonce'       => wp_create_nonce('k9_voting_nonce'),
        'is_logged_in' => is_user_logged_in(), // Pass login status to JavaScript
    ));
}
add_action('wp_enqueue_scripts', 'k9_enqueue_scripts');

// Enqueue Scripts paid
function k9_enqueue_scripts_paid() {

    // Retrieve PayPal credentials
    $paypal_credentials = k9_get_paypal_credentials();
    $client_id = $paypal_credentials['client_id'];

    // Enqueue jQuery and custom script
    /* wp_enqueue_script('jquery');
    wp_enqueue_script('k9-voting', plugin_dir_url(__FILE__) . 'assets/js/k9-voting.js', ['jquery'], null, true); */

    // Include PayPal SDK
    wp_enqueue_script('paypal-sdk', 'https://www.paypal.com/sdk/js?client-id=' . $client_id . '&components=buttons', [], null, true);

    // Pass PayPal Client ID and AJAX URL to JavaScript
    wp_localize_script('k9-voting', 'k9_voting_ajax_paid', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'paypal_client_id' => $client_id
    ]);
}
add_action('wp_enqueue_scripts', 'k9_enqueue_scripts_paid');

function k9_admin_enqueues() {
    //Enqueue admin styles
    wp_enqueue_style('k9-admin-styles', K9_PHOTO_CONTEST_URL . 'assets/admin/css/admin-style.css');
}
add_action('admin_enqueue_scripts','k9_admin_enqueues');

// function for custom template
function custom_k9_submission_template($single_template) {
    global $post;

    // Ensure $post is set before accessing its properties
    if (!isset($post) || empty($post)) {
        return $single_template; // Return default template if no post is available
    }

    if ($post->post_type == 'k9_submission') {
        $plugin_dir = plugin_dir_path(__FILE__);
        $template_path = $plugin_dir . 'templates/single-k9-submission.php';

        if (file_exists($template_path)) {
            return $template_path;
        }
    }

    return $single_template;
}
add_filter('template_include', 'custom_k9_submission_template');



// adding paid voting system through payment gateway paypal =======================================

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

// Shortcode for displaying the purchase vote button
function k9_purchase_votes_button() {
    if (is_user_logged_in()) {
        ob_start();
        ?>
        <div class="k9-purchase-votes-container">
            <button id="purchase-votes" class="purchase-votes">Purchase Votes</button><br/><br/>
            <div id="paypal-button-container"></div>
        </div>
        <?php
        return ob_get_clean();
    } else {
        return '<p>You must be logged in to purchase votes.</p>';
    }
}
add_shortcode('k9_purchase_votes', 'k9_purchase_votes_button');

// Shortcode for displaying the donation button
function k9_donation_button() {
    if (is_user_logged_in()) {
        ob_start();
        ?>
        <div class="k9-donation-container">
            <button id="donation" class="donation">Donation</button><br/><br/>
            <div class="form-check">
                <input type="radio" name="k9_donation" value="Yes" id="k9-donate-yes" class="form-check-input">
                <label for="k9-donate-yes" class="form-check-label">Yes</label>
            </div>
        </div>
        <?php
        return ob_get_clean();
    } else {
        return '<p>You must be logged in to donate</p>';
    }
}
add_shortcode('k9_donation', 'k9_donation_button');

// Shortcode to display the number of purchased votes
function k9_display_purchased_votes() {
    $user_id = get_current_user_id();
    if ($user_id) {
        $purchased_votes = get_user_meta($user_id, 'k9_purchased_votes', true);
        $purchased_votes = empty($purchased_votes) ? 0 : intval($purchased_votes);
        return "<p>Your purchased Vote(s): $purchased_votes</p>";
    } else {
        return '<p>You must be logged in to view your purchased votes.</p>';
    }
}
add_shortcode('k9_display_purchased_votes', 'k9_display_purchased_votes');

// showing purchase vote button and purchased votes
function k9_cast_votes_button($atts) {
    if (is_user_logged_in()) {
        global $post;
        $user_id = get_current_user_id();
        $post_id = $post->ID;

        // Check if the user has already voted for this post
        $has_voted = get_user_meta($user_id, 'k9_voted_post_' . $post_id, true);

        // Get free votes for the post
        $free_votes = intval(get_post_meta($post_id, 'k9_free_votes', true) ?: 0);

        // Get purchased votes for the post
        $purchased_votes = intval(get_post_meta($post_id, 'k9_purchased_votes', true) ?: 0);

        // Calculate total votes (free + purchased)
        $total_votes = $free_votes + $purchased_votes;

        // Get purchased votes for the user
        $user_purchased_votes = get_user_meta($user_id, 'k9_purchased_votes', true);
        $user_purchased_votes = empty($user_purchased_votes) ? 0 : intval($user_purchased_votes);

        // Check if the user has a free vote available for today
        $today = date('Y-m-d');
        $user_daily_votes = get_user_meta($user_id, 'k9_daily_votes', true) ?: [];
        $has_free_vote = !isset($user_daily_votes[$today]);

        // Determine if the user is eligible to vote
        $is_eligible = !$has_voted && ($has_free_vote || $user_purchased_votes > 0);

        ob_start();
        ?>
        <div class="k9-vote-container">
            <p>Available Vote(s): <span id="available-votes"><?php echo esc_html($user_purchased_votes); ?></span></p>
        </div>
        <?php
        return ob_get_clean();
    } else {
        return '<p>You must be logged in to vote.</p>';
    }
}
add_shortcode('k9_cast_votes', 'k9_cast_votes_button');

// free vote handling
function k9_handle_vote() {
    check_ajax_referer('k9_voting_nonce', 'nonce');

    // Check if the user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('You are not authorized to vote. Please log in.');
    }

    $post_id = intval($_POST['post_id']);
    $user_id = get_current_user_id();

    // Check if the user has already voted for this post
    $has_voted = get_user_meta($user_id, 'k9_voted_post_' . $post_id, true);
    if ($has_voted) {
        wp_send_json_error('You have already voted for this post.');
    }

    // Check if the user has a free vote available for today
    $today = date('Y-m-d');
    $user_daily_votes = get_user_meta($user_id, 'k9_daily_votes', true) ?: [];

    if (!isset($user_daily_votes[$today])) {
        // Use the free vote
        $user_daily_votes[$today] = true;
        update_user_meta($user_id, 'k9_daily_votes', $user_daily_votes);
    } else {
        // Check if the user has purchased votes
        $purchased_votes = get_user_meta($user_id, 'k9_purchased_votes', true);
        $purchased_votes = empty($purchased_votes) ? 0 : intval($purchased_votes);

        if ($purchased_votes < 1) {
            wp_send_json_error('Your have no free or purchased vote(s).');
        } else {
            // Deduct one paid vote
            update_user_meta($user_id, 'k9_purchased_votes', $purchased_votes - 1);
        }
    }

    // Mark the user as having voted for this post
    update_user_meta($user_id, 'k9_voted_post_' . $post_id, true);

    // Update the vote count for the post
    $current_votes = intval(get_post_meta($post_id, 'k9_votes', true));
    update_post_meta($post_id, 'k9_votes', $current_votes + 1);

    wp_send_json_success('Vote cast successfully!');
}
add_action('wp_ajax_k9_handle_vote', 'k9_handle_vote');
add_action('wp_ajax_nopriv_k9_handle_vote', 'k9_handle_vote');

// Handle vote casting per post (one vote per post forever)
function k9_cast_vote() {
    if (!isset($_POST['post_id']) || !is_numeric($_POST['post_id'])) {
        wp_send_json_error('Invalid post ID.');
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('You must be logged in to vote.');
    }

    $post_id = intval($_POST['post_id']);
    $has_voted = get_user_meta($user_id, 'k9_voted_post_' . $post_id, true);

    if ($has_voted) {
        wp_send_json_error('You have already voted for this post.');
    }

    // Get user's purchased votes
    $purchased_votes = get_user_meta($user_id, 'k9_purchased_votes', true);
    $purchased_votes = empty($purchased_votes) ? 0 : intval($purchased_votes);

    if ($purchased_votes < 1) {
        wp_send_json_error('You do not have enough votes.');
    }

    // Deduct one vote
    $remaining_votes = $purchased_votes - 1;
    update_user_meta($user_id, 'k9_purchased_votes', $remaining_votes);

    // Store that user has voted for this post
    update_user_meta($user_id, 'k9_voted_post_' . $post_id, 1);

    // Update total votes count for the post
    $total_votes = get_post_meta($post_id, 'k9_total_votes', true);
    $total_votes = empty($total_votes) ? 0 : intval($total_votes);
    update_post_meta($post_id, 'k9_total_votes', $total_votes + 1);

    wp_send_json_success([
        'message' => 'Vote cast successfully!',
        'total_votes' => $total_votes + 1,
        'remaining_votes' => $remaining_votes
    ]);
}
add_action('wp_ajax_k9_cast_vote', 'k9_cast_vote');
add_action('wp_ajax_nopriv_k9_cast_vote', 'k9_cast_vote'); // Ensure logged-in users only

// Shortcode for K9 Leaderboard
function k9_leaderboard_shortcode() {
    ob_start();

    // Get posts ordered by total_votes meta field
    $args = array(
        'post_type'      => 'k9_submission',
        'posts_per_page' => 10, // Get all posts
        'orderby'        => 'meta_value_num',
        'meta_key'       => 'k9_votes', // Assuming votes are stored as post meta
        'order'          => 'DESC',
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $rank = 1;
        $top_posts = [];

        // Collect top 3 posts
        while ($query->have_posts() && $rank <= 3) {
            $query->the_post();
            $top_posts[] = array(
                'id'        => get_the_ID(),
                'title'     => get_the_title(),
                'author'    => get_the_author(),
                'votes'     => get_post_meta(get_the_ID(), 'k9_votes', true) ?: 0,
                'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: 'https://via.placeholder.com/100',
                'link'      => get_permalink(get_the_ID()),
            );
            $rank++;
        }

        // Leaderboard container
        echo '<div class="leaderboard container-xl px-3">';
        echo '<h2 class="text-center mb-4">K9 Leaderboard</h2>';

       // Display Top 3 Posts
if (!empty($top_posts)) {
    echo '<div class="row top-three justify-content-center mb-4">';

    // #2 Post
    if (!empty($top_posts[1])) {
        echo '<div class="col-lg-3 col-md-4 col-sm-12 second-place">';
        echo '<div class="top-post bg-secondary text-light p-3 rounded text-center">';
        echo '<h3>#2 ' . esc_html($top_posts[1]['title']) . '</h3>';
        echo '<p>Author: ' . esc_html($top_posts[1]['author']) . '</p>';
        echo '<a href="' . esc_url($top_posts[1]['link']) . '">';
        echo '<img src="' . esc_url($top_posts[1]['thumbnail']) . '" class="img-fluid rounded-circle mb-3" alt="K9 Image">';
        echo '</a>';
        echo '<p>Total Votes: ' . esc_html($top_posts[1]['votes']) . '</p>';
        echo '</div>';
        echo '</div>';
    }

    // #1 Post
    if (!empty($top_posts[0])) {
        echo '<div class="col-lg-4 col-md-6 col-sm-12 first-place">';
        echo '<div class="top-post bg-primary text-light p-4 rounded text-center">';
        echo '<h2>#1 ' . esc_html($top_posts[0]['title']) . '</h2>';
        echo '<p>Author: ' . esc_html($top_posts[0]['author']) . '</p>';
        echo '<a href="' . esc_url($top_posts[0]['link']) . '">';
        echo '<img src="' . esc_url($top_posts[0]['thumbnail']) . '" class="img-fluid rounded-circle mb-3" alt="K9 Image">';
        echo '</a>';
        echo '<p class="lead">Total Votes: ' . esc_html($top_posts[0]['votes']) . '</p>';
        echo '</div>';
        echo '</div>';
    }

    // #3 Post
    if (!empty($top_posts[2])) {
        echo '<div class="col-lg-3 col-md-4 col-sm-12 third-place">';
        echo '<div class="top-post bg-dark text-light p-2 rounded text-center">';
        echo '<h3>#3 ' . esc_html($top_posts[2]['title']) . '</h3>';
        echo '<p>Author: ' . esc_html($top_posts[2]['author']) . '</p>';
        echo '<a href="' . esc_url($top_posts[2]['link']) . '">';
        echo '<img src="' . esc_url($top_posts[2]['thumbnail']) . '" class="img-fluid rounded-circle mb-3" alt="K9 Image">';
        echo '</a>';
        echo '<p>Total Votes: ' . esc_html($top_posts[2]['votes']) . '</p>';
        echo '</div>';
        echo '</div>';
    }

    echo '</div>'; // End top-three row
}

        // Posts table for rank 4 and beyond
        echo '<div class="table-responsive">';
        echo '<table class="table table-dark table-striped">';
        echo '<thead><tr><th>Rank</th><th>User</th><th>K9 Name</th><th>K9 Photo</th><th>Total Votes</th></tr></thead>';
        echo '<tbody>';

        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $votes = get_post_meta($post_id, 'k9_votes', true) ?: 0;
            $author = get_the_author();
            $title = get_the_title();
            $thumbnail = get_the_post_thumbnail_url($post_id, 'thumbnail') ?: 'https://via.placeholder.com/100';

            echo '<tr>';
            echo '<td>' . $rank . '</td>';
            echo '<td>' . esc_html($author) . '</td>';
            echo '<td>' . esc_html($title) . '</td>';
            echo '<td><a href="' . get_permalink($post_id) . '"><img src="' . esc_url($thumbnail) . '" class="img-thumbnail" alt="K9 Image"></a></td>';
            echo '<td>' . esc_html($votes) . '</td>';
            echo '</tr>';

            $rank++;
        }

        echo '</tbody></table>';
        echo '</div>'; // End table-responsive
        echo '</div>'; // End leaderboard container
    } else {
        echo '<p>No submissions found.</p>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('k9_leaderboard', 'k9_leaderboard_shortcode');
