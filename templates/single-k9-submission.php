<?php
/**
 * Template for displaying a single K9 submission.
 */
get_header(); ?>

<!-- Full-Width Section with Three Rows -->
<div class="col-12 k9-post-info-section">
    <!-- Row 1: Post Title -->
    <div class="k9-post-title">
        <h1><?php the_title(); ?></h1>
    </div>

    <!-- Row 2: Author, Entry Date, Total Votes -->
    <div class="k9-post-meta">
        <span class="post-author">
            <span class="dashicons dashicons-admin-users"></span> <?php echo get_the_author_meta('display_name', get_post_field('post_author', get_the_ID())); ?>
        </span>
        <span class="post-date">
            <span class="dashicons dashicons-calendar"></span> <?php echo get_the_date(); ?>
        </span>
        <span class="post-votes">
            <span class="dashicons dashicons-heart"></span> <?php echo esc_html(get_post_meta(get_the_ID(), 'k9_votes', true)); ?> Votes
        </span>
    </div>

    <!-- Row 3: Buttons -->
    <?php
    // Check if the current user has voted for this post
    $user_id = get_current_user_id();
    $has_voted = $user_id ? get_user_meta($user_id, 'k9_voted_post_' . get_the_ID(), true) : false;
    //end
    ?>
    <div class="k9-post-buttons">
        <a href="<?php echo site_url(); ?>" class="btn k9-btn-secondary">&larr; Back to Home</a>        
        <?php if (is_user_logged_in()) : ?>
            <?php if (!$has_voted) : ?>
                <a href="#" class="btn k9-btn-primary k9-card-vote-now" data-post-id="<?php echo $post_id; ?>"><span class="dashicons dashicons-heart"></span>Vote Now</a>
            <?php else : ?>
                <a href="#" class="btn k9-btn-primary"><span class="dashicons dashicons-heart"></span>Voted!</a>
            <?php endif; ?>
        <?php else : ?>
            <p class="k9-card-not-authorized"><a href="<?php echo wp_login_url(); ?>">Log in</a> to vote</p>
        <?php endif; ?>
    </div>

</div>

