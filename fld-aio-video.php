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
  wp_register_script('aio-youtube', plugin_dir_url(__FILE__) . '/js/youtube.js', array('jquery'), '1.0.0', true);
  wp_register_script('aio-vimeo', plugin_dir_url(__FILE__) . '/js/vimeo.js', array('jquery'), '1.0.0', true);
  wp_register_script('aio-mp4', plugin_dir_url(__FILE__) . '/js/mp4.js', array('jquery'), '1.0.0', true);
  wp_register_script('aio-cloudflare', plugin_dir_url(__FILE__) . '/js/cloudflare.js', array('jquery'), '1.0.0', true);
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


