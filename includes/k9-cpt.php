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
}
add_action('add_meta_boxes', 'k9_add_meta_boxes');

// Render the meta box.
function k9_render_meta_box($post)
{
    // Get existing meta values if they exist.
    $k9_owner_meta_field = get_post_meta($post->ID, 'k9_owner', true);
    $k9_department_agency_meta_field = get_post_meta($post->ID, 'k9_department_agency', true);

    // Add a nonce field for security.
    wp_nonce_field('k9_save_meta_box', 'k9_meta_box_nonce');
    ?>
    <label for="k9_owner">Full Name</label>
    <input type="text" id="k9_owner" name="k9_owner" value="<?php echo esc_attr($k9_owner_meta_field); ?>"
        style="width: 100%; margin-bottom: 10px;" />

    <label for="k9_department_agency">K9 Handler Department or Agency:</label>
    <input type="text" id="k9_department_agency" name="k9_department_agency"
        value="<?php echo esc_attr($k9_department_agency_meta_field); ?>" style="width: 100%; margin-bottom: 10px;" />
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
        update_post_meta($post_id, '_k9_owner', sanitize_text_field($_POST['k9_owner']));
    }
    if (isset($_POST['k9_department_agency'])) {
        update_post_meta($post_id, '_k9_department_agency', sanitize_text_field($_POST['k9_department_agency']));
    }
}
add_action('save_post', 'k9_save_meta_box');
