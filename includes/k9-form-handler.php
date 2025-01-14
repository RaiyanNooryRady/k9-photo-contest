<?php
// Shortcode for the K9 submission form.
function k9_submission_form_shortcode() {
    ob_start(); ?>
    <form id="k9-submission-form" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('k9_submission_form', 'k9_nonce'); ?>
        <label for="k9-name">K9 Name:</label>
        <input type="text" name="k9_name" id="k9-name" required>

        <label for="k9-photo">Upload Photo:</label>
        <input type="file" name="k9_photo" id="k9-photo" accept="image/*" required>

        <label for="k9-owner">Owner Name:</label>
        <input type="text" name="k9_owner" id="k9-owner" required>

        <label for="k9-memory">Favorite Memory:</label>
        <textarea name="k9_memory" id="k9-memory" required></textarea>

        <input type="hidden" name="action" value="k9_submit_form">
        <button type="submit">Submit</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('k9_submission_form', 'k9_submission_form_shortcode');

// Handle form submission.
function k9_handle_form_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'k9_submit_form') {
        // Verify nonce for security.
        if (!isset($_POST['k9_nonce']) || !wp_verify_nonce($_POST['k9_nonce'], 'k9_submission_form')) {
            wp_die('Security check failed.');
        }

        // Include necessary WordPress files.
        if (!function_exists('media_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        // Sanitize and retrieve form data.
        $k9_name = sanitize_text_field($_POST['k9_name']);
        $k9_owner = sanitize_text_field($_POST['k9_owner']);
        $k9_memory = sanitize_textarea_field($_POST['k9_memory']);

        // Handle photo upload.
        $photo_id = 0;
        if (!empty($_FILES['k9_photo']['name'])) {
            $uploaded = media_handle_upload('k9_photo', 0);

            if (is_wp_error($uploaded)) {
                wp_die('Photo upload failed: ' . $uploaded->get_error_message());
            }

            $photo_id = $uploaded;
        }

        // Create a new post.
        $post_id = wp_insert_post([
            'post_title'   => $k9_name,
            'post_content' => $k9_memory,
            'post_status'  => 'draft',
            'post_type'    => 'k9_submission',
            'meta_input'   => [
                'k9_owner' => $k9_owner,
            ],
        ]);

        if (is_wp_error($post_id)) {
            wp_die('Post creation failed: ' . $post_id->get_error_message());
        }

        // Assign the uploaded photo as the featured image.
        if ($photo_id) {
            set_post_thumbnail($post_id, $photo_id);
        }

        // Redirect to a thank-you page.
        wp_redirect(home_url('/thank-you'));
        exit;
    }
}
add_action('template_redirect', 'k9_handle_form_submission');
