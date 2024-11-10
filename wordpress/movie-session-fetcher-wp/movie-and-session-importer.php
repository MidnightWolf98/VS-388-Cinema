<?php
/*
Plugin Name: Movies & Sessions Fetcher
Description: A plugin that fetches movies and their sessions from external cinema APIS and inserts them as custom posts, and keeps these movies up to date with ratings and now showing or not.
Version: 0.9.02 Beta
Author: RMIT Team - Evan Kim, Hieu Tran, Yifan Shen, Sahil Narayanm and Mihir Anand
*/

// Import Required Functions Here. 
// NOTES: - BE CAFEFULL WHEN MOVING FUNCTIONS AROUND!!!
//        - ALSO DONT FORGET TO CHANGE THE PATHS IN THE IMPORTS!!!
require_once plugin_dir_path(__FILE__) . 'register-types/register-custom-posts.php'; //not moved yet
require_once plugin_dir_path(__FILE__) . 'register-types/register-custom-taxonomies.php'; //not moved yet
require_once plugin_dir_path(__FILE__) . 'required-functions/admin-panel.php';
require_once plugin_dir_path(__FILE__) . 'required-functions/helper-functions.php';
require_once plugin_dir_path(__FILE__) . 'required-functions/cleanup-functions.php';

// Import Cinema Modules Here
// NOTES: - BE CAFEFULL WHEN MOVING FUNCTIONS AROUND!!!
//        - ALSO DONT FORGET TO CHANGE THE PATHS IN THE IMPORTS!!!
require_once plugin_dir_path(__FILE__) . 'cinema-modules/hoyts-module.php';
require_once plugin_dir_path(__FILE__) . 'cinema-modules/village-module.php';

// Schedule the event on plugin activation (schedule cron job)
function movie_importer_schedule_event() {
    if ( ! wp_next_scheduled( 'movie_importer_cron_job' ) ) {
        wp_schedule_event( time(), 'daily', 'movie_importer_cron_job' );
        error_log('movie_importer_cron_job scheduled.');
    } else {
        error_log('movie_importer_cron_job already scheduled.');
    }
}
register_activation_hook( __FILE__, 'movie_importer_schedule_event' );

// Clear the event on plugin deactivation (clear cron job)
function movie_importer_clear_scheduled_event() {
    $timestamp = wp_next_scheduled( 'movie_importer_cron_job' );
    if ($timestamp) {
        wp_unschedule_event( $timestamp, 'movie_importer_cron_job' );
        error_log('movie_importer_cron_job unscheduled.');
    } else {
        error_log('movie_importer_cron_job not found.');
    }
}
register_deactivation_hook( __FILE__, 'movie_importer_clear_scheduled_event' );

// Hook the function to the scheduled event
add_action( 'movie_importer_cron_job', 'run_all_modules' );

// ********************************************************************************
//                         MAIN FUNCTION TO RUN ALL MODULES
// ********************************************************************************
// Add the main functions from each to run all modules (function thats runs on cron trigger)
function run_all_modules(){
    //delcare global for cleanup
    global $wpdb;

    // Run each module main function here
    hoyts_fetch_all_movies_and_sessions();
    hoyts_fetch_all_movies_and_sessions(); // 2nd run, incase first timed out.
    village_fetch_all_movies_and_sessions();
    village_fetch_all_movies_and_sessions(); // 2nd run, incase first timed out.
    
    // Run Cleanup, only if its not the first run
    $last_run = get_option('movie_importer_last_run'); // Check if this is the first run
    if ($last_run) {
        delete_old_sessions($wpdb);
        delete_old_date_taxonomies();
    }

    // Save the timestamp of the last run
    update_option('movie_importer_last_run', current_time('timestamp'));
}

// ********************************************************************************
// ********************************************************************************

// Add the admin menu to side bar
function movie_importer_menu() {
    add_menu_page(
        'Movie & Session Fetcher', // Page title
        'Movie & Session Fetcher', // Menu title
        'manage_options',          // Capability
        'movie-importer',          // Menu slug
        'movie_importer_admin_page', // Function to display the page
        'dashicons-video-alt2'     // Dashicon for the menu item
    );
}
add_action( 'admin_menu', 'movie_importer_menu' );



