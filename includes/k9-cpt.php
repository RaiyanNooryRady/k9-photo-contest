<?php
// Register Custom Post Type for K9 submissions.
function k9_register_cpt() {
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
