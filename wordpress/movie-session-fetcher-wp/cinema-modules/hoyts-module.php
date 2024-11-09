<?php
// ************ HOYTS MODULE ************
// This module is responsible for fetching movie & its sessions from Hoyts Cinema

// list of venues & their codes, required for fetching sessions
$venues = [
    // VICTORIA VENUES FOR HOYTS
       (object) [
        'code' => 'BMWCIN',
        'suburb' => 'Broadmeadows',
        'state' => 'VIC'
    ], (object) [
        'code' => 'CHDSTN',
        'suburb' => 'Chadstone',
        'state' => 'VIC'
    ], (object) [
        'code' => 'DOCCIN',
        'suburb' => 'District Docklands',
        'state' => 'VIC'
    ], (object) [
        'code' => 'EASTLN',
        'suburb' => 'Eastland',
        'state' => 'VIC'
    ], (object) [
        'code' => 'FHLCIN',
        'suburb' => 'Forrest Hill',
        'state' => 'VIC'
    ], (object) [
        'code' => 'FRANKS',
        'suburb' => 'Frankston',
        'state' => 'VIC'
    ], (object) [
        'code' => 'GRENSB',
        'suburb' => 'Greensborough',
        'state' => 'VIC'
    ], (object) [
        'code' => 'HIGPNT',
        'suburb' => 'Highpoint',
        'state' => 'VIC'
    ], (object) [
        'code' => 'MCECIN',
        'suburb' => 'Melbourne Central',
        'state' => 'VIC'
    ], (object) [
        'code' => 'NORTHL',
        'suburb' => 'Northland',
        'state' => 'VIC'
    ], (object) [
        'code' => 'VGDCIN',
        'suburb' => 'Victoria Gardens',
        'state' => 'VIC'
    ], (object) [
        'code' => 'TAYLOR',
        'suburb' => 'Watergardens',
        'state' => 'VIC'
    ]
    //ADD REST LATER
];

// Run everything
function hoyts_fetch_all_movies_and_sessions() {
    hoyts_fetch_and_insert_movies();
    hoyts_fetch_and_insert_sessions_all_venues();
}


// ******************************************************************************
// ******************************* Movies Fetcher *******************************
// ******************************************************************************

