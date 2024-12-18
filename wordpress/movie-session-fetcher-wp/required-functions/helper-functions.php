<?php
/* FUNCTIONS IN THIS FILE:
    1. insert_movie()
        Inserts a movie post with the given details
    
    2. insert_session()
        Inserts a session post with the given details
    
    3. upload_image_from_url()
        Downloads an image from a URL and uploads it to the media library
    
    4. get_attachment_id_by_filename()
        Retrieves the attachment ID of an image by its filename
    
    5. add_accessibility_to_movie()
        Adds accessibility terms (taxonomy + metadata) to a movie post
    
    6. generate_movie_html()
        Generates HTML content for a movie post
    
    7. generate_session_html()
        Generates HTML content for a session post
    
    8. format_date()
        Formats a date from 'Y-m-d' to 'd/m/Y'
    
    9. format_time()
        Formats a time from 'H:i:s' to 'g:i A'
*/


/* INSERT_MOVIE -> universal function to insert a movie post into the WordPress database
    IN: $title => movie title
        $summary => movie summary
        $release_date => movie release date
        $runtime => movie runtime in minutes
        $genres => array of movie genres
        $rating => movie rating
        $link => (optional) URL for more information about the movie
        $poster_url => (optional) URL of the movie poster image
        $status => (optional) status of the movie (default: 'Unknown')
    OUT: $post_id => ID of the inserted movie post or null if insertion failed
*/
function insert_movie($title, $summary, $release_date, $runtime, $genres, 
                      $rating, $link = null, $poster_url = null, $status = 'Unknown') {

    //generate the movie html
    $movie_html = generate_movie_html( $title, $summary, $release_date, 
                                       $runtime, $genres, $rating ,$link );

    // Prepare the post data
    $post_data = array(
        'post_title'    => $title,
        'post_content'  => $movie_html,
        'post_status'   => 'publish',
        'post_type'     => 'movie', // Custom post type for movies
    );

    $post_id = wp_insert_post( $post_data );

    if ( $post_id ) {
        // Add additional metadata like release date, runtime, genres, etc.
        update_post_meta( $post_id, 'release_date', sanitize_text_field( $release_date ) );
        update_post_meta( $post_id, 'runtime', intval( $runtime ) );
        update_post_meta( $post_id, 'genres', implode( ', ', array_map( 'sanitize_text_field', $genres ) ) );
        update_post_meta( $post_id, 'rating', sanitize_text_field( $rating ) );
        update_post_meta( $post_id, 'link', esc_url( $link ) );

        if ( !empty( $poster_url ) ) {
            // Upload the movie poster from the URL
            $poster_id = upload_image_from_url( $poster_url, $post_id, $title );
            
            if ( $poster_id ) {
                // If the poster was uploaded successfully, set it as the featured image
                set_post_thumbnail( $post_id, $poster_id );
            }

            update_post_meta( $post_id, 'img_link', esc_url( $poster_url ) );   
        }

        else{
            // Create a placeholder image for the movie poster
            $placeholder_id = create_placeholder_poster($title);
            set_post_thumbnail($post_id, $placeholder_id);
        }
        
        // Set the status taxonomy of the movie to given or default
        wp_set_object_terms( $post_id, sanitize_text_field( $status ), 'status' );

        return $post_id; // Return the post ID of the inserted movie
    }
    // Return nothing when the movie couldn't be inserted 
}


