<?php
add_action('init', 'register_aio_video_shortcode');
add_action('admin_init', 'my_custom_shortcode_button');

function my_custom_shortcode_button() {
    // Check if the current user has the capability to edit posts or pages
    if (current_user_can('edit_posts') || current_user_can('edit_pages')) {
        // Add the button only for users who can edit content
        add_filter('mce_external_plugins', 'add_tinymce_plugin');
        add_filter('mce_buttons', 'register_tinymce_button');
    }
}

// Register the button
function register_tinymce_button($buttons) {
    array_push($buttons, 'my_shortcode_button');
    return $buttons;
}

// Load the TinyMCE plugin
function add_tinymce_plugin($plugin_array) {
  // Register the script
  wp_register_script('my_custom_shortcode', plugin_dir_url(__FILE__) . 'custom-shortcode.js?v='.time(), array('jquery'), null, true);

  // Localize the script with data
  wp_localize_script('my_custom_shortcode', 'my_tinymce_object', array(
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce'    => wp_create_nonce('tinymce_nonce')
  ));

  // Enqueue the script (ensures it's available for localization)
  wp_enqueue_script('my_custom_shortcode');

  // Add the localized script to TinyMCE plugin array
  $plugin_array['my_shortcode_button'] = plugin_dir_url(__FILE__) . 'custom-shortcode.js?v='.time();

  return $plugin_array;
}

function register_aio_video_shortcode() {
  add_shortcode('aio_video', '_render_aio_video_shortcode');
}


// Define the function to handle the shortcode
function _render_aio_video_shortcode($atts) {
  // Extract shortcode attributes
  $atts = shortcode_atts(
    array(
        'id' => '',
    ),
    $atts,
    'aio_video'
  );

  $video = get_post($atts['id']);

  ob_start();
  _render_fld_video($video);
  $html = ob_get_clean();
  return $html;
}

function get_aio_video_posts() {
  // Check the nonce for security
  check_ajax_referer('tinymce_nonce', 'nonce');

  // Fetch the posts
  $args = array(
      'post_type'      => 'aio_video',
      'posts_per_page' => -1,
  );

  $query = new WP_Query($args);

  if ($query->have_posts()) {
      $posts = array();
      while ($query->have_posts()) {
          $query->the_post();
          $posts[] = array(
              'title' => get_the_title(),
              'id'    => get_the_ID(),
          );
      }
      wp_send_json_success($posts);
  } else {
      wp_send_json_error('No posts found.');
  }

  wp_die(); // This is required to terminate immediately and return a proper response
}
add_action('wp_ajax_get_aio_video_posts', 'get_aio_video_posts');
add_action('wp_ajax_nopriv_get_aio_video_posts', 'get_aio_video_posts');