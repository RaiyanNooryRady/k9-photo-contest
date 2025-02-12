
<?php
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
?>