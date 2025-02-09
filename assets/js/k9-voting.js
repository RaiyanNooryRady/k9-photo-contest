jQuery(document).ready(function($) {

    $('#paypal-button-container-donation').hide();

    $('.k9-card-vote-now').on('click', function(e) {
        e.preventDefault();

        var button = $(this);
        var post_id = button.data('post-id');

        // AJAX request to handle voting
        $.ajax({
            url: k9_voting_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'k9_handle_vote',
                post_id: post_id,
                nonce: k9_voting_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update button to "Voted!" and disable it
                    button.attr('disabled', true).text('Voted!');
                    alert(response.data); // Show success message
                    location.reload(); // Reload the page to reflect the updated vote count
                } else {
                    alert(response.data); // Show error message
                }
            },
            error: function() {
                alert('An error occurred while processing your vote.');
            }
        });
    });

    //purchasing votes through paypal functionality
    $('.purchase-votes').on('click', function() {
        var votes = prompt("Enter the number of votes you want to purchase:");
        if (!votes || isNaN(votes) || votes < 1) {
            alert("Please enter a valid number of votes.");
            return;
        }

        $.ajax({
            url: k9_voting_ajax_paid.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'k9_create_paypal_order',
                votes: votes
            },
            success: function(response) {
                if (response.success) {
                    $('#paypal-button-container').html(''); // Clear previous button
                    paypal.Buttons({
                        createOrder: function(data, actions) {
                            return response.data.orderID;
                        },
                        onApprove: function(data, actions) {
                            return $.ajax({
                                url: k9_voting_ajax_paid.ajax_url,
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    action: 'k9_capture_paypal_order',
                                    orderID: data.orderID,
                                    votes: votes
                                },
                                success: function(response) {
                                    if (response.success) {
                                        alert(response.data);
                                        location.reload();
                                    } else {
                                        alert("Payment verification failed.");
                                    }
                                }
                            });
                        }
                    }).render('#paypal-button-container');
                } else {
                    alert("Error: " + response.data);
                }
            }
        });
    });

    //donation through paypal functionality
    $('#k9-donate-yes').on('click', function() {
        var votes = prompt("Enter the amount you want to donate.");
        if (!votes || isNaN(votes) || votes < 1) {
            alert("Please enter a valid amount to donate.");
            return;
        }

        $.ajax({
            url: k9_voting_ajax_paid.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'k9_create_paypal_order',
                votes: votes
            },
            success: function(response) {
                if (response.success) {
                    $('#paypal-button-container-donation').show().html(''); // Clear previous button
                    paypal.Buttons({
                        createOrder: function(data, actions) {
                            return response.data.orderID;
                        },
                        onApprove: function(data, actions) {
                            return $.ajax({
                                url: k9_voting_ajax_paid.ajax_url,
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    action: 'k9_capture_paypal_order',
                                    orderID: data.orderID,
                                    votes: votes
                                },
                                success: function(response) {
                                    if (response.success) {
                                        alert(response.data);
                                        location.reload();
                                    } else {
                                        alert("Payment verification failed.");
                                    }
                                }
                            });
                        }
                    }).render('#paypal-button-container-donation');
                } else {
                    alert("Error: " + response.data);
                }
            }
        });
    });

    // When "No" is clicked, hide PayPal interface
    $('#k9-donate-no').on('click', function() {
        $('#paypal-button-container-donation').hide();
    });


});