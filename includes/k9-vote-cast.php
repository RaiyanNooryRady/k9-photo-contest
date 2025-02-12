<?php
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

?>