function hoyts_fetch_and_insert_movies() {

    // Increase time limit to 5 minutes for this operation
    set_time_limit(600);

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

        if ( empty( $movie['releaseDate'] ) || empty($movie['posterImage']) ) {
            // skip movies that don't have a release date or no poster (these are speculated movies w/ no poster)
            // even further before coming soon movies. 
            continue;
        }

        $movie_link = 'https://hoyts.com.au' . $movie['link'];
        $movie_poster = 'https://imgix.hoyts.com.au/' . $movie['posterImage'];
        
        $movie_status = 'Unknown';

        if($movie['type'] = 'nowShowing'){
            $movie_status = 'Now Showing';
        } else if($movie['type'] = 'comingSoon'){
            $movie_status = 'Coming Soon';
        } else if ($movie['type'] = 'advanceSale'){
            $movie_status = 'Tickets on Sale, Release Soon';
        }

        $movie_post_id = insert_movie($movie_title, 
                                     $movie['summary'], 
                                     $movie['releaseDate'], 
                                     $movie['runtime']['minutes'], 
                                     $movie['genres'], 
                                     $movie['rating']['id'], 
                                     $movie_link, 
                                     $movie_poster,
                                     $movie_status);

        if ($movie_post_id) {
            update_post_meta( $movie_post_id, 'HoytsID', sanitize_text_field( $movie['vistaId'] ) ); // Store vistaId as HoytsID
        }

        // $movie_html = generate_movie_html( $movie_title, $movie['summary'], $movie['releaseDate'], $movie['runtime']['minutes'], $movie['genres'], $movie['rating']['id'], 'https://hoyts.com.au' . $movie['link'] );
        
        // // Prepare the post data
        // $post_data = array(
        //     'post_title'    => $movie_title,
        //     'post_content'  => $movie_html,
        //     'post_status'   => 'publish',
        //     'post_type'     => 'movie', // Custom post type for movies
        // );
        
        // // Insert the post and get the post ID
        // $post_id = wp_insert_post( $post_data );

        // // If the post was successfully created
        // if ( $post_id ) {
        //     // Add additional metadata like release date, runtime, genres, etc.
        //     update_post_meta( $post_id, 'HoytsID', sanitize_text_field( $movie['vistaId'] ) ); // Store vistaId as HoytsID
        //     update_post_meta( $post_id, 'release_date', sanitize_text_field( $movie['releaseDate'] ) );
        //     update_post_meta( $post_id, 'runtime', intval( $movie['runtime']['minutes'] ) );
        //     update_post_meta( $post_id, 'genres', implode( ', ', array_map( 'sanitize_text_field', $movie['genres'] ) ) );
        //     update_post_meta( $post_id, 'rating', sanitize_text_field( $movie['rating']['id'] ) );
        //     update_post_meta( $post_id, 'link', esc_url( 'https://hoyts.com.au' . $movie['link'] ) );
        // }
        
        // // LOCALLY UPLOAD POSTER IMAGE FROM URL (CURRENTLY DEACTIVATED)!!!
        // if ( !empty( $movie['posterImage'] ) ) {
        //     // Upload the movie poster from the URL
        //     $poster_id = upload_image_from_url( 'https://imgix.hoyts.com.au/' . $movie['posterImage'], $post_id );
            
        //     if ( $poster_id ) {
        //         // If the poster was uploaded successfully, set it as the featured image
        //         set_post_thumbnail( $post_id, $poster_id );
        //     }

        //     update_post_meta( $post_id, 'img_link', esc_url( 'https://imgix.hoyts.com.au/' . $movie['posterImage'] ) );
        // }
        
        wp_reset_postdata(); // Reset the WP_Query data
    }
}

// Get Poster for movie (upload image from URL locally, USE AT OWN DISCRETION)
// CURRENTLY DEAVTICATED -> USES TOO MUCH RESOURCES! 
// CHANGED TO JSUT ADD LINK FOR IMAGE TO MOVIE

// ********************************************************************************
// ******************************* Sessions Fetcher *******************************
// ********************************************************************************

function hoyts_fetch_and_insert_sessions_all_venues(){
    global $venues;
    foreach($venues as $venue){
        hoyts_fetch_and_insert_sessions($venue->code, $venue->state, $venue->suburb);
    }
}

// Hook the session fetching function to a cron job or manual trigger
add_action( 'fetch_sessions', 'hoyts_fetch_and_insert_sessions_all_venues' );

