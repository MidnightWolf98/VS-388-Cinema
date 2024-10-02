<?php
/*
Plugin Name: Movies & Sessions Fetcher
Description: A plugin that fetches movies and their sessions from external cinema APIS and inserts them as custom posts, and keeps these movies up to date with ratings and now showing or not.
Version: 0.01 Beta
Author: RMIT Team - Evan Kim, Hieu Tran etc add later
*/

// Function to fetch and insert movies
function fetch_and_insert_movies() {
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
add_action( 'movie_importer_cron_job', 'fetch_and_insert_movies' );

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
        fetch_and_insert_movies();
        echo '<div class="notice notice-success is-dismissible"><p>Movies imported successfully!</p></div>';
    }
}

// Add the admin menu item
function movie_importer_menu() {
    add_menu_page( 'Hoyts Movie Importer', 'Hoyts Movie Importer', 'manage_options', 'movie-importer', 'movie_importer_admin_page' );
}
add_action( 'admin_menu', 'movie_importer_menu' );

// Register the custom post type 'movie'
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