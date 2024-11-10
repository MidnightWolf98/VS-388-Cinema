<?php
// ************ HOYTS MODULE ************
// This module is responsible for fetching movie & its sessions from Hoyts Cinema

// list of venues & their codes, required for fetching sessions
// $venues = [
//     // VICTORIA VENUES FOR HOYTS
//        (object) [
//         'code' => 'BMWCIN',
//         'suburb' => 'Broadmeadows',
//         'state' => 'VIC'
//     ], (object) [
//         'code' => 'CHDSTN',
//         'suburb' => 'Chadstone',
//         'state' => 'VIC'
//     ], (object) [
//         'code' => 'DOCCIN',
//         'suburb' => 'District Docklands',
//         'state' => 'VIC'
//     ], (object) [
//         'code' => 'EASTLN',
//         'suburb' => 'Eastland',
//         'state' => 'VIC'
//     ], (object) [
//         'code' => 'FHLCIN',
//         'suburb' => 'Forrest Hill',
//         'state' => 'VIC'
//     ], (object) [
//         'code' => 'FRANKS',
//         'suburb' => 'Frankston',
//         'state' => 'VIC'
//     ], (object) [
//         'code' => 'GRENSB',
//         'suburb' => 'Greensborough',
//         'state' => 'VIC'
//     ], (object) [
//         'code' => 'HIGPNT',
//         'suburb' => 'Highpoint',
//         'state' => 'VIC'
//     ], (object) [
//         'code' => 'MCECIN',
//         'suburb' => 'Melbourne Central',
//         'state' => 'VIC'
//     ], (object) [
//         'code' => 'NORTHL',
//         'suburb' => 'Northland',
//         'state' => 'VIC'
//     ], (object) [
//         'code' => 'VGDCIN',
//         'suburb' => 'Victoria Gardens',
//         'state' => 'VIC'
//     ], (object) [
//         'code' => 'TAYLOR',
//         'suburb' => 'Watergardens',
//         'state' => 'VIC'
//     ], (object) [
//         'code' => 'BANKTN',
//         'suburb' => 'Bankstown',
//         'state' => 'NSW'
//     ], (object) [
//         'code' => 'WESCIN',
//         'suburb' => 'Blacktown',
//         'state' => 'NSW'
//     ], (object) [
//         'code' => 'BROADW',
//         'suburb' => 'Broadway',
//         'state' => 'NSW'
//     ], (object) [
//         'code' => 'CHARLE',
//         'suburb' => 'Charlestown',
//         'state' => 'NSW'
//     ], (object) [
//         'code' => 'CHWOOD',
//         'suburb' => 'Chatswood Mandarin',
//         'state' => 'NSW'
//     ], (object) [
//         'code' => 'CWFFLD',
//         'suburb' => 'Chatswood Westfield',
//         'state' => 'NSW'
//     ], (object) [
//         'code' => 'CROCIN',
//         'suburb' => 'Cronulla',
//         'state' => 'NSW'
//     ], (object) [
//         'code' => 'EGDENS',
//         'suburb' => 'Eastgardens',
//         'state' => 'NSW'
//     ], (object) [
//         'code' => 'SHOWGR',
//         'suburb' => 'Entertainment Quarter',
//         'state' => 'NSW'
//     ], (object) [
//         'code' => 'ERINAF',
//         'suburb' => 'Erina',
//         'state' => 'NSW'
//     ], (object) [
//         'code' => 'GHLCIN',
//         'suburb' => 'Green Hills',
//         'state' => 'NSW'
//     ], (object) [
//         'code' => 'MTDRTT',
//         'suburb' => 'Mt Druitt',
//         'state' => 'NSW'
//     ], (object) [
//         'code' => 'PENRTH',
//         'suburb' => 'Penrith',
//         'state' => 'NSW'
//     ], (object) [
//         'code' => 'TWDCTY',
//         'suburb' => 'Tweed City',
//         'state' => 'NSW'
//     ], (object) [
//         'code' => 'WWGCIN',
//         'suburb' => 'Warrawong',
//         'state' => 'NSW'
//     ], (object) [
//         'code' => 'WGHMAL',
//         'suburb' => 'Warringah Mall',
//         'state' => 'NSW'
//     ], (object) [
//         'code' => 'WETHER',
//         'suburb' => 'Wetherill Park',
//         'state' => 'NSW'
//     ], (object) [
//         'code' => 'ARNCIN',
//         'suburb' => 'Arndale',
//         'state' => 'SA'
//     ], (object) [
//         'code' => 'NWDCIN',
//         'suburb' => 'Norwood',
//         'state' => 'SA'
//     ], (object) [
//         'code' => 'SLYCIN',
//         'suburb' => 'Salisbury',
//         'state' => 'SA'
//     ], (object) [
//         'code' => 'TTPLZA',
//         'suburb' => 'Tea Tree Plaza',
//         'state' => 'SA'
//     ], (object) [
//         'code' => 'IPSCIN',
//         'suburb' => 'Ipswich',
//         'state' => 'QLD'
//     ], (object) [
//         'code' => 'REDCLF',
//         'suburb' => 'Redcliffe',
//         'state' => 'QLD'
//     ], (object) [
//         'code' => 'STAFRD',
//         'suburb' => 'Stafford',
//         'state' => 'QLD'
//     ], (object) [
//         'code' => 'SUNBNK',
//         'suburb' => 'Sunnybank',
//         'state' => 'QLD'
//     ], (object) [
//         'code' => 'BELCON',
//         'suburb' => 'Belconnen',
//         'state' => 'ACT'
//     ], (object) [
//         'code' => 'WODENP',
//         'suburb' => 'Woden',
//         'state' => 'ACT'
//     ], (object) [
//         'code' => 'MIDCIN',
//         'suburb' => 'Midland Gate',
//         'state' => 'WA'
//     ], (object) [
//         'code' => 'BUNCIN',
//         'suburb' => 'Bunbury',
//         'state' => 'WA'
//     ], (object) [
//         'code' => 'CAROUS',
//         'suburb' => 'Carousel',
//         'state' => 'WA'
//     ], (object) [
//         'code' => 'CURCIN',
//         'suburb' => 'Currambine',
//         'state' => 'WA'
//     ], (object) [
//         'code' => 'BOOGDN',
//         'suburb' => 'Garden City',
//         'state' => 'WA'
//     ], (object) [
//         'code' => 'JOOCIN',
//         'suburb' => 'Joondalup',
//         'state' => 'WA'
//     ], (object) [
//         'code' => 'KYPCIN',
//         'suburb' => 'Karrinyup',
//         'state' => 'WA'
//     ], (object) [
//         'code' => 'MILENM',
//         'suburb' => 'Milennium',
//         'state' => 'WA'
//     ], (object) [
//         'code' => 'SOUTHL',
//         'suburb' => 'Southlands',
//         'state' => 'WA'
//     ], (object) [
//         'code' => 'WARCIN',
//         'suburb' => 'Warwick',
//         'state' => 'WA'
//     ]
//     //ADD REST LATER
// ];

