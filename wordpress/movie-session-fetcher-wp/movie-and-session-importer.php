<?php
/*
Plugin Name: Movies & Sessions Fetcher
Description: A plugin that fetches movies and their sessions from external cinema APIS and inserts them as custom posts, and keeps these movies up to date with ratings and now showing or not.
Version: 0.07.00 Beta
Author: RMIT Team - Evan Kim, Hieu Tran, Yifan Shen, Sahil Narayanm and Mihir Anand
*/

// Import Cinema Modules Here
require_once plugin_dir_path(__FILE__) . 'hoyts-module.php';
require_once plugin_dir_path(__FILE__) . 'village-module.php';
require_once plugin_dir_path(__FILE__) . 'helper-functions.php';

// Schedule the event on plugin activation
function movie_importer_schedule_event() {
    if ( ! wp_next_scheduled( 'movie_importer_cron_job' ) ) {
        wp_schedule_event( time(), 'daily', 'movie_importer_cron_job' );
        error_log('movie_importer_cron_job scheduled.');
    } else {
        error_log('movie_importer_cron_job already scheduled.');
    }
}
register_activation_hook( __FILE__, 'movie_importer_schedule_event' );

// Clear the event on plugin deactivation
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


// ADD ALL MODULES TO RUN HERE (MAKE SURE IN MODULE< ITS A FUNCTION THAT RUNS ALL MOVIES AND SESSIONS)
function run_all_modules(){
    //delcare global for cleanup
    global $wpdb;

    // Import from modules here
    hoyts_fetch_all_movies_and_sessions();
    hoyts_fetch_all_movies_and_sessions(); // 2nd run, incase first timed out.

    // Fill accessiblity taxonomies for movies depreciated, doesnt work!
    //attach_accessibility_to_all_movies();
    
    // Run Cleanup
    delete_old_sessions($wpdb);

}


