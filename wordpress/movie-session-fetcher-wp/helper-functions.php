<?php
// HELPER FUNCTIONS INCLUDING:

// 1. Function to delete old sessions
function delete_old_sessions($wpdb) {
    set_time_limit(300);
    $wpdb->query("DELETE FROM wp_movie_sessions WHERE session_date < DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
}

function insert_movie(/*add variables*/){}

function insert_session(/*add variables*/){}


?>