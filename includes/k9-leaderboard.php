<?php
// Shortcode for K9 Leaderboard
function k9_leaderboard_shortcode() {
    ob_start();

    // Get posts ordered by total_votes meta field
    $args = array(
        'post_type'      => 'k9_submission',
        'posts_per_page' => 10, // Get all posts
        'orderby'        => 'meta_value_num',
        'meta_key'       => 'k9_votes', // Assuming votes are stored as post meta
        'order'          => 'DESC',
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $rank = 1;
        $top_posts = [];

        // Collect top 3 posts
        while ($query->have_posts() && $rank <= 3) {
            $query->the_post();
            $top_posts[] = array(
                'id'        => get_the_ID(),
                'title'     => get_the_title(),
                'author'    => get_the_author(),
                'votes'     => get_post_meta(get_the_ID(), 'k9_votes', true) ?: 0,
                'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: 'https://via.placeholder.com/100',
                'link'      => get_permalink(get_the_ID()),
            );
            $rank++;
        }

        // Leaderboard container
        echo '<div class="leaderboard container-xl px-3">';
        echo '<h2 class="text-center mb-4">K9 Leaderboard</h2>';

       // Display Top 3 Posts
if (!empty($top_posts)) {
    echo '<div class="row top-three justify-content-center mb-4">';

    // #2 Post
    if (!empty($top_posts[1])) {
        echo '<div class="col-lg-3 col-md-4 col-sm-12 second-place">';
        echo '<div class="top-post bg-secondary text-light p-3 rounded text-center">';
        echo '<h3>#2 ' . esc_html($top_posts[1]['title']) . '</h3>';
        echo '<p>Author: ' . esc_html($top_posts[1]['author']) . '</p>';
        echo '<a href="' . esc_url($top_posts[1]['link']) . '">';
        echo '<img src="' . esc_url($top_posts[1]['thumbnail']) . '" class="img-fluid rounded-circle mb-3" alt="K9 Image">';
        echo '</a>';
        echo '<p>Total Votes: ' . esc_html($top_posts[1]['votes']) . '</p>';
        echo '</div>';
        echo '</div>';
    }

    // #1 Post
    if (!empty($top_posts[0])) {
        echo '<div class="col-lg-4 col-md-6 col-sm-12 first-place">';
        echo '<div class="top-post bg-primary text-light p-4 rounded text-center">';
        echo '<h2>#1 ' . esc_html($top_posts[0]['title']) . '</h2>';
        echo '<p>Author: ' . esc_html($top_posts[0]['author']) . '</p>';
        echo '<a href="' . esc_url($top_posts[0]['link']) . '">';
        echo '<img src="' . esc_url($top_posts[0]['thumbnail']) . '" class="img-fluid rounded-circle mb-3" alt="K9 Image">';
        echo '</a>';
        echo '<p class="lead">Total Votes: ' . esc_html($top_posts[0]['votes']) . '</p>';
        echo '</div>';
        echo '</div>';
    }

    // #3 Post
    if (!empty($top_posts[2])) {
        echo '<div class="col-lg-3 col-md-4 col-sm-12 third-place">';
        echo '<div class="top-post bg-dark text-light p-2 rounded text-center">';
        echo '<h3>#3 ' . esc_html($top_posts[2]['title']) . '</h3>';
        echo '<p>Author: ' . esc_html($top_posts[2]['author']) . '</p>';
        echo '<a href="' . esc_url($top_posts[2]['link']) . '">';
        echo '<img src="' . esc_url($top_posts[2]['thumbnail']) . '" class="img-fluid rounded-circle mb-3" alt="K9 Image">';
        echo '</a>';
        echo '<p>Total Votes: ' . esc_html($top_posts[2]['votes']) . '</p>';
        echo '</div>';
        echo '</div>';
    }

    echo '</div>'; // End top-three row
}

        // Posts table for rank 4 and beyond
        echo '<div class="table-responsive">';
        echo '<table class="table table-dark table-striped">';
        echo '<thead><tr><th>Rank</th><th>User</th><th>K9 Name</th><th>K9 Photo</th><th>Total Votes</th></tr></thead>';
        echo '<tbody>';

        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $votes = get_post_meta($post_id, 'k9_votes', true) ?: 0;
            $author = get_the_author();
            $title = get_the_title();
            $thumbnail = get_the_post_thumbnail_url($post_id, 'thumbnail') ?: 'https://via.placeholder.com/100';

            echo '<tr>';
            echo '<td>' . $rank . '</td>';
            echo '<td>' . esc_html($author) . '</td>';
            echo '<td>' . esc_html($title) . '</td>';
            echo '<td><a href="' . get_permalink($post_id) . '"><img src="' . esc_url($thumbnail) . '" class="img-thumbnail" alt="K9 Image"></a></td>';
            echo '<td>' . esc_html($votes) . '</td>';
            echo '</tr>';

            $rank++;
        }

        echo '</tbody></table>';
        echo '</div>'; // End table-responsive
        echo '</div>'; // End leaderboard container
    } else {
        echo '<p>No submissions found.</p>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('k9_leaderboard', 'k9_leaderboard_shortcode');

?>