// Manually trigger the movie import from the WordPress dashboard
function movie_importer_admin_page() {
    global $wpdb;
    ?>
    <style>
        .wrap {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        form {
            margin-bottom: 20px;
        }
        hr {
            margin-bottom: 20px;
        }
    </style>
    <div class="wrap">
        <h1>Movie & Session Fetcher</h1>

        <hr>
        
        <?php
            // Display the next scheduled run time
            $next_run = wp_next_scheduled('movie_importer_cron_job');
            if ($next_run) {
                echo '<p>Next scheduled run: <strong>' . date('Y-m-d H:i:s', $next_run) . '</p>';
            } else {
                echo '<p>Next scheduled run: <strong>Not scheduled</strong></p>';
            }
        ?>

        <?php
            // Display the last run time DEOSNT WORK RN FIX LATER! 
            $last_run = get_option('movie_importer_last_run');
            if ($last_run) {
                echo 'p>Last run: <strong>' . date('Y-m-d H:i:s', $last_run) . '</p>';
            } else {
                echo '<p>Last run: <strong>Never</strong></p>';
            }
        ?>

        <hr>

        <h3>Click the buttons below to manually fetch movies and sessions from the external cinema APIs.</h3>
        <form method="post" action="">
        <label><span style="color: orange;">Note:</span> This can take a while.</label><br>
            <input type="submit" name="run_all" class="button button-primary" value="Run All Now">
        </form>
        <line>

        <form method="post" action="">
        <label><span style="color: orange;">Note:</span> This can take a while when ran for the first time.</label><br>
            <input type="submit" name="import_movies" class="button button-primary" value="Manually Fetch Movies Now">
        </form>

        <form method="post" action="">
        <label><span style="color: red;">Note:</span> Please import movies before importing sessions.</label><br>
            <input type="submit" name="import_sessions" class="button button-primary" value="Manually Fetch Sessions Now">
        </form>

        <form method="post" action="">
            <input type="submit" name="run_cleanup" class="button button-primary" value="Manually Cleanup Old Sessions Now">
        </form>

        <form method="post" action="">
            <input type="submit" name="run_poster_cleanup" class="button button-primary" value="Cleanup All Movie Posters">
            <label><span style="color: orange;">Note:</span> This is for reseting the plugin state. RUN <strong>BEFORE DELETING ALL MOVIE POSTS.</strong></label><br>
        </form>
    </div>
    
    <?php
    // if ( isset( $_POST['import_movies'] ) ) {
    //     hoyts_fetch_all_movies_and_sessions();
    //     echo '<div class="notice notice-success is-dismissible"><p>Movies & sessions imported successfully!</p></div>';
    // }
    if ( isset( $_POST['run_all'] ) ) {
        run_all_modules();
        echo '<div class="notice notice-success is-dismissible"><p>Fetch and Cleanup Completed Successfully!</p></div>';
    }
    if ( isset( $_POST['import_movies'] ) ) {
            hoyts_fetch_and_insert_movies();
            echo '<div class="notice notice-success is-dismissible"><p>Movies Imported Successfully!</p></div>';
        }
    if ( isset( $_POST['import_sessions'] ) ) {
        hoyts_fetch_and_insert_sessions_all_venues();
        echo '<div class="notice notice-success is-dismissible"><p>Sessions Imported Successfully!</p></div>';
    }
    if ( isset( $_POST['run_cleanup'] ) ) {
        delete_old_sessions($wpdb);
        echo '<div class="notice notice-success is-dismissible"><p>Cleaned Up Sessions Successfully!</p></div>';
    }
    if ( isset( $_POST['run_poster_cleanup'] ) ) {
        delete_all_movie_posters($wpdb);
        echo '<div class="notice notice-success is-dismissible"><p>Cleaned Up Posters Successfully!</p></div>';
    }
}

// Add the admin menu item
// function movie_importer_menu() {
//     add_menu_page( 'Movie & Session Fetcher', 'Movie & Session Fetcher', 'manage_options', 'movie-importer', 'movie_importer_admin_page' );
// }
// add_action( 'admin_menu', 'movie_importer_menu' );
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


// ********************************************************************************
//                   Register custom post types Movies, Sessions
// ********************************************************************************
//Register Movie Post Type
function register_movie_post_type() {
    $labels = array(
        'name'               => _x( 'Movies', 'post type general name' ),
        'singular_name'      => _x( 'Movie', 'post type singular name' ),
        'menu_name'          => _x( 'Movies', 'admin menu' ),
        'name_admin_bar'     => _x( 'Movie', 'add new on admin bar' ),
        'add_new'            => _x( 'Add New', 'movie' ),
        'add_new_item'       => __( 'Add New Movie' ),
        'new_item'           => __( 'New Movie' ),
        'edit_item'          => __( 'Edit Movie' ),
        'view_item'          => __( 'View Movie' ),
        'all_items'          => __( 'All Movies' ),
        'search_items'       => __( 'Search Movies' ),
        'not_found'          => __( 'No movies found.' ),
        'not_found_in_trash' => __( 'No movies found in Trash.' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'movie' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-video-alt2', // Film camera icon
        'supports'           => array( 'title', 'editor', 'custom-fields' ),
    );

    register_post_type( 'movie', $args );
}
add_action( 'init', 'register_movie_post_type' );

// Register the custom post type 'session'
function register_session_post_type() {
    $labels = array(
        'name'               => _x( 'Sessions', 'post type general name' ),
        'singular_name'      => _x( 'Session', 'post type singular name' ),
        'menu_name'          => _x( 'Sessions', 'admin menu' ),
        'name_admin_bar'     => _x( 'Session', 'add new on admin bar' ),
        'add_new'            => _x( 'Add New', 'session' ),
        'add_new_item'       => __( 'Add New Session' ),
        'new_item'           => __( 'New Session' ),
        'edit_item'          => __( 'Edit Session' ),
        'view_item'          => __( 'View Session' ),
        'all_items'          => __( 'All Sessions' ),
        'search_items'       => __( 'Search Sessions' ),
        'not_found'          => __( 'No sessions found.' ),
        'not_found_in_trash' => __( 'No sessions found in Trash.' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'session' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => true, // Set this to true to allAow parent-child relationship
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-format-video', // Film snip with play button icon
        'supports'           => array( 'title', 'editor', 'page-attributes', 'custom-fields' ),
    );

    register_post_type( 'session', $args );
}
add_action( 'init', 'register_session_post_type' );

// ********************************************************************************
//         Register the custom taxonomies for the 'session' post type
// ********************************************************************************
function register_movie_session_taxonomies() {

    // Register the Accessibility taxonomy
    $labels_accessibility = array(
        'name'              => _x( 'Accessibility', 'taxonomy general name' ),
        'singular_name'     => _x( 'Accessibility', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Accessibility Options' ),
        'all_items'         => __( 'All Accessibility Options' ),
        'parent_item'       => __( 'Parent Accessibility' ),
        'parent_item_colon' => __( 'Parent Accessibility:' ),
        'edit_item'         => __( 'Edit Accessibility' ),
        'update_item'       => __( 'Update Accessibility' ),
        'add_new_item'      => __( 'Add New Accessibility' ),
        'new_item_name'     => __( 'New Accessibility Option Name' ),
        'menu_name'         => __( 'Accessibility' ),
    );

    $args_accessibility = array(
        'hierarchical'      => true, // Set to true for parent/child relationships
        'labels'            => $labels_accessibility,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'accessibility' ),
    );

    register_taxonomy( 'accessibility', array( 'session', 'movie' ), $args_accessibility );

    // Register the State taxonomy
    $labels_state = array(
        'name'              => _x( 'State', 'taxonomy general name' ),
        'singular_name'     => _x( 'State', 'taxonomy singular name' ),
        'search_items'      => __( 'Search States' ),
        'all_items'         => __( 'All States' ),
        'parent_item'       => __( 'Parent State' ),
        'parent_item_colon' => __( 'Parent State:' ),
        'edit_item'         => __( 'Edit State' ),
        'update_item'       => __( 'Update State' ),
        'add_new_item'      => __( 'Add New State' ),
        'new_item_name'     => __( 'New State Name' ),
        'menu_name'         => __( 'State' ),
    );

    $args_state = array(
        'hierarchical'      => true,
        'labels'            => $labels_state,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'state' ),
    );

    register_taxonomy( 'state', array( 'session' ), $args_state );

    // Register the Suburb taxonomy
    $labels_suburb = array(
        'name'              => _x( 'Suburb', 'taxonomy general name' ),
        'singular_name'     => _x( 'Suburb', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Suburbs' ),
        'all_items'         => __( 'All Suburbs' ),
        'parent_item'       => __( 'Parent Suburb' ),
        'parent_item_colon' => __( 'Parent Suburb:' ),
        'edit_item'         => __( 'Edit Suburb' ),
        'update_item'       => __( 'Update Suburb' ),
        'add_new_item'      => __( 'Add New Suburb' ),
        'new_item_name'     => __( 'New Suburb Name' ),
        'menu_name'         => __( 'Suburb' ),
    );

    $args_suburb = array(
        'hierarchical'      => false, // Set to false if there’s no hierarchy in suburbs
        'labels'            => $labels_suburb,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'suburb' ),
    );

    register_taxonomy( 'suburb', array( 'session' ), $args_suburb );

    $labels_cinema = array(
        'name'              => _x( 'Cinemas', 'taxonomy general name' ),
        'singular_name'     => _x( 'Cinema', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Cinemas' ),
        'all_items'         => __( 'All Cinemas' ),
        'parent_item'       => __( 'Parent Cinema' ),
        'parent_item_colon' => __( 'Parent Cinema:' ),
        'edit_item'         => __( 'Edit Cinema' ),
        'update_item'       => __( 'Update Cinema' ),
        'add_new_item'      => __( 'Add New Cinema' ),
        'new_item_name'     => __( 'New Cinema Name' ),
        'menu_name'         => __( 'Cinema' ),
    );

    $args_cinema = array(
        'hierarchical'      => true, // Set to true for parent/child relationships (e.g., categories)
        'labels'            => $labels_cinema,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'cinema' ),
    );

    register_taxonomy( 'cinema', array( 'session' ), $args_cinema );

    // Date Taxonomy
    $labels_date = array(
        'name'              => _x( 'Dates', 'taxonomy general name' ),
        'singular_name'     => _x( 'Date', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Dates' ),
        'all_items'         => __( 'All Dates' ),
        'parent_item'       => __( 'Parent Date' ),
        'parent_item_colon' => __( 'Parent Date:' ),
        'edit_item'         => __( 'Edit Date' ),
        'update_item'       => __( 'Update Date' ),
        'add_new_item'      => __( 'Add New Date' ),
        'new_item_name'     => __( 'New Date Name' ),
        'menu_name'         => __( 'Date' ),
    );

    $args_date = array(
        'hierarchical'      => false, // Set to false for non-hierarchical taxonomy (e.g., tags)
        'labels'            => $labels_date,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'date' ),
    );

    register_taxonomy( 'date', array( 'session' ), $args_date );

    // Time Taxonomy
    $labels_time = array(
        'name'              => _x( 'Times', 'taxonomy general name' ),
        'singular_name'     => _x( 'Time', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Times' ),
        'all_items'         => __( 'All Times' ),
        'parent_item'       => __( 'Parent Time' ),
        'parent_item_colon' => __( 'Parent Time:' ),
        'edit_item'         => __( 'Edit Time' ),
        'update_item'       => __( 'Update Time' ),
        'add_new_item'      => __( 'Add New Time' ),
        'new_item_name'     => __( 'New Time Name' ),
        'menu_name'         => __( 'Time' ),
    );

    $args_time = array(
        'hierarchical'      => false, // Set to false for non-hierarchical taxonomy (e.g., tags)
        'labels'            => $labels_time,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'time' ),
    );

    register_taxonomy( 'time', array( 'session' ), $args_time );

    // UTC Date Taxonomy
    $labels_utc_date = array(
        'name'              => _x( 'UTC Dates', 'taxonomy general name' ),
        'singular_name'     => _x( 'UTC Date', 'taxonomy singular name' ),
        'search_items'      => __( 'Search UTC Dates' ),
        'all_items'         => __( 'All UTC Dates' ),
        'parent_item'       => __( 'Parent UTC Date' ),
        'parent_item_colon' => __( 'Parent UTC Date:' ),
        'edit_item'         => __( 'Edit UTC Date' ),
        'update_item'       => __( 'Update UTC Date' ),
        'add_new_item'      => __( 'Add New UTC Date' ),
        'new_item_name'     => __( 'New UTC Date Name' ),
        'menu_name'         => __( 'UTC Date' ),
    );

    $args_utc_date = array(
        'hierarchical'      => false, // Set to false for non-hierarchical taxonomy (e.g., tags)
        'labels'            => $labels_utc_date,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'utc-date' ),
    );

    register_taxonomy( 'utc_date', array( 'session' ), $args_utc_date );

    // UTC Time Taxonomy
    $labels_utc_time = array(
        'name'              => _x( 'UTC Times', 'taxonomy general name' ),
        'singular_name'     => _x( 'UTC Time', 'taxonomy singular name' ),
        'search_items'      => __( 'Search UTC Times' ),
        'all_items'         => __( 'All UTC Times' ),
        'parent_item'       => __( 'Parent UTC Time' ),
        'parent_item_colon' => __( 'Parent UTC Time:' ),
        'edit_item'         => __( 'Edit UTC Time' ),
        'update_item'       => __( 'Update UTC Time' ),
        'add_new_item'      => __( 'Add New UTC Time' ),
        'new_item_name'     => __( 'New UTC Time Name' ),
        'menu_name'         => __( 'UTC Time' ),
    );

    $args_utc_time = array(
        'hierarchical'      => false, // Set to false for non-hierarchical taxonomy (e.g., tags)
        'labels'            => $labels_utc_time,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'utc-time' ),
    );

    register_taxonomy( 'utc_time', array( 'session' ), $args_utc_time );

}
add_action( 'init', 'register_movie_session_taxonomies' );