/* INSERT SESSION FUNCTION
    IN: $movie_post_id => ID of the parent movie post
        $movie_title => title of the movie
        $access_tags => array of accessibility tags
        $s_date => session date in 'Y-m-d' format
        $s_time => session time in 'H:i:s' format
        $utc_date => UTC date of the session in 'Y-m-d' format
        $utc_time => UTC time of the session in 'H:i:s' format
        $session_id => ID of the session
        $link => (optional) URL for more information about the session
        $state => state where the cinema is located
        $suburb => suburb where the cinema is located
        $cinema => name of the cinema
    OUT: $session_post_id => ID of the inserted session post or null if insertion failed
*/
function insert_session($movie_post_id, $movie_title, $access_tags, $s_date, $s_time, $utc_date, $utc_time, 
                        $session_id, $link='', $state, $suburb, $cinema ) {
    
    $formattted_date = format_date($s_date);
    $formatted_time = format_time($s_time);
    
    // Prepare the post data
    $post_data = array(
        'post_title'    => '"' . $movie_title . '"' . ' at '. $cinema . ' ' . $suburb . ', ' . $state . ' on ' . $formattted_date . ' ' . $formatted_time, // Title for the session post
        'post_status'   => 'publish',
        'post_type'     => 'session', // Custom post type for sessions
        'post_content'  => generate_session_html($movie_title, $access_tags, $cinema, $state, $suburb, $formattted_date, $formatted_time, esc_url( $link )), // No content for session posts
        'post_parent'   => $movie_post_id // Set the movie as the parent post
    );

    // Insert the session post and get the post ID
    $session_post_id = wp_insert_post( $post_data );

    if ( $session_post_id ) {
        // Store additional metadata including the session ID
        update_post_meta( $session_post_id, 'session_id', $session_id ); // Store session ID to prevent duplicates
        update_post_meta( $session_post_id, 'session_date', sanitize_text_field( $s_date . " " . $s_time  ) );
        update_post_meta( $session_post_id, 'utc_date', sanitize_text_field( $utc_date) );
        update_post_meta( $session_post_id, 'link', esc_url( $link ) );
        
        // Assign secondary tags to the Accessibility taxonomy
        if (!empty($access_tags)) {
            foreach ($access_tags as $tag) {
                $sanitized_tag = sanitize_text_field($tag);
                wp_set_object_terms($session_post_id, $sanitized_tag, 'accessibility', true);
            }
        }
    

        // Assign the state to the state taxonomy if not already assigned
        if ( !has_term( $state, 'state', $session_post_id ) ) {
            wp_set_object_terms( $session_post_id, $state, 'state', true );
        }

        // Assign given suburb to the suburb taxonomy if not already assigned
        if ( !has_term( $suburb, 'suburb', $session_post_id ) ) {
            wp_set_object_terms( $session_post_id, $suburb, 'suburb', true );
        }

        // Assign given cinema to the suburb taxonomy if not already assigned
        if ( !has_term( $cinema, 'cinema', $session_post_id ) ) {
            wp_set_object_terms( $session_post_id, $cinema, 'cinema', true );
        }
        
        wp_set_object_terms( $session_post_id, $s_date, 'date', true );
        wp_set_object_terms( $session_post_id, $s_time, 'time', true );
        wp_set_object_terms( $session_post_id, $utc_date, 'utc_date', true );
        wp_set_object_terms( $session_post_id, $utc_time, 'utc_time', true );

    }

}

/* UPLOAD IMAGE FROM URL FUNCTION
    IN: $image_url => URL of the image to be downloaded
        $post_id => ID of the post to attach the image to

    OUT: $attachment_id => ID of the uploaded image attachment or false if upload failed
*/                
function upload_image_from_url($image_url, $post_id, $movie_title='') {
    
    // Get the file name of the image
    $file_name = basename($image_url);

    // Check if the image has already been uploaded
    $existing_attachment_id = get_attachment_id_by_filename($file_name);
    if ($existing_attachment_id) {
        // If the image is already uploaded, return the existing attachment ID
        return $existing_attachment_id;
    }

    // Make a HEAD request to get the file headers
    $head_response = wp_remote_head($image_url);

    if (is_wp_error($head_response)) {
        return false; // Handle error if the HEAD request failed
    }

    // Get the Content-Length header to check the file size
    $content_length = wp_remote_retrieve_header($head_response, 'content-length');

    if ($content_length && $content_length > 27262976) { // 26 MB limit
        $image_url = 'https://via.placeholder.com/300x450?text=' . urlencode($movie_title);
    }
    
    // Download the image from the URL
    $response = wp_remote_get($image_url);
    
    if (is_wp_error($response)) {
        return false; // Handle error if the image couldn't be downloaded
    }
    
    // Get the image contents and save it to the uploads directory
    $image_data = wp_remote_retrieve_body($response);
    $upload_dir = wp_upload_dir();
    
    // Check if the uploads directory is writable
    if (wp_mkdir_p($upload_dir['path'])) {
        $file_path = $upload_dir['path'] . '/' . $file_name;
    } else {
        $file_path = $upload_dir['basedir'] . '/' . $file_name;
    }
    
    // Save the image data to the file path
    file_put_contents($file_path, $image_data);
    
    // Prepare an array of file information to simulate a file upload
    $file = array(
        'name'     => $file_name,
        'type'     => wp_remote_retrieve_header($response, 'content-type'),
        'tmp_name' => $file_path,
        'error'    => 0,
        'size'     => filesize($file_path),
    );
    
    // Include the necessary WordPress file to handle uploads
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Upload the image to the media library
    $attachment_id = media_handle_sideload($file, $post_id);

    // If there was an error uploading the image, handle it
    if (is_wp_error($attachment_id)) {
        return false;
    }

    // Store the file name as metadata for the attachment
    update_post_meta($attachment_id, '_source_image_filename', sanitize_file_name($file_name));

    // Set the uploaded image as the post's featured image
    set_post_thumbnail($post_id, $attachment_id);

    return $attachment_id; // Return the attachment ID of the image
}

