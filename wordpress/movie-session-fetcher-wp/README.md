# Wordpress - Movie & Session Fetch Plugin
By RMIT Team: **VS-388-Cinema**
* Evan Kim 
* Hieu Tran
* Mihir Anand
* Yifan Shen
* Sahil Narayan

## What Does This Plugin Do?
A plugin that fetches movies and their sessions from external cinema APIs and inserts them as custom posts in WordPress. It keeps these movies up to date with ratings and now showing or not. The plugin also provides various administrative tools for managing movie and session data, including cleanup functions and manual import options.

## How to Install The Plugin
1. Zip this folder
2. Go to wordpress admin panel, hover mouse over "Plugins" on the left hot-bar, and click "Add New Plugin"
3. Click "Upload Plugin" at the very top of the screen next to the title.
4. Upload the zip of this folder
5. Activate plugin

The plugin is now installed!

## Features of Current Version
1. Fetch Movies and Sessions:
    - The plugin fetches movie and session data from external cinema APIs (e.g., Hoyts and Village) and inserts them as custom posts in WordPress.

2. Custom Post Types:
    - Movies and sessions are stored as custom post types (movie and session).

3. Taxonomies:
    - The plugin uses custom taxonomies such as Dates and accessibility to categorize and manage movie and session data.

4. Admin Interface:
    - The plugin provides an admin interface with options to manually fetch movies and sessions, run cleanup tasks, and delete movie and session data.

5. Scheduled Fetching:
    - The plugin schedules daily imports of movie and session data using WordPress cron jobs.

6. Cleanup Functions:
    - The plugin includes functions to delete old sessions based on the Dates taxonomy and to delete all movie posters.

7. Error Handling and Notifications:
    - The plugin handles errors during the import and cleanup processes and displays error messages in the WordPress admin interface.

## Admin Interface Guide
All code related to the admin interface is in the file admin-panel.php located in the required functions folder. The admin interface provides the following options:

1. **Run All Modules:**
    - Manually fetch movies and sessions from external APIs and run cleanup tasks.

2. **Manually Fetch Movies:**
    - Manually fetch movies from external APIs.

3. **Manually Fetch Sessions:**
    - Manually fetch sessions from external APIs.
4. **Manually Run Session Cleanup**
    - Deletes sessions older than one day.

<br>

**All below functions are blocked behind a toggle to ensure safe use**. Additionally, 5 uses 1 confirmation prompt and 6 + 7 use double confirmation prompts.

5. **Delete All Movie Posters:**
    - Delete all movie posters stored locally.

6. **Delete All Movies and Sessions:**
    - Delete all movie and session posts with confirmation prompts.

7. **Delete All Movies, Posters, Sessions (Full Nuke)**
    - Number 4 & 5 Combined.

<br>

![admin terminal screenshot](readme-images/admin-panel.png)

## How to Setup Development Environment
This will guide you through how to setup a local wordpress environment for non-production testing. Alternatively, follow [this youtube tutorial](https://www.youtube.com/watch?v=XkKadPcPFT4&t=328s).

1. To setup the wordpress environment, first install [**XAMPP**](https://www.apachefriends.org/) 

2. Once XAMPP is installed, copy the "wordpress" folder into the **htdocs** folder in the instalation directory of XAMPP

3. Open XAMPP and start MySQL server

4. Click **admin** in XAMPP to open PHPmyAdmin GUI

5. Create a new DB called "**wordpress**"
![phpmyadmin screenshot](readme-images/image.png)

6. Now start the apache server in XAMPP.

7. In your browser, go to http://localhost/wordpress/

## Critical Code Map (As of Version 0.07.11 Beta)
- cinema-modules
    - **hoyts-module.php**
        - *$venues* *global object array, containing hoyts VIC venues*
        - *hoyts_fetch_all_movies_and_sessions()* *main entry*
            - calls hoyts_fetch_and_insert_movies() & hoyts_fetch_and_insert_sessions_all_venues()
        - *hoyts_fetch_and_insert_movies()*
        - *hoyts_fetch_and_insert_sessions_all_venues()*
            - runs hoyts_fetch_and_insert_sessions($venue_code, $state, $suburb), while looping thorugh $venues
        - *hoyts_fetch_and_insert_sessions($venue_code, $state, $suburb)*
    - **village-module.php**
        - WORK IN PROGRESS, NOT RAN OR IMPLEMENTED YET.

<br>

- register-types
    - These run on pluugin activation, both initialise custom post type and taxonomies

<br>

- required-functions
    - **admin-panel.php**
        - MOSTLY HTML, PHP, calls other functions, no need for map.
    - **cleanup-functions.php**
        - *delete_old_sessions()*
            - Deletes sessions that are more than 1 day old, to ensure all info is current.
        - *delete_posters_movies_sessions()*
            - runs delete all posters, then delete all movie & sessions.
        - *delete_all_movie_posters()*
            - deletes all posters linked to all current movie posts.
        - *delete_all_movies_and_sessions()*
            - deletes all movie and session posts.
    - **helper-functions.php**
        - *insert_movie()*
            - supposed to be a universal function for modules, not yet implemented
        - *insert_sessions()*
            - same as above, but for sessions
        - *upload_image_from_url($image_url, $post_id)*
            - Downloads an image from a URL and uploads it to the WordPress media library. It also sets the uploaded image as the featured image for the specified post.
        - *get_attachment_id_by_filename($file_name)*
            - Retrieves the attachment ID of an image by its filename.
        - *add_accessibility_to_movie($movie_id, $new_accessibility_terms)*
            - Adds accessibility terms (taxonomy and metadata) to a movie post.
        - *generate_movie_html($movie_title, $summary, $release_date, $runtime, $genres, $rating, $link)*
            - Generates HTML content for a movie post.
        - *generate_session_html($movie_title, $acc_tags, $cinema, $state, $suburb, $s_date, $s_time, $link)*
            - Generates HTML content for a session post.
