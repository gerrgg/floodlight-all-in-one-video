<?php
/*
Plugin Name: Floodlight All-In-One Video
Description: A all-in-one solution to manage and display videos on your WordPress site.
Version: 1.0
Author: Gregory Bastianelli
Author URI: https://floodlight.design/
License: GPLv2 or later
Text Domain: fld-aio-video
*/

// Define plugin version
define('FLOODLIGHT_VIDEO_VERSION', '1.0');

// Define plugin path
define('FLOODLIGHT_VIDEO_PATH', plugin_dir_path(__FILE__));

// Include necessary files
require_once FLOODLIGHT_VIDEO_PATH . 'post_types/aio-video.php';
require_once FLOODLIGHT_VIDEO_PATH . 'youtube.php';
require_once FLOODLIGHT_VIDEO_PATH . 'vimeo.php';
require_once FLOODLIGHT_VIDEO_PATH . 'mp4.php';
require_once FLOODLIGHT_VIDEO_PATH . 'shortcode.php';
require_once FLOODLIGHT_VIDEO_PATH . 'cloudflare-upload.php';
require_once FLOODLIGHT_VIDEO_PATH . 'cloudflare.php';

// Hook into WordPress init action to register custom post type
add_action('init', 'create_aio_video_post_type');

// Hook into admin init action to remove unwanted supports
add_action('init', 'remove_aio_video_support');

// Hook into admin menu action to remove meta boxes
add_action('admin_menu', 'remove_aio_video_meta_boxes');

// create metafields for aio_video
add_action('acf/init', 'create_acf_field_group_for_aio_video');

// remove unnessicary metaboxes
add_action('admin_menu','remove_my_post_metaboxes');

// Hook into the 'wp_enqueue_scripts' action
add_action('wp_enqueue_scripts', 'aio_video_enqueue_scripts');
add_action('wp_enqueue_scripts', 'my_plugin_enqueue_styles');

function aio_video_enqueue_scripts() {
  // Register the script
  wp_register_script('aio-youtube', plugin_dir_url(__FILE__) . 'js/youtube.js', array('jquery'), '1.0.0', true);
  wp_register_script('aio-vimeo', plugin_dir_url(__FILE__) . 'js/vimeo.js', array('jquery'), '1.0.0', true);
  wp_register_script('aio-mp4', plugin_dir_url(__FILE__) . 'js/mp4.js', array('jquery'), '1.0.0', true);
  wp_register_script('aio-cloudflare', plugin_dir_url(__FILE__) . 'js/cloudflare.js', array('jquery'), '1.0.0', true);
  wp_register_script('aio-vimeo-sdk', 'https://player.vimeo.com/api/player.js', array('jquery'), '1.0.0', true);
}

function _render_fld_video($video){
  if( $video ){
    $type = get_field('field_button_group', $video);
    $function_name = sprintf('_render_fld_video_%s', $type);
  
    if( function_exists($function_name) ){
      call_user_func($function_name, $video);
    } else {
      echo $function_name . 'doesn\'t exist';
    }
  }
}

function my_plugin_enqueue_styles() {
  wp_enqueue_style(
    'my-plugin-style', // Handle for the stylesheet
    plugin_dir_url(__FILE__) . 'style.css', // Path to the stylesheet
    array(), // Dependencies (optional)
    time(), // Version number (optional)
    'all' // Media type (optional, defaults to 'all')
  );
}

// Hook into the admin menu to add a settings page
add_action('admin_menu', 'aio_video_settings_page');

function aio_video_settings_page() {
  add_submenu_page(
    'edit.php?post_type=aio_video',      // Parent slug
    'API Settings',                      // Page title
    'API Settings',                      // Menu title
    'manage_options',                    // Capability required
    'aio_video_settings',                // Menu slug
    'aio_video_settings_page_html'       // Callback function to display the page content
  );
}

function aio_video_settings_page_html() {
  // Check if the user is allowed to access this page
  if (!current_user_can('manage_options')) {
    return;
  }

  // Save settings if form is submitted
  if (isset($_POST['aio_video_save_settings'])) {
    update_option('cloudflare_api_token', sanitize_text_field($_POST['cloudflare_api_token']));
    update_option('cloudflare_account_id', sanitize_text_field($_POST['cloudflare_account_id']));
    echo '<div class="updated"><p>Settings saved.</p></div>';
  }

  // Retrieve current settings
  $api_token = get_option('cloudflare_api_token', '');
  $account_id = get_option('cloudflare_account_id', '');

  // HTML for the settings form
  ?>
  <div class="wrap">
    <h1><?php esc_html_e('Floodlight Cloudflare API Settings', 'textdomain'); ?></h1>
    <form method="post" action="">
      <table class="form-table">
        <tr valign="top">
          <th scope="row"><label for="cloudflare_api_token"><?php esc_html_e('Cloudflare API Token', 'textdomain'); ?></label></th>
          <td><input type="text" id="cloudflare_api_token" name="cloudflare_api_token" value="<?php echo esc_attr($api_token); ?>" class="regular-text" /></td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="cloudflare_account_id"><?php esc_html_e('Cloudflare Account ID', 'textdomain'); ?></label></th>
          <td><input type="text" id="cloudflare_account_id" name="cloudflare_account_id" value="<?php echo esc_attr($account_id); ?>" class="regular-text" /></td>
        </tr>
      </table>
      <?php submit_button('Save Settings', 'primary', 'aio_video_save_settings'); ?>
    </form>
  </div>
  <?php
}