<div id="primary" class="content-area">
    <div class="container">
        <div class="row">
            <!-- Left Side: Main Content -->
            <div class="col-md-8">
                <main id="main" class="site-main">
                    <?php while (have_posts()) : the_post(); ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                            <div class="entry-content">
                                <?php 
                                    $prev_post = get_previous_post(); 
                                    $next_post = get_next_post(); 
                                ?>

                                <div class="post-navigation">
                                    <div class="prev-post">
                                        <?php if (!empty($prev_post)) : ?>
                                            <a href="<?php echo get_permalink($prev_post->ID); ?>">
                                                <span class="post-prev">Previous</span> <br/> 
                                                <?php echo get_the_title($prev_post->ID); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="next-post">
                                        <?php if (!empty($next_post)) : ?>
                                            <a href="<?php echo get_permalink($next_post->ID); ?>">
                                                <span class="post-next">Next</span> <br/> 
                                                <?php echo get_the_title($next_post->ID); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="featured-image text-center">
                                        <?php the_post_thumbnail('large', ['class' => 'img-fluid rounded']); ?>
                                    </div>
                                <?php endif; ?>

                                <br/><span class="k9_memory"><?php the_content(); ?></span>

                                <div class="k9-details">
                                    <p>
                                        <h2 class="k9-details-label">K9 Handler Department or Agency:</h2>
                                        <?php echo esc_html(get_post_meta(get_the_ID(), 'k9_department_agency', true)); ?>
                                    </p>

                                    <p>
                                        <h2 class="k9-details-label">Certifying Agency or Department:</h2>
                                        <?php echo esc_html(get_post_meta(get_the_ID(), 'k9_certifying_agency', true)); ?>
                                    </p>
                                    
                                    <p>
                                        <h2 class="k9-details-label">What is the K9 Certified In?</h2>
                                        <?php 
                                            $certifications = get_post_meta(get_the_ID(), 'k9_certification', true);
                                            echo !empty($certifications) ? implode(', ', (array) $certifications) : 'N/A'; 
                                        ?>
                                    </p>

                                    <p>
                                        <h2 class="k9-details-label">Years on the Job:</h2>
                                        <?php echo esc_html(get_post_meta(get_the_ID(), 'k9_years_on_job', true)); ?> years
                                    </p>

                                    <p>
                                        <h2 class="k9-details-label">Age of K9:</h2>
                                        <?php echo esc_html(get_post_meta(get_the_ID(), 'k9_age', true)); ?> years
                                    </p>

                                    <p>
                                        <h2 class="k9-details-label">Direct Supervisor's Name:</h2>
                                        <?php echo esc_html(get_post_meta(get_the_ID(), 'k9_supervisor_name', true)); ?>
                                    </p>

                                    <p>
                                        <h2 class="k9-details-label">Instagram Handle:</h2>
                                        <?php echo esc_html(get_post_meta(get_the_ID(), 'k9_instagram_handle', true)); ?>
                                    </p>
                                </div>
                            </div>

                            <footer class="entry-footer">
                                <?php //edit_post_link(__('Edit', 'text-domain'), '<span class="edit-link">', '</span>'); ?>
                            </footer>

                            <!-- Social Share Section -->
                            <hr class="hr-line-top">
                            <div class="social-share">
                                <span class="share-label">Share</span>
                                <i class="fas fa-share-alt"></i>
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($k9_permalink); ?>" target="_blank" class="social-icon">
                                    <i class="fab fa-facebook"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($k9_permalink); ?>" target="_blank" class="social-icon">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($k9_permalink); ?>" target="_blank" class="social-icon">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                            </div>
                            <hr class="hr-line-bottom">

                            <div class="post-navigation">
                                <div class="prev-post">
                                    <?php if (!empty($prev_post)) : ?>
                                        <a href="<?php echo get_permalink($prev_post->ID); ?>">
                                            <span class="post-prev">Previous</span> <br/> 
                                            <?php echo get_the_title($prev_post->ID); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="next-post">
                                    <?php if (!empty($next_post)) : ?>
                                        <a href="<?php echo get_permalink($next_post->ID); ?>">
                                            <span class="post-next">Next</span> <br/> 
                                            <?php echo get_the_title($next_post->ID); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </main>
            </div>

            <!-- Right Side: Sticky Sidebar with Top Custom Posts -->
            <div class="col-md-4">
                <aside class="widget-area">
                    <h2 class="widget-title">Popular K9</h2>
                    <ul class="top-k9-list">
                        <?php
                        $top_k9_posts = new WP_Query([
                            'post_type'      => 'k9_submission',
                            'posts_per_page' => 6,
                            'orderby'        => 'meta_value_num',
                            'meta_key'       => 'k9_votes', // Assuming votes are stored as post meta
                            'order'          => 'DESC',
                        ]);

                        if ($top_k9_posts->have_posts()) :
                            while ($top_k9_posts->have_posts()) : $top_k9_posts->the_post();
                                $votes = get_post_meta(get_the_ID(), 'k9_votes', true); // Fetch total votes
                                $author_id = get_post_field('post_author', get_the_ID()); // Get author ID
                                $author_name = get_the_author_meta('display_name', $author_id); // Get author name
                        ?>
                                <li class="top-k9-item">
                                    <div class="top-k9-row">
                                        <a href="<?php the_permalink(); ?>" class="top-k9-link">
                                            <!-- Left: Featured Image -->
                                            <div class="top-k9-img">
                                                <?php if (has_post_thumbnail()) : ?>
                                                    <?php the_post_thumbnail('thumbnail', ['class' => 'k9-thumb']); ?>
                                                <?php else : ?>
                                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/default-k9.jpg'); ?>" class="k9-thumb" alt="No Image">
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                        <!-- Right: Title, Author, Votes -->
                                        <div class="top-k9-details">
                                            <h3 class="top-k9-title"><?php the_title(); ?></h3>
                                            <p class="top-k9-author"><?php echo esc_html($author_name); ?></p>
                                            <p class="top-k9-votes">
                                                <span class="dashicons dashicons-heart"></span>
                                                <?php echo esc_html($votes ? $votes : '0'); ?> Votes
                                            </p>
                                        </div>
                                    </div>
                                </li>
                        <?php endwhile;
                            wp_reset_postdata();
                        else :
                            echo '<li>No top submissions found.</li>';
                        endif;
                        ?>
                    </ul>
                </aside>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>