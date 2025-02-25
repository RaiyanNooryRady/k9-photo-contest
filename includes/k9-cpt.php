<?php
// Register Custom Post Type for K9 submissions.
function k9_register_cpt()
{
    $labels = [
        'name' => 'K9 Submissions',
        'singular_name' => 'K9 Submission',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New K9 Submission',
        'edit_item' => 'Edit K9 Submission',
        'new_item' => 'New K9 Submission',
        'view_item' => 'View K9 Submission',
        'all_items' => 'All K9 Submissions',
        'search_items' => 'Search K9 Submissions',
        'not_found' => 'No submissions found.',
        'not_found_in_trash' => 'No submissions found in Trash.',
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'show_in_menu' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'author'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'k9-submissions'],
    ];

    register_post_type('k9_submission', $args);
}
add_action('init', 'k9_register_cpt');

// Add custom meta box for K9 Submissions.
function k9_add_meta_boxes()
{
    add_meta_box(
        'k9_meta_box',                // Meta box ID
        'Custom Fields',              // Meta box title
        'k9_render_meta_box',         // Callback function to render the meta box
        'k9_submission',              // Post type
        'normal',                     // Context
        'high'                        // Priority
    );

    // Add Expiry Date Meta Box
    add_meta_box(
        'k9_expiry_meta_box',
        'Post Expiry Date',
        'k9_render_expiry_meta_box',
        'k9_submission',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'k9_add_meta_boxes');

// Render the expiry date meta box.
function k9_render_expiry_meta_box($post)
{
    $k9_expiry_date = get_post_meta($post->ID, 'k9_expiry_date', true);

    wp_nonce_field('k9_save_expiry_meta_box', 'k9_expiry_meta_box_nonce');
    ?>
    <label for="k9_expiry_date">Set Expiry Date:</label>
    <input type="date" id="k9_expiry_date" name="k9_expiry_date"
        value="<?php echo esc_attr($k9_expiry_date); ?>" class="k9-admin-field" />
    <p><small>After this date, the photo will not be displayed.</small></p>
    <?php
}

// Render the meta box.
function k9_render_meta_box($post)
{
    // Get existing meta values if they exist.
    $k9_owner_meta_field = get_post_meta($post->ID, 'k9_owner', true);
    $k9_department_agency_meta_field = get_post_meta($post->ID, 'k9_department_agency', true);
    $k9_certifying_agency_meta_field = get_post_meta($post->ID, 'k9_certifying_agency', true);

    $k9_certification_meta_field = get_post_meta($post->ID, 'k9_certification', true);
    $k9_certification_meta_field = is_array($k9_certification_meta_field) ? $k9_certification_meta_field : [];

    $k9_years_on_job_meta_field = get_post_meta($post->ID, 'k9_years_on_job', true);
    $k9_age_meta_field = get_post_meta($post->ID, 'k9_age', true);
    // $k9_memory_meta_field = get_post_meta($post->ID, 'k9_memory', true);
    $k9_phone_meta_field = get_post_meta($post->ID, 'k9_phone', true);
    $k9_email_meta_field = get_post_meta($post->ID, 'k9_email', true);
    $k9_supervisor_name_meta_field = get_post_meta($post->ID, 'k9_supervisor_name', true);
    $k9_certified_meta_field = get_post_meta($post->ID, 'k9_certified', true);
    $k9_instagram_handle_meta_field = get_post_meta($post->ID, 'k9_instagram_handle', true);
    $k9_donation_meta_field = get_post_meta($post->ID, 'k9_donation', true);
    // Add a nonce field for security.
    wp_nonce_field('k9_save_meta_box', 'k9_meta_box_nonce');
    ?>
    <label for="k9_owner">Full Name</label>
    <input type="text" id="k9_owner" class="k9-admin-field" name="k9_owner"
        value="<?php echo esc_attr($k9_owner_meta_field); ?>" />

    <label for="k9_department_agency">K9 Handler Department or Agency:</label>
    <input type="text" id="k9_department_agency" class="k9-admin-field" name="k9_department_agency"
        value="<?php echo esc_attr($k9_department_agency_meta_field); ?>" />

    <label for="k9_certifying_agency">Certifying Agency or Department:</label>
    <input type="text" id="k9_certifying_agency" name="k9_certifying_agency"
        value="<?php echo esc_attr($k9_certifying_agency_meta_field); ?>" class="k9-admin-field" />

    <label for="k9_certification">What is the K9 Certified In?</label><br>
    <div>
        <input type="checkbox" id="k9_certification_patrol" name="k9_certification[]"
            value="Patrol K9 / Cross Trained Patrol K9" <?php checked(in_array('Patrol K9 / Cross Trained Patrol K9', $k9_certification_meta_field)); ?> />
        <label for="k9_certification_patrol">Patrol K9 / Cross Trained Patrol K9</label>
    </div>
    <div>
        <input type="checkbox" id="k9_certification_scent" name="k9_certification[]" value="Scent Detection / Tracking K9"
            <?php checked(in_array('Scent Detection / Tracking K9', $k9_certification_meta_field)); ?> />
        <label for="k9_certification_scent">Scent Detection / Tracking K9</label>
    </div>

    <label for="k9_years_on_job">Years on the Job:</label>
    <input type="number" id="k9_years_on_job" name="k9_years_on_job"
        value="<?php echo esc_attr($k9_years_on_job_meta_field); ?>" class="k9-admin-field" />

    <label for="k9_age">Age of K9</label>
    <input type="number" id="k9_age" name="k9_age" value="<?php echo esc_attr($k9_age_meta_field); ?>"
        class="k9-admin-field" />

    <label for="k9_phone">Phone:</label>
    <input type="number" id="k9_phone" name="k9_phone" value="<?php echo esc_attr($k9_phone_meta_field); ?>"
        class="k9-admin-field" />

    <label for="k9_email">Email:</label>
    <input type="email" id="k9_email" name="k9_email" value="<?php echo esc_attr($k9_email_meta_field); ?>"
        class="k9-admin-field" />

    <label for="k9_supervisor_name">Direct Supervisor's Name:</label>
    <input type="text" id="k9_supervisor_name" name="k9_supervisor_name"
        value="<?php echo esc_attr($k9_supervisor_name_meta_field); ?>" class="k9-admin-field" />

    <label for="k9_certified">Certification Confirmation:</label>
    <input type="text" id="k9_certified" name="k9_certified" value="<?php echo esc_attr($k9_certified_meta_field); ?>"
        class="k9-admin-field" />

    <label for="k9_instagram_handle">Instagram Handle:</label>
    <input type="text" id="k9_instagram_handle" name="k9_instagram_handle"
        value="<?php echo esc_attr($k9_instagram_handle_meta_field); ?>" class="k9-admin-field" />

    <label for="k9_donation">Would You Like to Make an Optional Donation?</label>
    <input type="text" id="k9_donation" name="k9_donation" value="<?php echo esc_attr($k9_donation_meta_field); ?>"
        class="k9-admin-field" />
    <?php
}

// Save the custom meta fields.
function k9_save_meta_box($post_id)
{
    // Check if nonce is set and valid.
    if (!isset($_POST['k9_meta_box_nonce']) || !wp_verify_nonce($_POST['k9_meta_box_nonce'], 'k9_save_meta_box')) {
        return;
    }

    // Check for autosave and user permissions.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save meta fields.
    if (isset($_POST['k9_owner'])) {
        update_post_meta($post_id, 'k9_owner', sanitize_text_field($_POST['k9_owner']));
    }
    if (isset($_POST['k9_department_agency'])) {
        update_post_meta($post_id, 'k9_department_agency', sanitize_text_field($_POST['k9_department_agency']));
    }
    if (isset($_POST['k9_certifying_agency'])) {
        update_post_meta($post_id, 'k9_certifying_agency', sanitize_text_field($_POST['k9_certifying_agency']));
    }
    // Save the `k9_certification` field as an array.
    if (isset($_POST['k9_certification']) && is_array($_POST['k9_certification'])) {
        update_post_meta($post_id, 'k9_certification', array_map('sanitize_text_field', $_POST['k9_certification']));
    } else {
        delete_post_meta($post_id, 'k9_certification'); // Clear if no certifications are checked.
    }

    if (isset($_POST['k9_years_on_job'])) {
        update_post_meta($post_id, 'k9_years_on_job', sanitize_text_field($_POST['k9_years_on_job']));
    }
    if (isset($_POST['k9_age'])) {
        update_post_meta($post_id, 'k9_age', sanitize_text_field($_POST['k9_age']));
    }

    if (isset($_POST['k9_phone'])) {
        update_post_meta($post_id, 'k9_phone', sanitize_text_field($_POST['k9_phone']));
    }
    if (isset($_POST['k9_email'])) {
        update_post_meta($post_id, 'k9_email', sanitize_text_field($_POST['k9_email']));
    }
    if (isset($_POST['k9_supervisor_name'])) {
        update_post_meta($post_id, 'k9_supervisor_name', sanitize_text_field($_POST['k9_supervisor_name']));
    }
    if (isset($_POST['k9_certified'])) {
        update_post_meta($post_id, 'k9_certified', sanitize_text_field($_POST['k9_certified']));
    }
    if (isset($_POST['k9_instagram_handle'])) {
        update_post_meta($post_id, 'k9_instagram_handle', sanitize_text_field($_POST['k9_instagram_handle']));
    }
    if (isset($_POST['k9_donation'])) {
        update_post_meta($post_id, 'k9_donation', sanitize_text_field($_POST['k9_donation']));
    }
}
add_action('save_post', 'k9_save_meta_box');

// Save the expiry date.
function k9_save_expiry_meta_box($post_id)
{
    // Check nonce validity.
    if (!isset($_POST['k9_expiry_meta_box_nonce']) || !wp_verify_nonce($_POST['k9_expiry_meta_box_nonce'], 'k9_save_expiry_meta_box')) {
        return;
    }

    // Check for autosave and user permissions.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save the expiry date (date-only format).
    if (isset($_POST['k9_expiry_date'])) {
        update_post_meta($post_id, 'k9_expiry_date', sanitize_text_field($_POST['k9_expiry_date']));
    }
}
add_action('save_post', 'k9_save_expiry_meta_box');