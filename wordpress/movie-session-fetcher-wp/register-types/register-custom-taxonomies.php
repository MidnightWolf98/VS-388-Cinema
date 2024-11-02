<?php

// ********************************************************************************
//         Register the custom taxonomies for the 'session' post type
// ********************************************************************************
function register_movie_session_taxonomies() {

    // Register the Accessibility taxonomy
    $labels_accessibility = array(
        'name'              => _x( 'Accessibility', 'taxonomy general name' ),
        'singular_name'     => _x( 'Accessibility', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Accessibility Options' ),
        'all_items'         => __( 'All Accessibility Options' ),
        'parent_item'       => __( 'Parent Accessibility' ),
        'parent_item_colon' => __( 'Parent Accessibility:' ),
        'edit_item'         => __( 'Edit Accessibility' ),
        'update_item'       => __( 'Update Accessibility' ),
        'add_new_item'      => __( 'Add New Accessibility' ),
        'new_item_name'     => __( 'New Accessibility Option Name' ),
        'menu_name'         => __( 'Accessibility' ),
    );

    $args_accessibility = array(
        'hierarchical'      => true, // Set to true for parent/child relationships
        'labels'            => $labels_accessibility,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'accessibility' ),
        'show_in_rest'      => true,
    );

    register_taxonomy( 'accessibility', array( 'session', 'movie' ), $args_accessibility );

    // Register the State taxonomy
    $labels_state = array(
        'name'              => _x( 'State', 'taxonomy general name' ),
        'singular_name'     => _x( 'State', 'taxonomy singular name' ),
        'search_items'      => __( 'Search States' ),
        'all_items'         => __( 'All States' ),
        'parent_item'       => __( 'Parent State' ),
        'parent_item_colon' => __( 'Parent State:' ),
        'edit_item'         => __( 'Edit State' ),
        'update_item'       => __( 'Update State' ),
        'add_new_item'      => __( 'Add New State' ),
        'new_item_name'     => __( 'New State Name' ),
        'menu_name'         => __( 'State' ),
    );

    $args_state = array(
        'hierarchical'      => true,
        'labels'            => $labels_state,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'state' ),
        'show_in_rest'      => true,
    );

    register_taxonomy( 'state', array( 'session' ), $args_state );

    // Register the Suburb taxonomy
    $labels_suburb = array(
        'name'              => _x( 'Suburb', 'taxonomy general name' ),
        'singular_name'     => _x( 'Suburb', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Suburbs' ),
        'all_items'         => __( 'All Suburbs' ),
        'parent_item'       => __( 'Parent Suburb' ),
        'parent_item_colon' => __( 'Parent Suburb:' ),
        'edit_item'         => __( 'Edit Suburb' ),
        'update_item'       => __( 'Update Suburb' ),
        'add_new_item'      => __( 'Add New Suburb' ),
        'new_item_name'     => __( 'New Suburb Name' ),
        'menu_name'         => __( 'Suburb' ),
    );

    $args_suburb = array(
        'hierarchical'      => false, // Set to false if there’s no hierarchy in suburbs
        'labels'            => $labels_suburb,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'suburb' ),
        'show_in_rest'      => true,
    );

    register_taxonomy( 'suburb', array( 'session' ), $args_suburb );

    $labels_cinema = array(
        'name'              => _x( 'Cinemas', 'taxonomy general name' ),
        'singular_name'     => _x( 'Cinema', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Cinemas' ),
        'all_items'         => __( 'All Cinemas' ),
        'parent_item'       => __( 'Parent Cinema' ),
        'parent_item_colon' => __( 'Parent Cinema:' ),
        'edit_item'         => __( 'Edit Cinema' ),
        'update_item'       => __( 'Update Cinema' ),
        'add_new_item'      => __( 'Add New Cinema' ),
        'new_item_name'     => __( 'New Cinema Name' ),
        'menu_name'         => __( 'Cinema' ),
    );

    $args_cinema = array(
        'hierarchical'      => true, // Set to true for parent/child relationships (e.g., categories)
        'labels'            => $labels_cinema,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'cinema' ),
        'show_in_rest'      => true,
    );

    register_taxonomy( 'cinema', array( 'session' ), $args_cinema );

    // Date Taxonomy
    $labels_date = array(
        'name'              => _x( 'Dates', 'taxonomy general name' ),
        'singular_name'     => _x( 'Date', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Dates' ),
        'all_items'         => __( 'All Dates' ),
        'parent_item'       => __( 'Parent Date' ),
        'parent_item_colon' => __( 'Parent Date:' ),
        'edit_item'         => __( 'Edit Date' ),
        'update_item'       => __( 'Update Date' ),
        'add_new_item'      => __( 'Add New Date' ),
        'new_item_name'     => __( 'New Date Name' ),
        'menu_name'         => __( 'Date' ),
    );

    $args_date = array(
        'hierarchical'      => false, // Set to false for non-hierarchical taxonomy (e.g., tags)
        'labels'            => $labels_date,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'date' ),
        'show_in_rest'      => true,
    );

    register_taxonomy( 'date', array( 'session' ), $args_date );

    // Time Taxonomy
    $labels_time = array(
        'name'              => _x( 'Times', 'taxonomy general name' ),
        'singular_name'     => _x( 'Time', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Times' ),
        'all_items'         => __( 'All Times' ),
        'parent_item'       => __( 'Parent Time' ),
        'parent_item_colon' => __( 'Parent Time:' ),
        'edit_item'         => __( 'Edit Time' ),
        'update_item'       => __( 'Update Time' ),
        'add_new_item'      => __( 'Add New Time' ),
        'new_item_name'     => __( 'New Time Name' ),
        'menu_name'         => __( 'Time' ),
    );

    $args_time = array(
        'hierarchical'      => false, // Set to false for non-hierarchical taxonomy (e.g., tags)
        'labels'            => $labels_time,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'time' ),
        'show_in_rest'      => true,
    );

    register_taxonomy( 'time', array( 'session' ), $args_time );

    // UTC Date Taxonomy
    $labels_utc_date = array(
        'name'              => _x( 'UTC Dates', 'taxonomy general name' ),
        'singular_name'     => _x( 'UTC Date', 'taxonomy singular name' ),
        'search_items'      => __( 'Search UTC Dates' ),
        'all_items'         => __( 'All UTC Dates' ),
        'parent_item'       => __( 'Parent UTC Date' ),
        'parent_item_colon' => __( 'Parent UTC Date:' ),
        'edit_item'         => __( 'Edit UTC Date' ),
        'update_item'       => __( 'Update UTC Date' ),
        'add_new_item'      => __( 'Add New UTC Date' ),
        'new_item_name'     => __( 'New UTC Date Name' ),
        'menu_name'         => __( 'UTC Date' ),
    );

    $args_utc_date = array(
        'hierarchical'      => false, // Set to false for non-hierarchical taxonomy (e.g., tags)
        'labels'            => $labels_utc_date,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'utc-date' ),
        'show_in_rest'      => true,
    );

    register_taxonomy( 'utc_date', array( 'session' ), $args_utc_date );

    // UTC Time Taxonomy
    $labels_utc_time = array(
        'name'              => _x( 'UTC Times', 'taxonomy general name' ),
        'singular_name'     => _x( 'UTC Time', 'taxonomy singular name' ),
        'search_items'      => __( 'Search UTC Times' ),
        'all_items'         => __( 'All UTC Times' ),
        'parent_item'       => __( 'Parent UTC Time' ),
        'parent_item_colon' => __( 'Parent UTC Time:' ),
        'edit_item'         => __( 'Edit UTC Time' ),
        'update_item'       => __( 'Update UTC Time' ),
        'add_new_item'      => __( 'Add New UTC Time' ),
        'new_item_name'     => __( 'New UTC Time Name' ),
        'menu_name'         => __( 'UTC Time' ),
    );

    $args_utc_time = array(
        'hierarchical'      => false, // Set to false for non-hierarchical taxonomy (e.g., tags)
        'labels'            => $labels_utc_time,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'utc-time' ),
        'show_in_rest'      => true,
        
    );

    register_taxonomy( 'utc_time', array( 'session' ), $args_utc_time );

}
add_action( 'init', 'register_movie_session_taxonomies' );

?>