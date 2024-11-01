<?php
// HELPER FUNCTIONS INCLUDING:

// 1. Function to delete old sessions FIX FUNCTION, DOESNT WORK! 
function delete_old_sessions() {
    // Set the time limit to avoid timeout issues
    set_time_limit(300);

    // Get the current date and time
    $current_time = current_time('timestamp');

    // Calculate the timestamp for 1 day ago
    $one_day_ago = strtotime('-1 day', $current_time);

    // Query to get all session posts that are more than 1 day old
    $args = array(
        'post_type'      => 'session',
        'post_status'    => 'publish',
        'date_query'     => array(
            array(
                'before' => date('Y-m-d H:i:s', $one_day_ago),
                'inclusive' => true,
            ),
        ),
        'posts_per_page' => -1, // Get all matching posts
    );
    $old_sessions = get_posts($args);

    // Loop through each session post and delete it
    foreach ($old_sessions as $session) {
        wp_delete_post($session->ID, true); // true to force delete
    }
}

function insert_movie(/*add variables*/){}

function insert_session(/*add variables*/){}

// Helper function to get attachment ID by image URL
// function get_attachment_id_by_url($image_url) {
//     global $wpdb;
//     $attachment_id = $wpdb->get_var($wpdb->prepare("
//         SELECT post_id FROM $wpdb->postmeta
//         WHERE meta_key = '_source_image_url'
//         AND meta_value = %s
//     ", esc_url_raw($image_url)));
//     return $attachment_id;
// }

// function upload_image_from_url($image_url, $post_id) {

//     // Check if the image has already been uploaded
//     $existing_attachment_id = get_attachment_id_by_url($image_url);
//     if ($existing_attachment_id) {
//         // If the image is already uploaded, return the existing attachment ID
//         return $existing_attachment_id;
//     }

//     // Get the file name of the image
//     $file_name = basename($image_url);
    
//     // Download the image from the URL
//     $response = wp_remote_get($image_url);
    
//     if (is_wp_error($response)) {
//         return false; // Handle error if the image couldn't be downloaded
//     }
    
//     // Get the image contents and save it to the uploads directory
//     $image_data = wp_remote_retrieve_body($response);
//     $upload_dir = wp_upload_dir();
    
//     // Check if the uploads directory is writable
//     if (wp_mkdir_p($upload_dir['path'])) {
//         $file_path = $upload_dir['path'] . '/' . $file_name;
//     } else {
//         $file_path = $upload_dir['basedir'] . '/' . $file_name;
//     }
    
//     // Save the image data to the file path
//     file_put_contents($file_path, $image_data);
    
//     // Prepare an array of file information to simulate a file upload
//     $file = array(
//         'name'     => $file_name,
//         'type'     => wp_remote_retrieve_header($response, 'content-type'),
//         'tmp_name' => $file_path,
//         'error'    => 0,
//         'size'     => filesize($file_path),
//     );
    
//     // Include the necessary WordPress file to handle uploads
//     require_once(ABSPATH . 'wp-admin/includes/file.php');
//     require_once(ABSPATH . 'wp-admin/includes/media.php');
//     require_once(ABSPATH . 'wp-admin/includes/image.php');

//     // Upload the image to the media library
//     $attachment_id = media_handle_sideload($file, $post_id);

//     // If there was an error uploading the image, handle it
//     if (is_wp_error($attachment_id)) {
//         return false;
//     }

//     // Set the uploaded image as the post's featured image
//     set_post_thumbnail($post_id, $attachment_id);

//     return $attachment_id; // Return the attachment ID of the image
// }

function upload_image_from_url($image_url, $post_id) {
    // Check if the image has already been uploaded
    $existing_attachment_id = get_attachment_id_by_filename(basename($image_url));
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

    // Store the file name as metadata for the attachment
    update_post_meta($attachment_id, '_source_image_filename', sanitize_file_name($file_name));

    // Set the uploaded image as the post's featured image
    set_post_thumbnail($post_id, $attachment_id);

    return $attachment_id; // Return the attachment ID of the image
}

function get_attachment_id_by_filename($file_name) {
    global $wpdb;
    $attachment_id = $wpdb->get_var($wpdb->prepare("
        SELECT post_id FROM $wpdb->postmeta
        WHERE meta_key = '_source_image_filename'
        AND meta_value = %s
    ", sanitize_file_name($file_name)));
    return $attachment_id;
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

function add_accessibility_to_movie($movie_id, $new_accessibility_terms) {
    // Get current accessibility terms for the movie
    $current_terms = wp_get_post_terms($movie_id, 'accessibility', array('fields' => 'names'));

    // Merge new terms with existing terms, ensuring no duplicates
    $all_terms = array_unique(array_merge($current_terms, $new_accessibility_terms));

    // Attach unique accessibility terms to the parent movie
    if (!empty($all_terms)) {
        wp_set_object_terms($movie_id, $all_terms, 'accessibility', false);
        update_post_meta( $movie_id, 'supported_accessibility', $all_terms );
    }
}

// module runner entry point for accessibility taxonomy DONT WORK
// function attach_accessibility_to_all_movies() {
//     // Get all movies
//     $args = array(
//         'post_type'   => 'movie',
//         'post_status' => 'publish',
//         'numberposts' => -1,
//     );
//     $movies = get_posts($args);

//     // Loop through each movie and call attach_accessibility_to_movie
//     foreach ($movies as $movie) {
//         attach_accessibility_to_movie($movie->ID);
//     }
// }

// function attach_accessibility_to_movie($movie_id) {
//     // Get all sessions that are children of the movie
//     $args = array(
//         'post_type'   => 'session',
//         'post_status' => 'publish',
//         'numberposts' => -1,
//         'post_parent' => $movie_id,
//     );
//     $sessions = get_posts($args);

//     // Initialize an array to store unique accessibility terms
//     $all_accessibility_terms = array();

//     // Loop through each session and get its accessibility terms
//     foreach ($sessions as $session) {
//         $session_accessibility_terms = wp_get_post_terms($session->ID, 'accessibility', array('fields' => 'ids'));
//         if (!is_wp_error($session_accessibility_terms) && !empty($session_accessibility_terms)) {
//             $all_accessibility_terms = array_merge($all_accessibility_terms, $session_accessibility_terms);
//         }
//     }

//     // Remove duplicate terms
//     $all_accessibility_terms = array_unique($all_accessibility_terms);

//     // Attach unique accessibility terms to the parent movie
//     if (!empty($all_accessibility_terms)) {
//         wp_set_object_terms($movie_id, $all_accessibility_terms, 'accessibility', false);
//     }
// }



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
    $html .= '<p><a style"colour:green;" href="' . $link . '" target="_blank">More Info</a></p>';
    $html .= '</div>';

    return $html;
}

?>