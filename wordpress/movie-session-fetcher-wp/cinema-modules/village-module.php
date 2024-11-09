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

    function village_fetch_and_insert_movies($village_session_id) {

        // Increase time limit to 5 minutes for this operation
        set_time_limit(600); 
        
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
    function village_fetch_and_insert_sessions($village_session_id) {
        return;
    }
?>