require_once plugin_dir_path(__FILE__) . 'hoyts-venues.php';

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

        //DEBUG!!!!
        //error_log("Processing movie $movie_title");

        // Use WP_Query to check if a post with the same movie title exists
        $args = array(
            'post_type'  => 'Movie', // Custom post type
            'title'      => $movie_title,
            'posts_per_page' => 1 // Limit to 1 result
        );
        $query = new WP_Query( $args );

        $movie_status = 'Unknown';

        if ($movie['type'] == 'nowShowing') {
            $movie_status = 'Now Showing';
        } elseif ($movie['type'] == 'comingSoon') {
            $movie_status = 'Coming Soon';
        } elseif ($movie['type'] == 'advanceSale') {
            $movie_status = 'Tickets on Sale, Release Soon';
        } elseif ($movie['type'] == 'advanceScreening') {
            $movie_status = 'Advance Screening';
        }
        
        if ( empty( $movie['releaseDate'] ) || empty($movie['posterImage']) ) {
            // skip movies that don't have a release date or no poster (these are speculated movies w/ no poster)
            // even further before coming soon movies. 
            continue;
        }

        // Set links
        $movie_link = 'https://hoyts.com.au' . $movie['link'];
        $movie_poster = 'https://imgix.hoyts.com.au/' . $movie['posterImage'];

        if ( $query->have_posts() ) {
            // If the movie already exists, update its status
            $existing_movie_id = $query->posts[0]->ID;        

            // Update the status taxonomy of the existing movie
            wp_set_object_terms($existing_movie_id, sanitize_text_field($movie_status), 'status');
            update_post_meta( $existing_movie_id, 'HoytsID', sanitize_text_field( $movie['vistaId'] ) ); // Store vistaId as HoytsID

            // Check if a poster is set for the movie post type
            if (!has_post_thumbnail($existing_movie_id)) {
                // If no poster is set, send the request to set the poster
                $poster_id = upload_image_from_url($movie_poster, $existing_movie_id, $movie_title);
                if ($poster_id) {
                    set_post_thumbnail($existing_movie_id, $poster_id);
                    //error_log("Poster set for movie $movie_title");
                } else {
                    error_log("Failed to set poster for movie $movie_title");
                }
            }

            wp_reset_postdata();
            continue;
        }

        // Format release date 
        list($release_date, $release_time) = explode('T', $movie['releaseDate']);
        $release_date_obj = DateTime::createFromFormat('Y-m-d', $release_date);
        $release_date_formatted = $release_date_obj ? $release_date_obj->format('d/m/Y') : $release_date;

        // Set runtime (ensure it exists, as some JSON movie objs dont have duration)
        $movie_runtime = isset($movie['duration']) ? $movie['duration'] : "Unknown";
        

        if(!$movie_status){
            $movie_status = 'Unknown';
        }

        $movie_post_id = insert_movie($movie_title, 
                                     $movie['summary'], 
                                     $release_date_formatted, 
                                     $movie_runtime, 
                                     $movie['genres'], 
                                     $movie['rating']['id'], 
                                     $movie_link, 
                                     $movie_poster,
                                     $movie_status);

        // Add the HoytsID as a meta field to the movie post
        if ($movie_post_id) {
            update_post_meta( $movie_post_id, 'HoytsID', sanitize_text_field( $movie['vistaId'] ) ); // Store vistaId as HoytsID
        }
        
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
    global $hoyts_venues;
    foreach($hoyts_venues as $venue){
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
        error_log("Hoyts Module: Error in Session API Request", $response->get_error_message());
        return;
    }

    // Fail safe, if a cinema is added incorrectly, skips
    $response_code = wp_remote_retrieve_response_code( $response );
    if ( $response_code >= 400 ) {
        error_log("Hoyts Module: HTTP Error in Session API Request: " . $response_code);
        return;
    }
    
    // Decode the JSON response
    $sessions = json_decode( wp_remote_retrieve_body( $response ), true );
    
    if ( empty( $sessions ) ) {
        error_log("Hoyts Module: No sessions found");
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
        
        // Attach accessibilty taxonomies to parent movie
        add_accessibility_to_movie($movie_post_id, $filtered_tags);

        // Prepare data to send to insert session
        list($session_date, $session_time) = explode('T', $session['date']);
        list($session_utc_date, $session_utc_time) = explode('T', $session['utcDate']);

        $movie_title = get_the_title($movie_post_id);

        $book_link = 'https://hoyts.com.au' . $session['link'];

        // Insert the session
        insert_session($movie_post_id, $movie_title, $filtered_tags, $session_date, $session_time, $session_utc_date, 
                       $session_utc_time, $session_id, $book_link, $state, $suburb, 'Hoyts');
        
        wp_reset_postdata(); // Reset the WP_Query data
    }
}

?>
