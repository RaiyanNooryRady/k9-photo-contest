<?php
/*
Plugin Name: K9 Photo Contest
Description: A plugin for managing K9 photo contests.
Version: 1.0
Author: Your Name
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin directory.
define('K9_PHOTO_CONTEST_DIR', plugin_dir_path(__FILE__));
define('K9_PHOTO_CONTEST_URL', plugin_dir_url(__FILE__));

// Include necessary files.
include_once K9_PHOTO_CONTEST_DIR . 'includes/k9-cpt.php';
include_once K9_PHOTO_CONTEST_DIR . 'includes/k9-form-handler.php';

// Enqueue styles and scripts.
function k9_enqueue_scripts() {

    //Enqueue Styles
    wp_enqueue_style('k9-bootstrap-css', K9_PHOTO_CONTEST_URL . 'assets/css/bootstrap.min.css' );
    wp_enqueue_style('k9-styles', K9_PHOTO_CONTEST_URL . 'assets/css/style.css');

    //Enqueue Scripts
    wp_enqueue_script('k9-bootstrap-js', K9_PHOTO_CONTEST_URL . 'assets/js/bootstrap.bundle.min.js');
    wp_enqueue_script('k9-scripts', K9_PHOTO_CONTEST_URL . 'assets/js/script.js', ['jquery'], false, true);
}
add_action('wp_enqueue_scripts', 'k9_enqueue_scripts');
