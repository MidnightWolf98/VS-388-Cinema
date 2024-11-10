<?php

    // Import venues file 
    require_once plugin_dir_path(__FILE__) . 'village-venues.php';

    // Village Cinema Module 
    function village_fetch_all_movies_and_sessions(){
        // Get the session key
        $village_session_id = village_get_session_key();

        // Check if the session key is available
        if (empty($village_session_id)) {
            error_log("Village Module: Session Key not available, unable to fetch movies and sessions.");
            return;
        }

        // Fetch all movies
        village_get_all_movies($village_session_id);

        // Fetch all sessions
        village_fetch_and_insert_sessions_all_venues($village_session_id);
    }
    
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

    function village_get_all_movies($village_session_id = '') {

        // Increase time limit to 5 minutes for this operation
        set_time_limit(600); 
        
        //api endpoints
        $api_url_now_showing = 'https://villagecinemas.com.au/api/film/getMoviesNowShowing?userSessionId=';
        $api_url_coming_soon = 'https://villagecinemas.com.au/api/film/getMoviesComingSoon?userSessionId=';

        village_get_movies_from_api($api_url_now_showing);
        village_get_movies_from_api($api_url_coming_soon);
    }

    function village_get_movies_from_api($api_url){
        
        set_time_limit(600); // Increase time limit to 5 minutes for this operation

        // Get JSON from given API
        $response = wp_remote_get($api_url);

        // Error Check
        if (is_wp_error($response)) {
            error_log("Village Module: Failed to retrieve movies from " . $api_url);
            return;
        }

        //Decode JSON
        $movies = json_decode( wp_remote_retrieve_body( $response ), true)['Items'];

        // Empty Check
        if (empty($movies)) {
            error_log("Village Module: No movies found in the response from " . $api_url);
            return;
        }

        // Loop thorugh movies
        foreach ($movies as $movie) {

            // DEBUG - Log the movie being processed
            error_log("Village Module: Processing movie " . $movie['Title']);

            $movie_title = sanitize_text_field( $movie['Title'] );

            // Use WP_Query to check if a post with the same movie title exists
            $args = array(
                'post_type'  => 'Movie', // Custom post type
                'title'      => $movie_title,
                'posts_per_page' => 1 // Limit to 1 result
            );
            $query = new WP_Query( $args );

            // village id to exisitng post
            if ( $query->have_posts() ) {
                // If the movie already exists, update its status
                $existing_movie_id = $query->posts[0]->ID;        

                // Update the status taxonomy of the existing movie
                update_post_meta( $existing_movie_id, 'villageID', sanitize_text_field( $movie['MovieId'] ) ); // Store vistaId as HoytsID

                wp_reset_postdata();
                continue;
            }

            // **********************************
            // TODO: ADD NEW MOVIE POST CODE HERE
            // **********************************

        } // End of movie loop
    } // End of function - village_get_movies_from_api
    
    // ********************************************************************************
    // ******************************* Sessions Fetcher *******************************
    // ********************************************************************************
    
    function village_fetch_and_insert_sessions_all_venues($village_session_id = ''){
        global $village_venues;
        foreach ($village_venues as $vil_venue) {
            village_fetch_and_insert_sessions($vil_venue->id, $vil_venue->state, $vil_venue->suburb);
        }
    }
    
    // Hook the session fetching function to a cron job or manual trigger
    add_action( 'fetch_sessions', 'village_fetch_and_insert_sessions_all_venues' );
    
    // IN: $venue_code, $state, $suburb -> in array above called 'venues'.
    function village_fetch_and_insert_sessions($venue_code, $state, $suburb) {
        
        set_time_limit(600);

        $api_url = 'https://villagecinemas.com.au/api/session/getMovieSessions?cinemaId=' . $venue_code;

        error_log("Village Module: Fetching sessions from " . $api_url);
        
        $response = wp_remote_get( $api_url );

        if ( is_wp_error( $response ) ) {
            error_log("Village Module: Error in Session API Request", $response->get_error_message());
            return;
        }

        // Decode the JSON response
        $movie_sessions = json_decode( wp_remote_retrieve_body( $response ), true )['Items'];
        
        if ( empty( $movie_sessions ) ) {
            error_log("Village Module: No sessions found");
            return;
        }
        //***********************************************//
        //************* LOOP THROUGH MOVIES *************//
        //***********************************************//
        foreach ($movie_sessions as $movie){

            //DEBUG 
            error_log("Village Module: Processing Movie " . $movie['Title'] . " at " . $suburb . " in " . $state);

            $movie_title = sanitize_text_field($movie['Title']);
            $village_id = sanitize_text_field($movie['MovieId']);

            // Get parent post id
            $args = array(
                'post_type'  => 'movie',
                'meta_query' => array(
                    array(
                        'key'   => 'villageID', // This assumes 'hoytsID' was stored when creating the movie
                        'value' => $village_id,
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
            
            //*************************************************//
            //************* LOOP THROUGH SESSIONS *************//
            //*************************************************//
            foreach ($movie['Sessions'] as $session) {
                error_log("Village Module: Processing Session " . $session['ShowDateTime'] . " for " . $movie_title . " at " . $suburb . " in " . $state);
                // Find existing session by session ID
                $session_id = sanitize_text_field($session['SessionId']);
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

                // Get the session details
                $session_attributes = $session['Attributes'];
                $allowed_terms = ['CC', 'OC', 'AD', 'SFF'];
                $matching_attributes = array();

                // Loop through the attributes and look for specific ShortName values
                foreach ($session_attributes as $attribute) {
                    if (in_array($attribute['ShortName'], $allowed_terms)) {
                        $matching_attributes[] = $attribute['ShortName'];
                    }
                }

                // Ensure the matching_attributes array only includes the specified ShortName values and remove duplicates
                $matching_attributes = array_unique(array_filter($matching_attributes, function($shortName) use ($allowed_terms) {
                    return in_array($shortName, $allowed_terms);
                }));

                // Convert "OC" to "OPEN CAP" if found
                foreach ($matching_attributes as &$shortName) {
                    if ($shortName === 'OC') {
                        $shortName = 'OPEN CAP';
                    }
                }

                if (empty($matching_attributes)) {
                    // If the session doesn't have the required attributes, skip it
                    continue;
                }

                error_log("Village Module: " . implode(', ', array_column($matching_attributes, 'ShortName')) . " attributes found for session " . $session_id);
                add_accessibility_to_movie($movie_post_id, $matching_attributes);

                // Process sesson info
                list($session_date, $session_time_utc) = explode('T', $session['ShowDateTime']);
                list($session_time, $utc_identifier) = explode('+', $session_time_utc);

                $book_link = 'https://villagecinemas.com.au/tickets?sessionId=' . $session_id . '&cinemaId=' . $venue_code;

                insert_session($movie_post_id, $movie_title, $matching_attributes, $session_date, $session_time, $session_date, 
                       $session_time_utc, $session_id, $book_link, $state, $suburb, 'Village');
                wp_reset_postdata();

            } // End of session loop
        } // End of movie loop 

        return;
    }
?>