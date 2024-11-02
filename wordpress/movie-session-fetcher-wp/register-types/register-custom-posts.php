<?php
// ********************************************************************************
//                   Register custom post types Movies, Sessions
// ********************************************************************************

//Register Movie Post Type
function register_movie_post_type() {
    $labels = array(
        'name'               => _x( 'Movies', 'post type general name' ),
        'singular_name'      => _x( 'Movie', 'post type singular name' ),
        'menu_name'          => _x( 'Movies', 'admin menu' ),
        'name_admin_bar'     => _x( 'Movie', 'add new on admin bar' ),
        'add_new'            => _x( 'Add New', 'movie' ),
        'add_new_item'       => __( 'Add New Movie' ),
        'new_item'           => __( 'New Movie' ),
        'edit_item'          => __( 'Edit Movie' ),
        'view_item'          => __( 'View Movie' ),
        'all_items'          => __( 'All Movies' ),
        'search_items'       => __( 'Search Movies' ),
        'not_found'          => __( 'No movies found.' ),
        'not_found_in_trash' => __( 'No movies found in Trash.' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'movie' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-video-alt2', // Film camera icon
        'supports'           => array( 'title', 'editor', 'custom-fields' ),
    );

    register_post_type( 'movie', $args );
}
add_action( 'init', 'register_movie_post_type' );

// Register Session Post Type
function register_session_post_type() {
    $labels = array(
        'name'               => _x( 'Sessions', 'post type general name' ),
        'singular_name'      => _x( 'Session', 'post type singular name' ),
        'menu_name'          => _x( 'Sessions', 'admin menu' ),
        'name_admin_bar'     => _x( 'Session', 'add new on admin bar' ),
        'add_new'            => _x( 'Add New', 'session' ),
        'add_new_item'       => __( 'Add New Session' ),
        'new_item'           => __( 'New Session' ),
        'edit_item'          => __( 'Edit Session' ),
        'view_item'          => __( 'View Session' ),
        'all_items'          => __( 'All Sessions' ),
        'search_items'       => __( 'Search Sessions' ),
        'not_found'          => __( 'No sessions found.' ),
        'not_found_in_trash' => __( 'No sessions found in Trash.' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'session' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => true, // Set this to true to allAow parent-child relationship
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-format-video', // Film snip with play button icon
        'supports'           => array( 'title', 'editor', 'page-attributes', 'custom-fields' ),
    );

    register_post_type( 'session', $args );
}
add_action( 'init', 'register_session_post_type' );
?>