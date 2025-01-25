<?php
// Shortcode for the K9 submission form.
function k9_submission_form_shortcode()
{
    ob_start(); ?>
    
    <form id="k9-submission-form" class="k9-form needs-validation" method="post" enctype="multipart/form-data" novalidate>
        <?php wp_nonce_field('k9_submission_form', 'k9_nonce'); ?>
        <div class="mb-3">
            <label for="k9-owner" class="form-label">Full Name:</label>
            <input type="text" name="k9_owner" id="k9-owner" class="form-control" placeholder="Enter your full name"
                required>
        </div>

        <div class="mb-3">
            <label for="k9-department-agency" class="form-label">K9 Handler Department or Agency:</label>
            <input type="text" name="k9_department_agency" id="k9-department-agency" class="form-control"
                placeholder="Enter department or agency" required>
        </div>

        <div class="mb-3">
            <label for="k9-name" class="form-label">K9 Name:</label>
            <input type="text" name="k9_name" id="k9-name" class="form-control" placeholder="Enter K9's name" required>
        </div>

        <div class="mb-3">
            <label for="k9-certifying-agency" class="form-label">Certifying Agency or Department:</label>
            <input type="text" name="k9_certifying_agency" id="k9-certifying-agency" class="form-control"
                placeholder="Enter certifying agency or department" required>
        </div>

        <div class="mb-3">
            <label class="form-label">What is the K9 Certified In?</label>
            <div class="form-check">
                <input type="checkbox" name="k9_certification[]" value="Patrol K9 / Cross Trained Patrol K9"
                    id="k9-cert-patrol" class="form-check-input">
                <label for="k9-cert-patrol" class="form-check-label">Patrol K9 / Cross Trained Patrol K9</label>
            </div>
            <div class="form-check">
                <input type="checkbox" name="k9_certification[]" value="Scent Detection / Tracking K9" id="k9-cert-scent"
                    class="form-check-input">
                <label for="k9-cert-scent" class="form-check-label">Scent Detection / Tracking K9</label>
            </div>
        </div>

        <div class="mb-3">
            <label for="k9-years-on-job" class="form-label">Years on the Job:</label>
            <input type="number" name="k9_years_on_job" id="k9-years-on-job" class="form-control"
                placeholder="Enter years on the job" required>
        </div>

        <div class="mb-3">
            <label for="k9-age" class="form-label">Age of K9:</label>
            <input type="number" name="k9_age" id="k9-age" class="form-control" placeholder="Enter K9's age" required>
        </div>

        <div class="mb-3">
            <label for="k9-memory" class="form-label">Best or Most Notable Career Accomplishment or Favorite Memory:</label>
            <textarea name="k9_memory" id="k9-memory" class="form-control" rows="4"
                placeholder="We love hearing how our K9 teams have impacted our communities. We’d love to hear yours!"
                required></textarea>
        </div>

        <div class="mb-3">
            <label for="k9-phone" class="form-label">Phone:</label>
            <input type="tel" name="k9_phone" id="k9-phone" class="form-control" placeholder="Enter your phone number"
                required>
        </div>

        <div class="mb-3">
            <label for="k9-email" class="form-label">Email:</label>
            <input type="email" name="k9_email" id="k9-email" class="form-control" placeholder="Enter your email address"
                required>
        </div>

        <div class="mb-3">
            <label for="k9-supervisor-name" class="form-label">Direct Supervisor's Name:</label>
            <input type="text" name="k9_supervisor_name" id="k9-supervisor-name" class="form-control"
                placeholder="Enter supervisor's name" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Certification Confirmation:</label>
            <p>I understand that this contest is for CURRENT CERTIFIED LAW ENFORCEMENT K9's ONLY.</p>
            <div class="form-check">
                <input type="radio" name="k9_certified" value="Yes" id="k9-cert-yes" class="form-check-input" required>
                <label for="k9-cert-yes" class="form-check-label">Yes</label>
            </div>
        </div>

        <div class="mb-3">
            <label for="k9-instagram-handle" class="form-label">Instagram Handle:</label>
            <input type="text" name="k9_instagram_handle" id="k9-instagram-handle" class="form-control"
                placeholder="Enter Instagram handle">
            <small class="form-text text-muted">Please only put your personal Instagram handle if you agree to let us tag
                you in posts.</small>
        </div>

        <div class="mb-3">
            <label for="k9-photo" class="form-label">Upload a Picture of Your K9:</label>
            <input type="file" name="k9_photo" id="k9-photo" class="form-control" accept="image/*" required>
            <small class="form-text text-muted">Max file size: 10 MB. Images can be of just your K9 or you and your
                K9!</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Would You Like to Make an Optional Donation?</label>
            <div class="form-check">
                <input type="radio" name="k9_donation" value="Yes" id="k9-donate-yes" class="form-check-input">
                <label for="k9-donate-yes" class="form-check-label">Yes</label>
            </div>
            <div class="form-check">
                <input type="radio" name="k9_donation" value="No" id="k9-donate-no" class="form-check-input">
                <label for="k9-donate-no" class="form-check-label">No</label>
            </div>
        </div>

        <input type="hidden" name="action" value="k9_submit_form">
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>

    <?php
    return ob_get_clean();
}
add_shortcode('k9_submission_form', 'k9_submission_form_shortcode');


