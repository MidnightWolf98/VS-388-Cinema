<?php
/* FUNCTIONS IN THIS FILE:
    1. insert_movie()
        not yet implemented
    
    2. insert_session()
        not yet implemented
    
    3. upload_image_from_url()
        Downloads an image from a URL and uploads it to the media library
    
    4. get_attachment_id_by_filename()
        Retrieves the attachment ID of an image by its filename
    
    5. add_accessibility_to_movie()
        Adds accessibility terms (taxonomy + metadata) to a movie post
    
    6. generate_movie_html()
        Generates HTML content for a movie post
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
function insert_movie($title, $summary, $release_date, $runtime, $genres, $rating, $link = null, $poster_url = null, $status = 'Unknown') {

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
            $poster_id = upload_image_from_url( $poster_url, $post_id );
            
            if ( $poster_id ) {
                // If the poster was uploaded successfully, set it as the featured image
                set_post_thumbnail( $post_id, $poster_id );
            }

            update_post_meta( $post_id, 'img_link', esc_url( $poster_url ) );   
        }
        
        // Set the status taxonomy of the movie to given or default
        wp_set_object_terms( $post_id, sanitize_text_field( $status ), 'status' );

        return $post_id; // Return the post ID of the inserted movie
    }
    // Return nothing when the movie couldn't be inserted 
}


// UNIMPLEMENTED
function insert_session($movie_id, $access_tags, $s_date, 
                        $s_time, $utc_date, $utc_time, 
                        $session_id, $cinema_id, $link,
                        $state, $suburb, $cinema){}


/* UPLOAD IMAGE FROM URL FUNCTION
    IN: $image_url => URL of the image to be downloaded
        $post_id => ID of the post to attach the image to

    OUT: $attachment_id => ID of the uploaded image attachment or false if upload failed
*/                
function upload_image_from_url($image_url, $post_id) {
    
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

    if ($content_length && $content_length > 15728640) { // 10 MB limit
        return null; // return no poster if the file size is too larges
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
        $s_date => session date
        $s_time => session time
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

?>