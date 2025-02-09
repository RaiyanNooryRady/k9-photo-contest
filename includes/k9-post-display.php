<?php

function k9_submission_display_shortcode($atts) {
    // Shortcode attributes with defaults
    $atts = shortcode_atts([
        'posts_per_page' => 6, // Number of posts per page
    ], $atts, 'k9_submissions');

    // Pagination setup
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    // Query to fetch approved k9_submission posts
    $args = [
        'post_type'      => 'k9_submission',
        'post_status'    => 'publish', // Only published posts
        'posts_per_page' => $atts['posts_per_page'],
        'paged'          => $paged,
    ];

    $k9_query = new WP_Query($args);

    // Start output buffering
    ob_start();

    if ($k9_query->have_posts()) :
        echo '<div class="k9-submissions-grid">';
        while ($k9_query->have_posts()) : $k9_query->the_post();
            $post_id = get_the_ID();
            $k9_name = get_the_title();
            $k9_image = get_the_post_thumbnail_url($post_id, 'medium');
            $k9_author = get_post_meta($post_id, 'k9_owner', true);
            $k9_votes = get_post_meta($post_id, 'k9_votes', true) ?: 0;
            $k9_permalink = get_permalink();

            // Check if the current user has voted for this post
            $user_id = get_current_user_id();
            $has_voted = $user_id ? get_user_meta($user_id, 'k9_voted_post_' . $post_id, true) : false;

            ?>
            <div class="k9-card">
                <?php if ($k9_image) : ?>
                    <img src="<?php echo esc_url($k9_image); ?>" alt="<?php echo esc_attr($k9_name); ?>" class="k9-card-image">
                <?php endif; ?>
                <div class="k9-card-content">
                    <h1 class="k9-card-title"><?php echo esc_html($k9_name); ?></h1>
                    <p class="k9-card-author"><span>Author:</span> <?php echo esc_html($k9_author); ?></p>
                    <a href="<?php echo esc_url($k9_permalink); ?>" class="k9-card-read-more">Read More...</a>
                    <div class="k9-card-social-share">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($k9_permalink); ?>" target="_blank">
                            <i class="fab fa-facebook fa-2x"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($k9_permalink); ?>" target="_blank">
                            <i class="fab fa-twitter fa-2x"></i>
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($k9_permalink); ?>" target="_blank">
                            <i class="fab fa-linkedin fa-2x"></i>
                        </a>
                    </div>
                    <div class="k9-card-vote-container">
                        <p class="k9-card-votes">
                            <span class="fa-solid fa-heart"></span> <?php echo esc_html($k9_votes); ?> Vote(s)
                        </p>
                        <?php if (is_user_logged_in()) : ?>
                            <?php if (!$has_voted) : ?>
                                <button class="k9-card-vote-now" data-post-id="<?php echo $post_id; ?>">
                                    Vote Now
                                </button>
                            <?php else : ?>
                                <button class="k9-card-vote-now" disabled>Voted!</button>
                            <?php endif; ?>
                        <?php else : ?>
                            <p class="k9-card-not-authorized"><a href="<?php echo wp_login_url(); ?>">Log in</a> to vote</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        endwhile;
        echo '</div>';

        // Pagination
        echo '<div class="k9-pagination">';
        echo paginate_links([
            'total'   => $k9_query->max_num_pages,
            'current' => $paged,
        ]);
        echo '</div>';

        wp_reset_postdata();
    else :
        echo '<p>No K9 submissions found.</p>';
    endif;

    // Return the output
    return ob_get_clean();
}
add_shortcode('k9_submissions', 'k9_submission_display_shortcode');

?>