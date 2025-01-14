<?php
// Shortcode for the K9 submission form.
function k9_submission_form_shortcode() {
    ob_start(); ?>
    <form id="k9-submission-form" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('k9_submission_form', 'k9_nonce'); ?>
         <!-- Full Name -->
    <label for="k9-owner"> Full Name:</label>
    <input type="text" name="k9_owner" id="k9-owner" placeholder="Enter your full name" required>

    <!-- Department or Agency -->
    <label for="k9-department-agency">K9 Handler Department or Agency:</label>
    <input type="text" name="k9_department_agency" id="k9-department-agency" placeholder="Enter department or agency" required>

    <!-- K9 Name -->
    <label for="k9-name">K9 Name:</label>
    <input type="text" name="k9_name" id="k9-name" placeholder="Enter K9's name" required>

    <!-- Certifying Agency or Department -->
    <label for="k9-certifying-agency">Certifying Agency or Department:</label>
    <input type="text" name="k9_certifying_agency" id="k9-certifying-agency" placeholder="Enter certifying agency or department" required>

    <!-- Certification Type -->
    <label>What is the K9 Certified In?</label>
    <div>
        <input type="checkbox" name="k9_certification[]" value="Patrol K9 / Cross Trained Patrol K9" id="k9-cert-patrol">
        <label for="k9-cert-patrol">Patrol K9 / Cross Trained Patrol K9</label>
    </div>
    <div>
        <input type="checkbox" name="k9_certification[]" value="Scent Detection / Tracking K9" id="k9-cert-scent">
        <label for="k9-cert-scent">Scent Detection / Tracking K9</label>
    </div>

    <!-- Years on the Job -->
    <label for="k9-years-on-job">Years on the Job:</label>
    <input type="number" name="k9_years_on_job" id="k9-years-on-job" placeholder="Enter years on the job" required>

    <!-- Age of K9 -->
    <label for="k9-age">Age of K9:</label>
    <input type="number" name="k9_age" id="k9-age" placeholder="Enter K9's age" required>

    <!-- Accomplishment or Memory -->
    <label for="k9-memory">Best or Most Notable Career Accomplishment or Favorite Memory:</label>
    <textarea name="k9_memory" id="k9-memory" rows="4" placeholder="Share your story" required></textarea>

    <!-- Community Impact -->
    <label for="k9-community-impact">We love hearing how our K9 teams have impacted our communities. We’d love to hear yours!</label>
    <textarea name="k9_community_impact" id="k9-community-impact" rows="4" placeholder="Share your impact story"></textarea>

    <!-- Phone -->
    <label for="k9-phone">Phone:</label>
    <input type="tel" name="k9_phone" id="k9-phone" placeholder="Enter your phone number" required>

    <!-- Email -->
    <label for="k9-email">Email:</label>
    <input type="email" name="k9_email" id="k9-email" placeholder="Enter your email address" required>

    <!-- Supervisor's Name -->
    <label for="k9-supervisor-name">Direct Supervisor's Name:</label>
    <input type="text" name="k9_supervisor_name" id="k9-supervisor-name" placeholder="Enter supervisor's name" required>

    <!-- Certification Confirmation -->
    <label for="k9-certified">I understand that this contest is for CURRENT CERTIFIED LAW ENFORCEMENT K9's ONLY. By clicking yes, I agree that my K9 is currently certified and actively employed at an agency or department:</label>
    <div>
        <input type="radio" name="k9_certified" value="Yes" id="k9-cert-yes" required>
        <label for="k9-cert-yes">Yes</label>
    </div>

    <!-- Instagram Handle -->
    <label for="k9-instagram-handle">Department and/or K9's Instagram Handle:</label>
    <input type="text" name="k9_instagram_handle" id="k9-instagram-handle" placeholder="Enter Instagram handle">

    <!-- Photo Upload -->
    <label for="k9-photo">UPLOAD A PICTURE OF YOUR K9! Images can be of just your K9 or You and your K9!</label>
    <input type="file" name="k9_photo" id="k9-photo" accept="image/*" required>
    <p>Max. file size: 10 MB</p>

    <!-- Donation -->
    <label for="k9-donation">Would You Like to Make an OPTIONAL Donation? (100% of donations go to Harlow's Heroes):</label>
    <div>
        <input type="radio" name="k9_donation" value="Yes" id="k9-donate-yes">
        <label for="k9-donate-yes">Yes</label>
    </div>
    <div>
        <input type="radio" name="k9_donation" value="No" id="k9-donate-no">
        <label for="k9-donate-no">No</label>
    </div>


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
