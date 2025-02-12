<?php
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

?>