// Handle form submission.
function k9_handle_form_submission()
{
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
        $k9_owner = sanitize_text_field($_POST['k9_owner']);
        $k9_department_agency = sanitize_text_field($_POST['k9_department_agency']);
        $k9_name = sanitize_text_field($_POST['k9_name']);
        $k9_certifying_agency = sanitize_text_field($_POST['k9_certifying_agency']);
        $k9_certification = !empty($_POST['k9_certification']) ? array_map('sanitize_text_field', $_POST['k9_certification']) : [];
        $k9_years_on_job = intval($_POST['k9_years_on_job']);
        $k9_age = intval($_POST['k9_age']);
        $k9_memory = sanitize_textarea_field($_POST['k9_memory']);
        $k9_phone = sanitize_text_field($_POST['k9_phone']);
        $k9_email = sanitize_email($_POST['k9_email']);
        $k9_supervisor_name = sanitize_text_field($_POST['k9_supervisor_name']);
        $k9_certified = sanitize_text_field($_POST['k9_certified']);
        $k9_instagram_handle = sanitize_text_field($_POST['k9_instagram_handle']);
        $k9_donation = sanitize_text_field($_POST['k9_donation']);

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
            'post_title' => $k9_name,
            'post_content' => $k9_memory,
            'post_status' => 'draft',
            'post_type' => 'k9_submission',
        ]);

        if (is_wp_error($post_id)) {
            wp_die('Post creation failed: ' . $post_id->get_error_message());
        }

        // Assign the uploaded photo as the featured image.
        if ($photo_id) {
            set_post_thumbnail($post_id, $photo_id);
        }

        // Save additional fields as post meta.
        update_post_meta($post_id, 'k9_owner', $k9_owner);
        update_post_meta($post_id, 'k9_department_agency', $k9_department_agency);
        update_post_meta($post_id, 'k9_certifying_agency', $k9_certifying_agency);
        update_post_meta($post_id, 'k9_certification', $k9_certification); // Saving as an array.
        update_post_meta($post_id, 'k9_years_on_job', $k9_years_on_job);
        update_post_meta($post_id, 'k9_age', $k9_age);
        update_post_meta($post_id, 'k9_memory', $k9_memory);
        update_post_meta($post_id, 'k9_phone', $k9_phone);
        update_post_meta($post_id, 'k9_email', $k9_email);
        update_post_meta($post_id, 'k9_supervisor_name', $k9_supervisor_name);
        update_post_meta($post_id, 'k9_certified', $k9_certified);
        update_post_meta($post_id, 'k9_instagram_handle', $k9_instagram_handle);
        update_post_meta($post_id, 'k9_donation', $k9_donation);

        // Redirect to a thank-you page.
        wp_redirect(home_url('/thank-you'));
        exit;
    }
}
add_action('template_redirect', 'k9_handle_form_submission');
