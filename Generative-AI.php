<?php
/**
 * Plugin Name: Generative AI
 * Plugin URI: http://wordpress.org/plugins/generative-ai/
 * Description: This plugin adds a custom admin menu with an AI icon and displays generative content.
 * Author: Advait
 * Version: 1.0.0
 * Author URI: http://advait.com/
 */

// Hook for adding admin menus
add_action('admin_menu', 'generative_ai_menu');

function generative_ai_menu() {
    add_menu_page('Generative AI Plugin Settings', 'Generative AI', 'manage_options', 'generative_ai_settings', 'generative_ai_page', 'dashicons-smiley');
}

function generative_ai_page() {
    ?>
    <div class="wrap">
        <h2>Generative AI Dashboard</h2>
        <p>Welcome to the Generative AI plugin dashboard. Here you can publish new content automatically.</p>
        <form method="post" action="">
            <?php wp_nonce_field('spc_create_post_action', 'spc_create_post_nonce'); ?>
            <?php submit_button('1 posts publish now', 'primary', 'publish_content'); ?>
        </form>
        <div class="generative-ai-footer">By Advait Labs</div>
    </div>
    <?php
}
add_action('admin_init', 'spc_handle_post_request');

function spc_handle_post_request() {
    if (isset($_POST['publish_content']) && check_admin_referer('spc_create_post_action', 'spc_create_post_nonce')) {
        spc_create_posts(1); // Pass the number of posts to create
    }
}
function fetch_api_content() {
    $api_url = 'https://api.example.com/generate-content?api_key=YOUR_API_KEY';
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        error_log('API Call Failed: ' . $response->get_error_message());
        return 'Default content because API failed';
    }

    $body = wp_remote_retrieve_body($response);
    if (empty($body)) {
        error_log('API Response was empty');
        return 'Default content because API failed';
    }
    return $body;
}


function spc_create_posts($number_of_posts) {
    $success_count = 0;
    $failed_count = 0;

    $category_ids = array(1); // Array of category IDs
    $tag_names = array('Blog', 'Article'); // Array of tag names

    for ($i = 0; $i < $number_of_posts; $i++) {
        $api_content = fetch_api_content(); // Fetch content from the API

        $post_data = array(
            'post_title'    => 'Generated Content ' . current_time('m/d/Y H:i:s'),
            'post_content'  => $api_content, // Use API content
            'post_status'   => 'publish',
            'post_author'   => get_current_user_id(),
            'post_category' => $category_ids,
            'tags_input'    => $tag_names,
        );

        $post_id = wp_insert_post($post_data);
        if ($post_id) {
            $success_count++;
        } else {
            $failed_count++;
        }
    }

    add_action('admin_notices', function() use ($success_count, $failed_count) {
        if ($success_count > 0) {
            echo '<div class="updated"><p>Successfully published ' . $success_count . ' posts.</p></div>';
        }
        if ($failed_count > 0) {
            echo '<div class="error"><p>Failed to publish ' . $failed_count . ' posts.</p></div>';
        }
    });
}


function generative_ai_admin_styles() {
    ?>
    <style>
        /* Style adjustments for admin menu icon and footer text */
        #adminmenu .menu-icon-generative_ai div.wp-menu-image:before {
            content: "\f524"; /* Dashicon code */
        }
        
        .generative-ai-footer {
            position: absolute;
            right: 20px;
            bottom: 10px;
            font-size: small;
            color: #555;
        }
    </style>
    <?php
}
add_action('admin_head', 'generative_ai_admin_styles');
