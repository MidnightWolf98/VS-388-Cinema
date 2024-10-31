<?php
// HELPER FUNCTIONS INCLUDING:

// 1. Function to delete old sessions FIX FUNCTION, DOESNT WORK! 
function delete_old_sessions($wpdb) {
    set_time_limit(300);
    $wpdb->query("DELETE FROM wp_movie_sessions WHERE session_date < DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
}

function insert_movie(/*add variables*/){}

function insert_session(/*add variables*/){}

// Helper function to get attachment ID by image URL
function get_attachment_id_by_url($image_url) {
    global $wpdb;
    $attachment_id = $wpdb->get_var($wpdb->prepare("
        SELECT post_id FROM $wpdb->postmeta
        WHERE meta_key = '_source_image_url'
        AND meta_value = %s
    ", esc_url_raw($image_url)));
    return $attachment_id;
}

function upload_image_from_url($image_url, $post_id) {

    // Check if the image has already been uploaded
    $existing_attachment_id = get_attachment_id_by_url($image_url);
    if ($existing_attachment_id) {
        // If the image is already uploaded, return the existing attachment ID
        return $existing_attachment_id;
    }

    // Get the file name of the image
    $file_name = basename($image_url);
    
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

    // Set the uploaded image as the post's featured image
    set_post_thumbnail($post_id, $attachment_id);

    return $attachment_id; // Return the attachment ID of the image
}

// module runner entry point for accessibility taxonomy
function attach_accessibility_to_all_movies() {
    // Get all movies
    $args = array(
        'post_type'   => 'movie',
        'post_status' => 'publish',
        'numberposts' => -1,
    );
    $movies = get_posts($args);

    // Loop through each movie and call attach_accessibility_to_movie
    foreach ($movies as $movie) {
        attach_accessibility_to_movie($movie->ID);
    }
}

function attach_accessibility_to_movie($movie_id) {
    // Get all sessions that are children of the movie
    $args = array(
        'post_type'   => 'session',
        'post_status' => 'publish',
        'numberposts' => -1,
        'post_parent' => $movie_id,
    );
    $sessions = get_posts($args);

    // Initialize an array to store unique accessibility terms
    $all_accessibility_terms = array();

    // Loop through each session and get its accessibility terms
    foreach ($sessions as $session) {
        $session_accessibility_terms = wp_get_post_terms($session->ID, 'accessibility', array('fields' => 'ids'));
        if (!is_wp_error($session_accessibility_terms) && !empty($session_accessibility_terms)) {
            $all_accessibility_terms = array_merge($all_accessibility_terms, $session_accessibility_terms);
        }
    }

    // Remove duplicate terms
    $all_accessibility_terms = array_unique($all_accessibility_terms);

    // Attach unique accessibility terms to the parent movie
    if (!empty($all_accessibility_terms)) {
        wp_set_object_terms($movie_id, $all_accessibility_terms, 'accessibility', false);
    }
}

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
    $html .= '<h2>' . $movie_title . '</h2>';
    $html .= '<p>' . $summary . '</p>';
    $html .= '<p><strong>Release Date:</strong> ' . $release_date . '</p>';

    if ($runtime > 0)
        $html .= '<p><strong>Runtime:</strong> ' . $runtime . ' minutes</p>';
    else
        $html .= '<p><strong>Runtime:</strong> Unknown</p>';

    $html .= '<p><strong>Genres:</strong> ' . $genres . '</p>';
    $html .= '<p><strong>Rating:</strong> ' . $rating . '</p>';
    $html .= '<p><a href="' . $link . '" target="_blank">More Info</a></p>';
    $html .= '</div>';

    return $html;
}

?>