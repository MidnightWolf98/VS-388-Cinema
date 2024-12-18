<?php
/* FUNCTIONS IN THIS FILE:
    1. delete_old_sessions()
        As name suggests. CURRENTLY NOT WORKING!

    2. delete_all_movie_posters()
        deletes all posters attached to movie posts.

    3. delete_all_movies_and_sessions()
        As name suggests.
*/

// Function to delete old sessions FIX FUNCTION, DOESNT WORK! 
function delete_old_sessions() {
    // Set the time limit to avoid timeout issues
    set_time_limit(300);

    // Get the current date and time
    $current_time = current_time('timestamp');

    // Calculate the timestamp for 1 day ago
    $one_day_ago = strtotime('-1 day', $current_time);

    // Query to get all session posts
    $args = array(
        'post_type'      => 'session',
        'post_status'    => 'publish',
        'posts_per_page' => -1, // Get all matching posts
    );
    $sessions = get_posts($args);

    // Loop through each session post
    foreach ($sessions as $session) {
        // Get the term in the "Dates" taxonomy
        $dates_terms = wp_get_post_terms($session->ID, 'date', array('fields' => 'names'));

        // Check if the result is a WP_Error object
        if (is_wp_error($dates_terms)) {
            $error_message = $dates_terms->get_error_message();
            set_transient('delete_old_sessions_error', $error_message, 60);
            error_log('Error retrieving terms for session ID ' . $session->ID . ': ' . $error_message);
            continue; // Skip this session if there's an error
        }

        // Convert the array of dates to a string for logging
        $dates_string = implode(', ', $dates_terms);
        //error_log('Checking session ID ' . $session->ID . ' with dates ' . $dates_string . ' against today: '. $current_time);

        // Check if the date is more than 1 day old
        if (!empty($dates_terms)) {
            $date = $dates_terms[0]; // Assuming each session has only one date
            $date_timestamp = strtotime($date);
            if ($date_timestamp < $one_day_ago) {
                //error_log('Deleting session ID ' . $session->ID . ' with date ' . $date);
                wp_delete_post($session->ID, true); // true to force delete
            }
        }
    }
}

function delete_posters_movies_sessions(){
    delete_all_movie_posters();
    delete_all_movies_and_sessions();
}

// Function to delete all movie posters (call this function from admin panel options)
function delete_all_movie_posters() {
    // Get all movie posts
    $args = array(
        'post_type'      => 'movie',
        'post_status'    => 'publish',
        'numberposts'    => -1,
    );
    $movies = get_posts($args);

    // Loop through each movie post
    foreach ($movies as $movie) {
        // Get the ID of the attached poster (featured image)
        $poster_id = get_post_thumbnail_id($movie->ID);

        // If a poster is attached, delete it
        if ($poster_id) {
            wp_delete_attachment($poster_id, true); // true to force delete
        }
    }
}

function delete_all_movies_and_sessions() {
    // Set the time limit to avoid timeout issues
    set_time_limit(500);

    // Get all movie posts
    $movie_args = array(
        'post_type'      => 'movie',
        'post_status'    => 'any', // Include all statuses
        'numberposts'    => -1,
    );
    $movies = get_posts($movie_args);

    // Loop through each movie post and delete it
    foreach ($movies as $movie) {
        wp_delete_post($movie->ID, true); // true to force delete
    }

    // Get all session posts
    $session_args = array(
        'post_type'      => 'session',
        'post_status'    => 'any', // Include all statuses
        'numberposts'    => -1,
    );
    $sessions = get_posts($session_args);

    // Loop through each session post and delete it
    foreach ($sessions as $session) {
        wp_delete_post($session->ID, true); // true to force delete
    }
}

// Function to delete old date taxonomies
function delete_old_date_taxonomies() {
    // Get all date terms
    $date_terms = get_terms(array(
        'taxonomy'   => 'date',
        'hide_empty' => false,
    ));

    // Get the current date and time
    $current_time = current_time('timestamp');

    // Calculate the timestamp for 1 day ago
    $one_day_ago = strtotime('-1 day', $current_time);

    // Loop through each date term
    foreach ($date_terms as $date_term) {
        // Get the date string from the term
        $date_string = $date_term->name;

        // Convert the date string to a timestamp
        $date_timestamp = strtotime($date_string);

        // Check if the date is more than 1 day old
        if ($date_timestamp < $one_day_ago) {
            // Delete the date term
            wp_delete_term($date_term->term_id, 'date');
        }
    }
}

?>