// IN: $venue_code, $state, $suburb -> in array above called 'venues'.
function hoyts_fetch_and_insert_sessions($venue_code, $state, $suburb) {

    // Increase time limit to 5 minutes for this operation
    set_time_limit(480);

    // For Debug: logs
    //error_log("Tyring to Fetch sessions for $venue_code in $suburb, $state");

    // Define the API endpoint for sessions
    $api_url = 'https://apim.hoyts.com.au/au/cinemaapi/api/sessions/' . $venue_code; // Replace with the actual API URL for sessions
    
    // Fetch the JSON data from the API
    $response = wp_remote_get( $api_url );
    
    if ( is_wp_error( $response ) ) {
        error_log("Error in Session API Request", $response->get_error_message());
        return;
    }

    // Fail safe, if a cinema is added incorrectly, skips
    $response_code = wp_remote_retrieve_response_code( $response );
    if ( $response_code >= 400 ) {
        error_log("HTTP Error in Session API Request: " . $response_code);
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

        //FOR DEBUG
        //error_log("Processing session $session_id for movie $movie_hoyts_id");
        
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

        // Check if secondaryTags contains at least one accessibility
        $allowed_terms = ['AD', 'CC', 'OPEN CAP', 'SS'];
        $has_allowed_term = false;
        if (!empty($session['secondaryTags'])) {
            foreach ($session['secondaryTags'] as $tag) {
                if (in_array($tag, $allowed_terms)) {
                    $has_allowed_term = true;
                    break;
                }
            }
        }
        if (!$has_allowed_term) {
            continue;
        }

        $allowed_terms = ['AD', 'CC', 'OPEN CAP', 'SS'];
        $filtered_tags = array_filter($session['secondaryTags'], function($tag) use ($allowed_terms) {
            return in_array($tag, $allowed_terms);
        });
        
        $movie_post = $movie_query->posts[0]; // Get the movie post object
        $movie_post_id = $movie_post->ID; // Get the movie post ID
        
        //attach accessibilty taxonomies to parent movie
        add_accessibility_to_movie($movie_post_id, $filtered_tags);

        // extract date and time from the session date
        list($session_date, $session_time) = explode('T', $session['date']);
        list($session_utc_date, $session_utc_time) = explode('T', $session['utcDate']);

        $movie_title = get_the_title($movie_post_id);
        
        // Prepare the session post data
        $post_data = array(
            'post_title'    => '"' . $movie_title . '"' . ' at Hoyts ' . $suburb . ', ' . $state . ' on ' . $session_date . ' ' . $session_time, // Title for the session post
            'post_status'   => 'publish',
            'post_type'     => 'session', // Custom post type for sessions
            'post_content'  => generate_session_html($movie_title, $filtered_tags, 'Hoyts', $state, $suburb, $session_date, $session_time, esc_url( 'https://hoyts.com.au' . $session['link'])), // No content for session posts
            'post_parent'   => $movie_post_id // Set the movie as the parent post
        );
        
        // Insert the session post and get the post ID
        $session_post_id = wp_insert_post( $post_data );

        
        
        if ( $session_post_id ) {
            // Store additional metadata including the session ID
            update_post_meta( $session_post_id, 'session_id', $session_id ); // Store session ID to prevent duplicates
            update_post_meta( $session_post_id, 'cinema_id', sanitize_text_field( $session['cinemaId'] ) );
            update_post_meta( $session_post_id, 'session_date', sanitize_text_field( $session_date . " " . $session_time  ) );
            update_post_meta( $session_post_id, 'utc_date', sanitize_text_field( $session['utcDate'] ) );
            update_post_meta( $session_post_id, 'link', esc_url( 'https://hoyts.com.au' . $session['link'] ) );
            
            // Assign secondary tags to the Accessibility taxonomy
            if ( !empty( $session['secondaryTags'] ) && is_array($session['secondaryTags']) ) {
            
                if (!empty($filtered_tags)) {
                    foreach ($filtered_tags as $tag) {
                        $sanitized_tag = sanitize_text_field($tag);
                        wp_set_object_terms($session_post_id, $sanitized_tag, 'accessibility', true);
                    }
                }
            }

            // Assign the state to the state taxonomy if not already assigned
            if ( !has_term( 'VIC', 'state', $session_post_id ) ) {
                wp_set_object_terms( $session_post_id, $state, 'state', true );
            }

            // Assign 'Watergardens' to the suburb taxonomy if not already assigned
            if ( !has_term( $suburb, 'suburb', $session_post_id ) ) {
                wp_set_object_terms( $session_post_id, $suburb, 'suburb', true );
            }

            if ( !has_term( 'Hoyts', 'cinema', $session_post_id ) ) {
                wp_set_object_terms( $session_post_id, 'Hoyts', 'cinema', true );
            }
            
            wp_set_object_terms( $session_post_id, $session_date, 'date', true );
            wp_set_object_terms( $session_post_id, $session_time, 'time', true );
            wp_set_object_terms( $session_post_id, $session_utc_date, 'utc_date', true );
            wp_set_object_terms( $session_post_id, $session_utc_time, 'utc_time', true );

        }
        
        wp_reset_postdata(); // Reset the WP_Query data
    }
}

?>
