<?php
// ********************************************************************************
//                                   Admin Panel
// ********************************************************************************
function movie_importer_admin_page() {
    global $wpdb;
    ?>
    <style>
        /* General Styles */
        .wrap {
            max-width: 100%;
            padding: 40px;
            background-color: #f8f8f8;
            font-family: Arial, sans-serif;
        }
        h1 {
            font-size: 28px;
            color: #333;
            text-align: center;
            border-bottom: 3px solid #0073aa;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        h2, h3 {
            font-size: 20px;
            color: #333;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        .section-header {
            font-size: 18px;
            color: #0073aa;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .info-text {
            color: #555;
            font-size: 15px;
            margin-bottom: 20px;
        }

        /* Panel Box */
        .panel-box {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            width: 100%;
        }

        /* Button Styles */
        .button-primary, .button-warning, .delete-button {
            font-size: 15px;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-align: center;
            display: block;
            max-width: 250px;
            margin-bottom: 15px;
        }
        .button-primary {
           background-color: #0073aa;
            border-color: #0073aa;
            color: #fff;
            text-align: center;
            margin-left: 0; /* Align left */
            width: 100%;
            max-width: 250px;
        }
        .button-primary:hover {
            background-color: #005f8a;
        }
        .button-warning {
            background-color: #0073aa;
            border-color: #0073aa;
            color: #fff;
            text-align: center;
            margin-left: 0; /* Align left */
            width: 100%;
            max-width: 250px;
        }
        .delete-button {
            background-color: #d9534f;
            border-color: #d9534f;
            color: #fff;
        }
        .delete-button:hover {
            background-color: #c9302c;
        }
        .disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        /* Center alignment for "Run All Now" */
        .center-align {
            text-align: center;
        }

        /* Toggle Switch */
        #toggle-deleters {
            margin-left: 10px;
            vertical-align: middle;
        }

        /* Highlighted Notes */
        .note {
            color: #df0000;
            font-weight: bold;
        }
        .note-text {
            color: #555;
            font-size: 14px;
            margin-top: 10px;
            margin-bottom: 40px; 
            
        }

        /* Data Display Styles */
        .data-info {
            background-color: #f1f1f1;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            font-size: 15px;
        }

        /* Instruction Box */
        .instructions-box {
            padding: 20px;
            background-color: #e9f7ff;
            border: 1px solid #bcdff1;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
    
    <div class="wrap">
        <h1>Movie & Session Fetcher Admin Panel</h1>

        <div class="instructions-box">
            <h2>Instructions</h2>
            <p>Use this panel to manage the movie and session data imported from external cinema APIs. You can perform manual fetch operations for movies and sessions, schedule regular updates, and clean up outdated sessions. Please proceed with caution when using the deletion options as they are permanent.</p>
        </div>

        <div class="panel-box">
            <div class="data-info">
                <?php
                    // Display the next scheduled run time
                    $next_run = wp_next_scheduled('movie_importer_cron_job');
                    echo '<p><strong>Next Scheduled Run:</strong> ' . ($next_run ? date('d/m/Y H:i', $next_run) : 'Not scheduled') . '</p>';

                    // Display the last run time
                    $last_run = get_option('movie_importer_last_run');
                    echo '<p><strong>Last Run:</strong> ' . ($last_run ? date('d/m/Y H:i', $last_run) : 'Never') . '</p>';
                ?>
            </div>
        </div>

        <h3>Manual Fetch Options</h3>
        <div class="panel-box">
        

            <form method="post" action="">
                <input type="submit" name="import_movies" class="button-warning" value="Fetch Movies Now">
                <p class="note-text"><span class="note">Note:</span> This can take a while when run for the first time.</p>
            </form>

            <form method="post" action="">   
                <input type="submit" name="import_sessions" class="button-warning" value="Fetch Sessions Now">
                <p class="note-text"><span class="note">Note:</span> Please import movies before importing sessions.</p>
            </form>

            <form method="post" action="">
                <input type="submit" name="run_cleanup" class="button-warning" value="Clean Up Old Sessions">
                <p class="note-text">This will remove outdated sessions from the database.</p>
            </form>
            
             <div class="center-align">
                <form method="post" action="">
                    <input type="submit" name="run_all" class="button-primary" value="Run All Now">
                    <p class="note-text"><span class="note">Note:</span> This may take some time to complete.</p>
                </form>
            </div>
        </div>

        <h3>Data Deletion Options</h3>
        <div class="panel-box">
            <label for="toggle-deleters" class="section-header">Enable Deletion Options</label>
            <input type="checkbox" id="toggle-deleters">
            <span class="info-text" style="color: darkorange;">Use with caution. Deletion is permanent and cannot be reverted.</span><br><br>

            <div id="deleters-forms" class="disabled">
                <form method="post" action="" onsubmit="return confirmAction('delete all movie posters');">
                    <input type="submit" name="run_poster_cleanup" class="delete-button" value="Delete All Movie Posters" disabled>
                    <p class="note-text"><span class="note">Note:</span> This deletes all movie posters stored locally.</p>
                </form>

                <form method="post" action="" onsubmit="return confirmAction('delete all movies and sessions');">
                    <input type="submit" name="nuke_all_movies_sessions" class="delete-button" value="Delete All Movies & Sessions" disabled>
                    <p class="note-text"><span class="note">Note:</span> This deletes EVERYTHING (except posters). Run poster cleanup first.</p>
                </form>

                <form method="post" action="" onsubmit="return confirmAction('delete everything including posters');">
                    <input type="submit" name="nuke_all" class="delete-button" value="Delete All Data" disabled>
                    <p class="note-text"><span class="note">Note:</span> THIS DELETES EVERYTHING, including posters.</p>
                </form>
            </div>
        </div>

        <script>
            document.getElementById('toggle-deleters').addEventListener('change', function() {
                var deletersForms = document.getElementById('deleters-forms');
                deletersForms.classList.toggle('disabled', !this.checked);
                deletersForms.querySelectorAll('.delete-button').forEach(button => button.disabled = !this.checked);
            });

            function confirmAction(action) {
                return confirm(`Are you sure you want to ${action}? This action cannot be undone.`);
            }
        </script>
    </div>

    <?php
    // Processing form actions
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
        echo '<div class="notice notice-success is-dismissible"><p>Sessions Cleaned Up Successfully!</p></div>';
    }
    if ( isset( $_POST['run_poster_cleanup'] ) ) {
        delete_all_movie_posters($wpdb);
        echo '<div class="notice notice-success is-dismissible"><p>All Movie Posters Deleted Successfully!</p></div>';
    }
    if ( isset( $_POST['nuke_all_movies_sessions'] ) ) {
        delete_all_movies_and_sessions();
        echo '<div class="notice notice-success is-dismissible"><p>All Movies & Sessions Deleted Successfully (Posters not included).</p></div>';
    }
    if ( isset( $_POST['nuke_all'] ) ) {
        delete_posters_movies_sessions();
        echo '<div class="notice notice-success is-dismissible"><p>All Data Deleted Successfully.</p></div>';
    }
}
?>