/* GET ATTACHMENT ID BY FILENAME FUNCTION
    IN: $file_name => name of the image file
    OUT: $attachment_id => ID of the image attachment or null if not found
*/
function get_attachment_id_by_filename($file_name) {
    global $wpdb;
    $attachment_id = $wpdb->get_var($wpdb->prepare("
        SELECT post_id FROM $wpdb->postmeta
        WHERE meta_key = '_source_image_filename'
        AND meta_value = %s
    ", sanitize_file_name($file_name)));
    return $attachment_id;
}

/* ADD ACCESSIBILITY TO MOVIE FUNCTION
    IN: $movie_id => ID of the movie post
        $new_accessibility_terms => array of new accessibility terms to add
    OUT: void
*/
function add_accessibility_to_movie($movie_id, $new_accessibility_terms) {
    // Get current accessibility terms for the movie
    $current_terms = wp_get_post_terms($movie_id, 'accessibility', array('fields' => 'names'));

    // Merge new terms with existing terms, ensuring no duplicates
    $all_terms = array_unique(array_merge($current_terms, $new_accessibility_terms));

    // Attach unique accessibility terms to the parent movie
    if (!empty($all_terms) & ($all_terms != $current_terms)) {

        // Update the taxomomies
        wp_set_object_terms($movie_id, $all_terms, 'accessibility', false);

        // Add to Custom-Fields
        update_post_meta($movie_id, 'supported_accessibility', implode(" ", $all_terms));
    }
}

/* GENERATE MOVIE HTML FUNCTION
    IN: $movie_title => title of the movie
        $summary => summary of the movie
        $release_date => release date of the movie
        $runtime => runtime of the movie in minutes
        $genres => array of genres associated with the movie
        $rating => rating of the movie
        $link => URL for more information about the movie
    OUT: $html => generated HTML content for the movie
*/
function generate_movie_html($movie_title, $summary, $release_date, $runtime, $genres, $rating, $link) {
    // Sanitize the input fields
    $movie_title = sanitize_text_field($movie_title);
    $summary = sanitize_text_field($summary);
    $release_date = sanitize_text_field($release_date);
    $runtime = intval($runtime);
    $genres = implode(', ', array_map('sanitize_text_field', $genres));
    $rating = sanitize_text_field($rating);
    $link = esc_url($link);

    // Generate the HTML content
    $html = '<div class="movie">';
    $html .= '<p>' . $summary . '</p>';
    $html .= '<p><strong>Release Date:</strong> ' . $release_date . '</p>';

    if ($runtime > 0)
        $html .= '<p><strong>Runtime:</strong> ' . $runtime . ' minutes</p>';
    else
        $html .= '<p><strong>Runtime:</strong> Unknown</p>';

    $html .= '<p><strong>Genres:</strong> ' . $genres . '</p>';
    $html .= '<p><strong>Rating:</strong> ' . $rating . '</p>';
    $html .= '<p><a style"colour:green;" href="' . $link . '" target="_blank">More Info</a></p>';
    $html .= '</div>';

    return $html;
}

/* GENERATE SESSION HTML FUNCTION
    IN: $movie_title => title of the movie
        $acc_tags => array of accessibility tags
        $cinema => name of the cinema
        $state => state where the cinema is located
        $suburb => suburb where the cinema is located
        $s_date => session date, MUST PRE FORMATTED
        $s_time => session time, MUST BE PRE FORMATTED
        $link => URL to book the session
    OUT: $html => generated HTML content for the session
*/
function generate_session_html($movie_title, $acc_tags, $cinema, $state, $suburb, $s_date, $s_time, $link) {
    // Sanitize the input fields
    $cinema = sanitize_text_field($cinema);
    $state = sanitize_text_field($state);
    $suburb = sanitize_text_field($suburb);
    $s_date = sanitize_text_field($s_date);
    $s_time = sanitize_text_field($s_time);
    $link = esc_url($link);

    // Generate the HTML content
    $html = '<div class="session">';
    $html .= '<p><strong>Accessibility:</strong> ' . implode(', ', $acc_tags) . '</p>';
    $html .= '<p><strong>Location:</strong> ' . $suburb . ', ' . $state . '</p>';
    $html .= '<p><strong>Date:</strong> ' . $s_date . '</p>';
    $html .= '<p><strong>Time:</strong> ' . $s_time . '</p>';
    $html .= '<p><a style"colour:green;" href="' . $link . '" target="_blank">Book Now</a></p>';
    $html .= '</div>';

    return $html;
}

/* FORMAT DATE FUNCTION
    IN: $date => date in 'Y-m-d' format
    OUT: $formatted_date => date in 'd/m/Y' format or original date if invalid
*/
function format_date($date) {
    $date_obj = DateTime::createFromFormat('Y-m-d', $date);
    return $date_obj ? $date_obj->format('d/m/Y') : $date;
}

/* FORMAT TIME FUNCTION
    IN: $time => time in 'H:i:s' format
    OUT: $formatted_time => time in 'g:i A' format or original time if invalid
*/
function format_time($time) {
    $time_obj = DateTime::createFromFormat('H:i:s', $time);
    return $time_obj ? $time_obj->format('g:i A') : $time;
}

function create_placeholder_poster($movie_title) {
    // Create a placeholder image for the movie poster
    
    

}

?>