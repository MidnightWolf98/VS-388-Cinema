<?php
// HELPER FUNCTIONS INCLUDING:

// 1. Function to delete old sessions
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

?>