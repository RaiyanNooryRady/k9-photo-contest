<?php
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
            <div id="paypal-button-container-donation"></div>
        </div>
        <?php
        return ob_get_clean();
    } else {
        return '<p>You must be logged in to donate</p>';
    }
}
add_shortcode('k9_donation', 'k9_donation_button');

?>