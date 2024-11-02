<?php
// ********************************************************************************
//                                   Admin Panel
// ********************************************************************************
function movie_importer_admin_page() {
    global $wpdb;
    ?>
    <style>
        .wrap {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        form {
            margin-bottom: 20px;
        }
        hr {
            margin-bottom: 20px;
        }
        .delete-button {
        background-color: red;
        border-color: red;
        }
        .delete-button:hover {
            background-color: darkred;
            border-color: darkred;
        }
    </style>
    <div class="wrap">
        <h1>Movie & Session Fetcher</h1>

        <hr>
        
        <?php
            // Display the next scheduled run time
            $next_run = wp_next_scheduled('movie_importer_cron_job');
            if ($next_run) {
                echo '<p>Next scheduled run: <strong>' . date('Y-m-d H:i:s', $next_run) . '</p>';
            } else {
                echo '<p>Next scheduled run: <strong>Not scheduled</strong></p>';
            }
        ?>

        <?php
            // Display the last run time DEOSNT WORK RN FIX LATER! 
            $last_run = get_option('movie_importer_last_run');
            if ($last_run) {
                echo 'p>Last run: <strong>' . date('Y-m-d H:i:s', $last_run) . '</p>';
            } else {
                echo '<p>Last run: <strong>Never</strong></p>';
            }
        ?>

        <hr>

        <h3>Click the buttons below to manually fetch movies and sessions from the external cinema APIs.</h3>
        <form method="post" action="">
        <label><span style="color: orange;">Note:</span> This can take a while.</label><br>
            <input type="submit" name="run_all" class="button button-primary" value="Run All Now">
        </form>
        <line>

        <form method="post" action="">
        <label><span style="color: orange;">Note:</span> This can take a while when ran for the first time.</label><br>
            <input type="submit" name="import_movies" class="button button-primary" value="Manually Fetch Movies Now">
        </form>

        <form method="post" action="">
        <label><span style="color: red;">Note:</span> Please import movies before importing sessions.</label><br>
            <input type="submit" name="import_sessions" class="button button-primary" value="Manually Fetch Sessions Now">
        </form>

        <form method="post" action="">
            <input type="submit" name="run_cleanup" class="button button-primary" value="Manually Cleanup Old Sessions Now">
        </form>

        <hr>

        <h3><strong>NUKES - FOR DEBUGGING!</strong></h3>
        <p><strong><span style="color:red;">USE WITH CAUTION!</span></strong></p>
        <!-- <form method="post" action="">
            <input type="submit" name="nuke_all_movies_sessions" class="button button-primary" value="DELETE ALL MOVIES AND SESSIONS">
            <label><span style="color: orange;">Note:<strong>THIS DELETES EVERYTING. </span>(except Posters, run poster cleanup first.)</strong></label><br>
        </form> -->

        <form method="post" action="">
            <input type="submit" name="run_poster_cleanup" class="button button-primary" value="DELETE ALL MOVIE POSTERS">
            <label><span style="color: RED;">Note:</span> This is for reseting the plugin state. RUN <strong>BEFORE DELETING ALL MOVIE POSTS.</strong></label><br>
        </form>

        <form method="post" action="" onsubmit="return confirmDeletion();">
            <input type="submit" name="nuke_all_movies_sessions" class="button delete-button" value="DELETE ALL MOVIES AND SESSIONS">
            <label><span style="color: red;">Note:<strong> THIS DELETES EVERYTHING. </span>(except Posters, RUN POSTER CLEANUP FIRST.)</strong></label><br>
        </form>

        <script>
            function confirmDeletion() {
                if (!confirm("Have you run the poster cleanup first?")) {
                    return false;
                }
                return confirm("Are you sure you want to delete all movies and sessions? This action CAN NOT be undone.");
            }
        </script>


    </div>
    
    <?php
    // if ( isset( $_POST['import_movies'] ) ) {
    //     hoyts_fetch_all_movies_and_sessions();
    //     echo '<div class="notice notice-success is-dismissible"><p>Movies & sessions imported successfully!</p></div>';
    // }
    if ( isset( $_POST['run_all'] ) ) {
        run_all_modules();
        echo '<div class="notice notice-success is-dismissible"><p>Fetch and Cleanup Completed Successfully!</p></div>';
    }
    if ( isset( $_POST['import_movies'] ) ) {
            hoyts_fetch_and_insert_movies();
            echo '<div class="notice notice-success is-dismissible"><p>Movies Imported Successfully!</p></div>';
        }
    if ( isset( $_POST['import_sessions'] ) ) {
        hoyts_fetch_and_insert_sessions_all_venues();
        echo '<div class="notice notice-success is-dismissible"><p>Sessions Imported Successfully!</p></div>';
    }
    if ( isset( $_POST['run_cleanup'] ) ) {
        delete_old_sessions($wpdb);
        echo '<div class="notice notice-success is-dismissible"><p>Cleaned Up Sessions Successfully!</p></div>';
    }
    if ( isset( $_POST['run_poster_cleanup'] ) ) {
        delete_all_movie_posters($wpdb);
        echo '<div class="notice notice-success is-dismissible"><p>Cleaned Up Posters Successfully!</p></div>';
    }
    if ( isset( $_POST['nuke_all_movies_sessions'] ) ) {
        delete_all_movies_and_sessions();
        echo '<div class="notice notice-success is-dismissible"><p>Deleted All Successfully.</p></div>';
    }
}
?>