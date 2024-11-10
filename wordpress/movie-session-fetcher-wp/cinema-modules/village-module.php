<?php

    // Import venues file 
    require_once plugin_dir_path(__FILE__) . 'village-venues.php';

    // Village Cinema Module 
    function village_fetch_all_movies_and_sessions(){}
    
    function village_get_session_key(){
        // Get the Session Key
        $homepage = file_get_contents('https://villagecinemas.com.au/');

        if ($homepage === false) {
            error_log("Village Module: Failed to retrieve the homepage for session key.");
            return;
        }

        // Create a new DOMDocument instance
        $dom = new DOMDocument();

        // Suppress errors due to malformed HTML
        libxml_use_internal_errors(true);

        // Load the HTML into the DOMDocument
        $dom->loadHTML($homepage);

        // Clear the libxml error buffer
        libxml_clear_errors();

        // Create a new DOMXPath instance
        $xpath = new DOMXPath($dom);

        // Query for the input element with the id 'user-session-id'
        $input = $xpath->query('//input[@id="user-session-id"]');

        if ($input->length > 0) {
            // Get the value of the input element that contains the session key
            $village_session_id = $input->item(0)->getAttribute('value');
            return $village_session_id;
        } else {
            error_log("Village Module: Input element with id 'user-session-id' not found, Session Key not available.");
        }
    }

    function village_get_all_movies($village_session_id) {

        // Increase time limit to 5 minutes for this operation
        set_time_limit(600); 
        
        //api endpoints
        $api_url_now_showing = 'https://villagecinemas.com.au/api/film/getMoviesNowShowing?userSessionId=';
        $api_url_coming_soon = 'https://villagecinemas.com.au/api/film/getMoviesComingSoon?userSessionId=';

    }

    function village_get_movies_from_api($api_url){

        // Get JSON from given API
        $response = wp_remote_get($api_url);

        // Error Check
        if (is_wp_error($response)) {
            error_log("Village Module: Failed to retrieve movies from " . $api_url);
            return;
        }

        //Decode JSON
        $movies = json_decode( wp_remote_retrieve_body( $response ), true);

        // Empty Check
        if (empty($movies)) {
            error_log("Village Module: No movies found in the response from " . $api_url);
            return;
        }

        // Loop thorugh movies
        foreach ($movies as $movie) {
            $movie_title = sanitize_text_field( $movie['Title'] );

            // Use WP_Query to check if a post with the same movie title exists
            $args = array(
                'post_type'  => 'Movie', // Custom post type
                'title'      => $movie_title,
                'posts_per_page' => 1 // Limit to 1 result
            );
            $query = new WP_Query( $args );
        }

        // village id to exisitng post
        if ( $query->have_posts() ) {
            // If the movie already exists, update its status
            $existing_movie_id = $query->posts[0]->ID;        

            // Update the status taxonomy of the existing movie
            update_post_meta( $existing_movie_id, 'VillageID', sanitize_text_field( $movie['MovieId'] ) ); // Store vistaId as HoytsID

            wp_reset_postdata();
            continue;
        }

        //ADD NEW MOVIE POST CODE HERE

    }
    
    // ********************************************************************************
    // ******************************* Sessions Fetcher *******************************
    // ********************************************************************************
    
    function village_fetch_and_insert_sessions_all_venues($village_session_id){
        return;
    }
    
    // Hook the session fetching function to a cron job or manual trigger
    add_action( 'fetch_sessions', 'village_fetch_and_insert_sessions_all_venues' );
    
    // IN: $venue_code, $state, $suburb -> in array above called 'venues'.
    function village_fetch_and_insert_sessions($village_session_id, $venue_code, $state, $suburb) {
        return;
    }
?>