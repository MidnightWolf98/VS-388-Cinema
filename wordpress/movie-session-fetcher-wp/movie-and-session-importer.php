<?php
/*
Plugin Name: Movies & Sessions Fetcher
Description: A plugin that fetches movies and their sessions from external cinema APIS and inserts them as custom posts, and keeps these movies up to date with ratings and now showing or not.
Version: 0.04 Beta
Author: RMIT Team - Evan Kim, Hieu Tran, Yifan Shen, Sahil Narayanm Mihir Anand
*/

// Function to fetch and insert movies
function hoyts_fetch_and_insert_movies() {
    // Define the API endpoint
    $api_url = 'https://apim.hoyts.com.au/au/cinemaapi/api/movies'; // Replace with your actual API URL
    
    // Fetch the JSON data from the API
    $response = wp_remote_get( $api_url );
    
    if ( is_wp_error( $response ) ) {
        // Handle error in API request
        return;
    }
    
    // Decode the JSON response
    $movies = json_decode( wp_remote_retrieve_body( $response ), true );
    
    if ( empty( $movies ) ) {
        return;
    }
    
    // Loop through the movies
    foreach ( $movies as $movie ) {
        $movie_title = sanitize_text_field( $movie['name'] );
        
        // Use WP_Query to check if a post with the same movie title exists
        $args = array(
            'post_type'  => 'Movie', // Custom post type
            'title'      => $movie_title,
            'posts_per_page' => 1 // Limit to 1 result
        );
        $query = new WP_Query( $args );
        
        if ( $query->have_posts() ) {
            // If the movie already exists, skip it
            error_log("Movie already exists $movie_title ");
            wp_reset_postdata();
            continue;
        }
        
        // Prepare the post data
        $post_data = array(
            'post_title'    => $movie_title,
            'post_content'  => sanitize_text_field( $movie['summary'] ),
            'post_status'   => 'publish',
            'post_type'     => 'movie', // Custom post type for movies
        );
        
        // Insert the post and get the post ID
        $post_id = wp_insert_post( $post_data );

        // If the post was successfully created
        if ( $post_id ) {
            // Add additional metadata like release date, runtime, genres, etc.
            update_post_meta( $post_id, 'HoytsID', sanitize_text_field( $movie['vistaId'] ) ); // Store vistaId as HoytsID
            update_post_meta( $post_id, 'release_date', sanitize_text_field( $movie['releaseDate'] ) );
            update_post_meta( $post_id, 'runtime', intval( $movie['runtime']['minutes'] ) );
            update_post_meta( $post_id, 'genres', implode( ', ', array_map( 'sanitize_text_field', $movie['genres'] ) ) );
            update_post_meta( $post_id, 'rating', sanitize_text_field( $movie['rating']['id'] ) );
            update_post_meta( $post_id, 'link', esc_url( $movie['link'] ) );
        }
        
        wp_reset_postdata(); // Reset the WP_Query data
    }
}

// Schedule the event on plugin activation
function movie_importer_schedule_event() {
    if ( ! wp_next_scheduled( 'movie_importer_cron_job' ) ) {
        wp_schedule_event( time(), 'daily', 'movie_importer_cron_job' );
    }
}
add_action( 'wp', 'movie_importer_schedule_event' );

// Clear the event on plugin deactivation
function movie_importer_clear_scheduled_event() {
    $timestamp = wp_next_scheduled( 'movie_importer_cron_job' );
    wp_unschedule_event( $timestamp, 'movie_importer_cron_job' );
}
register_deactivation_hook( __FILE__, 'movie_importer_clear_scheduled_event' );

// Hook the function to the scheduled event
add_action( 'movie_importer_cron_job', 'hoyts_fetch_and_insert_movies' );

// Manually trigger the movie import from the WordPress dashboard
function movie_importer_admin_page() {
    ?>
    <div class="wrap">
        <h1>Hoyts Movie Importer</h1>
        <form method="post" action="">
            <input type="submit" name="import_movies" class="button button-primary" value="Import Movies Now">
        </form>
    </div>
    <?php
    if ( isset( $_POST['import_movies'] ) ) {
        hoyts_fetch_and_insert_movies();
        hoyts_fetch_and_insert_sessions();
        echo '<div class="notice notice-success is-dismissible"><p>Movies & sessions imported successfully!</p></div>';
    }
}

// Add the admin menu item
function movie_importer_menu() {
    add_menu_page( 'Hoyts Movie Importer', 'Hoyts Movie Importer', 'manage_options', 'movie-importer', 'movie_importer_admin_page' );
}
add_action( 'admin_menu', 'movie_importer_menu' );

function hoyts_fetch_and_insert_sessions() {
    error_log("Fetching sessions...");
    // Define the API endpoint for sessions
    $api_url = 'https://apim.hoyts.com.au/au/cinemaapi/api/sessions/TAYLOR'; // Replace with the actual API URL for sessions
    
    // Fetch the JSON data from the API
    $response = wp_remote_get( $api_url );
    
    if ( is_wp_error( $response ) ) {
        error_log("Error in Session API Request", $response->get_error_message());
        return;
    }
    
    // Decode the JSON response
    $sessions = json_decode( wp_remote_retrieve_body( $response ), true );
    
    if ( empty( $sessions ) ) {
        error_log("No sessions found");
        return;
    }

    // Loop through the sessions
    foreach ( $sessions as $session ) {
        $movie_hoyts_id = sanitize_text_field( $session['movieId'] ); // Hoyts movie ID
        $session_id = intval( $session['id'] ); // Unique session ID
        error_log("Processing session $session_id for movie $movie_hoyts_id");
        
        // Check if the session already exists using the session ID (stored as post meta)
        $args = array(
            'post_type'  => 'session',
            'meta_query' => array(
                array(
                    'key'   => 'session_id', // Meta key where the session ID is stored
                    'value' => $session_id,
                    'compare' => '='
                )
            ),
            'posts_per_page' => 1 // Limit to 1 result
        );
        
        $query = new WP_Query( $args );
        
        if ( $query->have_posts() ) {
            // If the session already exists, skip it
            wp_reset_postdata();
            continue;
        }
        
        // Query for the movie using HoytsID (stored as meta 'hoytsID')
        $args = array(
            'post_type'  => 'movie',
            'meta_query' => array(
                array(
                    'key'   => 'hoytsID', // This assumes 'hoytsID' was stored when creating the movie
                    'value' => $movie_hoyts_id,
                    'compare' => '='
                )
            ),
            'posts_per_page' => 1 // Limit to 1 result
        );
        
        $movie_query = new WP_Query( $args );
        
        if ( ! $movie_query->have_posts() ) {
            // If the movie doesn't exist, skip this session
            wp_reset_postdata();
            continue;
        }
        
        $movie_post = $movie_query->posts[0]; // Get the movie post object
        $movie_post_id = $movie_post->ID; // Get the movie post ID
        
        // Prepare the session post data
        $post_data = array(
            'post_title'    => 'Session for Movie: ' . get_the_title( $movie_post_id ),
            'post_status'   => 'publish',
            'post_type'     => 'session', // Custom post type for sessions
            'post_parent'   => $movie_post_id // Set the movie as the parent post
        );
        
        // Insert the session post and get the post ID
        $session_post_id = wp_insert_post( $post_data );
        
        if ( $session_post_id ) {
            // Store additional metadata including the session ID
            update_post_meta( $session_post_id, 'session_id', $session_id ); // Store session ID to prevent duplicates
            update_post_meta( $session_post_id, 'cinema_id', sanitize_text_field( $session['cinemaId'] ) );
            update_post_meta( $session_post_id, 'session_date', sanitize_text_field( $session['date'] ) );
            update_post_meta( $session_post_id, 'utc_date', sanitize_text_field( $session['utcDate'] ) );
            update_post_meta( $session_post_id, 'booking_link', esc_url( 'https://hoyts.com.au' . $session['link'] ) );
            
            // Assign secondary tags to the Accessibility taxonomy
            if ( !empty( $session['secondaryTags'] ) ) {
                $accessibility_terms = array_map( 'sanitize_text_field', $session['secondaryTags'] );
                wp_set_post_terms( $session_post_id, $accessibility_terms, 'accessibility' );
            }
            // Assign 'VIC' to the state taxonomy if not already assigned
            if ( !has_term( 'VIC', 'state', $session_post_id ) ) {
                wp_set_object_terms( $session_post_id, 'VIC', 'state', true );
            }

            // Assign 'Watergardens' to the suburb taxonomy if not already assigned
            if ( !has_term( 'Watergardens', 'suburb', $session_post_id ) ) {
                wp_set_object_terms( $session_post_id, 'Watergardens', 'suburb', true );
            }
        }
        
        wp_reset_postdata(); // Reset the WP_Query data
    }
}

// Hook the session fetching function to a cron job or manual trigger
add_action( 'fetch_sessions', 'fetch_and_insert_sessions' );

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
        'supports'           => array( 'title', 'editor', 'page-attributes', 'custom-fields' ),
    );

    register_post_type( 'session', $args );
}
add_action( 'init', 'register_session_post_type' );

// ********************************************************************************
//         Register the custom taxonomies for the 'session' post type
// ********************************************************************************
function register_session_taxonomies() {

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

    register_taxonomy( 'accessibility', array( 'session' ), $args_accessibility );

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
}
add_action( 'init', 'register_session_taxonomies' );
