<?php 

function create_aio_video_post_type() {
  // Define labels for the custom post type
  $labels = array(
    'name'                  => _x('FLD Videos', 'Post type general name', 'textdomain'),
    'singular_name'         => _x('FLD Video', 'Post type singular name', 'textdomain'),
    'menu_name'             => _x('FLD Videos', 'Admin Menu text', 'textdomain'),
    'name_admin_bar'        => _x('FLD Video', 'Add New on Toolbar', 'textdomain'),
    'add_new'               => __('Add New', 'textdomain'),
    'add_new_item'          => __('Add New FLD Video', 'textdomain'),
    'new_item'              => __('New FLD Video', 'textdomain'),
    'view_item'             => __('View FLD Video', 'textdomain'),
    'all_items'             => __('All FLD Videos', 'textdomain'),
    'search_items'          => __('Search FLD Videos', 'textdomain'),
    'parent_item_colon'     => __('Parent FLD Videos:', 'textdomain'),
    'not_found'             => __('No FLD Videos found.', 'textdomain'),
    'not_found_in_trash'    => __('No FLD Videos found in Trash.', 'textdomain'),
    'featured_image'        => _x('FLD Video Cover Image', 'textdomain'),
    'set_featured_image'    => _x('Set cover image', 'textdomain'),
    'remove_featured_image' => _x('Remove cover image', 'textdomain'),
    'use_featured_image'    => _x('Use as cover image', 'textdomain'),
    'archives'              => _x('FLD Video archives', 'textdomain'),
    'insert_into_item'      => _x('Insert into FLD Video', 'textdomain'),
    'uploaded_to_this_item' => _x('Uploaded to this FLD Video', 'textdomain'),
    'filter_items_list'     => _x('Filter FLD Videos list', 'textdomain'),
    'items_list_navigation' => _x('FLD Videos list navigation', 'textdomain'),
    'items_list'            => _x('FLD Videos list', 'textdomain'),
  );

  // Register the custom post type
  register_post_type('aio_video', array(
      'labels'                => $labels,
      'public'                => true,
      'publicly_queryable'    => true,
      'show_ui'               => true,
      'show_in_menu'          => true,
      'query_var'             => true,
      'rewrite'               => array('slug' => 'aio-video'),
      'menu_icon'             => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGZpbGw9IiNGMEY2RkM5OSI+PHBhdGggZD0iTTI0IDIzaC0yNHYtMjFoMjR2MjF6bS0yMC0xdi00aC0zdjRoM3ptMTUgMHYtMTloLTE0djE5aDE0em00IDB2LTRoLTN2NGgzem0tNi05LjVsLTkgNXYtMTBsOSA1em0zIC41djRoM3YtNGgtM3ptLTE2IDR2LTRoLTN2NGgzem01LTEuMmw1Ljk0MS0zLjMtNS45NDEtMy4zdjYuNnptMTEtNy44djRoM3YtNGgtM3ptLTE2IDR2LTRoLTN2NGgzem0xNi05djRoM3YtNGgtM3ptLTE2IDR2LTRoLTN2NGgzeiIvPjwvc3ZnPg==',
      'capability_type'       => 'post',
      'has_archive'           => true,
      'hierarchical'          => false,
      'menu_position'         => 5,
      'supports'              => array('title', 'thumbnail'), // Only title and thumbnail are supported
  ));
}

function remove_aio_video_support() {
  // Remove support for comments and discussion settings
  remove_post_type_support('aio_video', 'comments');    // Comments
  remove_post_type_support('aio_video', 'discussion');  // Discussion settings
}

function remove_aio_video_meta_boxes() {
  // Remove comments meta box for the AIO Video post type
  remove_meta_box('commentsdiv', 'aio_video', 'normal');
}

function create_acf_field_group_for_aio_video() {
  // Check if ACF is active and the function exists
  if (function_exists('acf_add_local_field_group')) {

      // Define the field group
      $field_group = array(
          'key' => 'group_aio_video_settings', // Unique key for the field group
          'title' => 'AIO Video Settings',     // Title of the field group
          'fields' => array(
              array(
                  'key' => 'field_button_group',   // Unique key for the button group field
                  'label' => 'Select Option',       // Label displayed in the admin
                  'name' => 'button_group',         // Name used in the database
                  'type' => 'button_group',         // Field type
                  'instructions' => 'Choose an option.', // Instructions for the field
                  'required' => 1,                  // Whether the field is required
                  'choices' => array(
                      'youtube' => 'Youtube',
                      'vimeo' => 'Vimeo',
                      'mp4' => 'MP4',
                      'cloudflare' => 'Cloudflare',
                  ),                                // Button group choices
                  'allow_null' => 0,                // Allow null option
                  'layout' => 'horizontal',         // Layout (horizontal or vertical)
              ),
              array(
                'key' => 'field_video_id',   // Unique key for the text field
                'label' => 'Video ID',        // Label displayed in the admin
                'name' => 'video_id',         // Name used in the database
                'type' => 'text',             // Field type
                'instructions' => 'Enter the Video ID.', // Instructions for the field
                'required' => 0,              // Whether the field is required
                'placeholder' => '',          // Placeholder text
                'prepend' => '',              // Optional prepend text
                'append' => '',               // Optional append text
                'maxlength' => '',            // Optional max length
                'conditional_logic' => array( // Conditional logic for the field
                    array(
                        array(
                            'field' => 'field_button_group', // Reference to the button group field
                            'operator' => '==',             // Logic operator (options: '==', '!=', '>', '<', '>=', '<=')
                            'value' => 'mp4',              // Value to check (if not 'mp4')
                        ),
                        array(
                          'field' => 'field_button_group', // Reference to the button group field
                          'operator' => '==',             // Logic operator (options: '==', '!=', '>', '<', '>=', '<=')
                          'value' => 'cloudflare',              // Value to check (if not 'mp4')
                      ),
                    ),
                ),
              ),
              array(
                'key' => 'field_my_file',
                'label' => 'Video File',
                'name' => 'my_file',
                'type' => 'file',
                'return_format' => 'url', // This ensures the URL is returned
                'instructions' => 'Upload an mp4',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'conditional_logic' => array( // Conditional logic for the field
                      array(
                          array(
                              'field' => 'field_button_group', // Reference to the button group field
                              'operator' => '==',             // Logic operator (options: '==', '!=', '>', '<', '>=', '<=')
                              'value' => 'mp4',              // Value to check (if not 'mp4')
                          ),
                      ),
                  ),
                'library' => 'all',
                'min_size' => '',
                'max_size' => '',
                'mime_types' => '',
              ),
              array(
                'key' => 'field_my_file_cloudflare', // Field key
                'label' => 'Video File',             // Field label
                'name' => 'my_file_cloudflare',      // Field name
                'type' => 'post_object',             // Field type changed to post selector
                'post_type' => array('fld_video'),   // Restrict to selecting 'fld_video' posts
                'return_format' => 'id',             // Return the post ID (or 'object' if you need the full post object)
                'instructions' => 'Select a Cloudflare Video from the list.', // Instructions for the user
                'required' => 0,                     // Whether the field is required
                'conditional_logic' => 0,            // No conditional logic for this example
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'conditional_logic' => array(       // Conditional logic for the field (optional)
                    array(
                        array(
                            'field' => 'field_button_group',  // Reference to the button group field
                            'operator' => '==',               // Logic operator
                            'value' => 'cloudflare',          // Value to trigger this logic
                        ),
                    ),
                ),
              ),
              array(
                'key' => 'field_background',
                'label' => 'Background',
                'name' => 'background',
                'type' => 'true_false',
                'instructions' => 'Enable video to play as background and loop muted',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                  'width' => '',
                  'class' => '',
                  'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => 'Yes',
                'ui_off_text' => 'No',
                'conditional_logic' => array( // Conditional logic for the field
                  array(
                    array(
                        'field' => 'field_button_group', // Reference to the button group field
                        'operator' => '==',             // Logic operator (options: '==', '!=', '>', '<', '>=', '<=')
                        'value' => 'mp4',              // Value to check (if not 'mp4')
                    ),
                  ),
                ),
              ),
              array(
                'key' => 'field_autoplay',
                'label' => 'Autoplay',
                'name' => 'autoplay',
                'type' => 'true_false',
                'instructions' => 'Enable autoplay for the video',
                'required' => 0,
                'conditional_logic' => array( // Conditional logic for the field
                  array(
                    array(
                        'field' => 'field_button_group', // Reference to the button group field
                        'operator' => '==',             // Logic operator (options: '==', '!=', '>', '<', '>=', '<=')
                        'value' => 'mp4',              // Value to check (if not 'mp4')
                    ),
                  ),
                ),
                'wrapper' => array(
                  'width' => '',
                  'class' => '',
                  'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => 'Yes',
                'ui_off_text' => 'No',
              ),
              array(
                'key' => 'field_lazy',
                'label' => 'Lazy Load',
                'name' => 'lazy_load',
                'type' => 'true_false',
                'instructions' => 'Enable Lazy Load for better load times',
                'required' => 0,
                'conditional_logic' => array(
                  array(
                    array(
                      'field' => 'field_button_group',
                      'operator' => '==',
                      'value' => 'mp4',
                    ),
                  ),
                ),
                'wrapper' => array(
                  'width' => '',
                  'class' => '',
                  'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => 'Yes',
                'ui_off_text' => 'No',
              ),
              array(
                'key' => 'field_thumbnail',    // Unique key for the image field
                'label' => 'Thumbnail',        // Label displayed in the admin
                'name' => 'thumbnail',         // Name used in the database
                'type' => 'image',             // Field type
                'instructions' => 'Upload or select a thumbnail image.', // Instructions for the field
                'required' => 0,              // Whether the field is required
                'return_format' => 'url',     // Return format (options: 'array', 'url', 'id')
                'preview_size' => 'thumbnail', // Preview size of the image
                'library' => 'all',           // Media library (options: 'all', 'uploadedTo', 'array', 'id')
                'min_width' => '',            // Minimum width of image
                'min_height' => '',           // Minimum height of image
                'min_size' => '',             // Minimum file size
                'max_width' => '',            // Maximum width of image
                'max_height' => '',           // Maximum height of image
                'max_size' => '',             // Maximum file size
                'mime_types' => '',           // Allowed mime types
                'conditional_logic' => array( // Conditional logic for the field
                  array(
                    array(
                        'field' => 'field_button_group', // Reference to the button group field
                        'operator' => '!=',             // Logic operator (options: '==', '!=', '>', '<', '>=', '<=')
                        'value' => 'cloudflare',              // Value to check (if not 'mp4')
                    ),
                  ),
                ),
              ),
              array(
                'key' => 'field_play_icon',    // Unique key for the image field
                'label' => 'Play Icon (optional)',        // Label displayed in the admin
                'name' => 'play_icon',         // Name used in the database
                'type' => 'image',             // Field type
                'instructions' => 'Upload or select an icon for the play button.', // Instructions for the field
                'required' => 0,              // Whether the field is required
                'return_format' => 'url',     // Return format (options: 'array', 'url', 'id')
                'preview_size' => 'thumbnail', // Preview size of the image
                'library' => 'all',           // Media library (options: 'all', 'uploadedTo', 'array', 'id')
                'min_width' => '',            // Minimum width of image
                'min_height' => '',           // Minimum height of image
                'min_size' => '',             // Minimum file size
                'max_width' => '',            // Maximum width of image
                'max_height' => '',           // Maximum height of image
                'max_size' => '',             // Maximum file size
                'mime_types' => '',           // Allowed mime types
              ),
              array(
                'key' => 'field_video_width',  // Unique key for the width field
                'label' => 'Video Width',      // Label displayed in the admin
                'name' => 'video_width',       // Name used in the database
                'type' => 'number',            // Field type
                'instructions' => 'Enter the width of the video in pixels.', // Instructions for the field
                'required' => 0,              // Whether the field is required
                'min' => 0,                   // Minimum value
                'max' => '',                  // Maximum value
                'step' => 1,                  // Step value for increments
                'default_value' => 1920
            ),
            array(
                'key' => 'field_video_height', // Unique key for the height field
                'label' => 'Video Height',     // Label displayed in the admin
                'name' => 'video_height',      // Name used in the database
                'type' => 'number',            // Field type
                'instructions' => 'Enter the height of the video in pixels.', // Instructions for the field
                'required' => 0,              // Whether the field is required
                'min' => 0,                   // Minimum value
                'max' => '',                  // Maximum value
                'step' => 1,                  // Step value for increments
                'default_value' => 1080
            ),
            
          ),
          'location' => array(
              array(
                  array(
                      'param' => 'post_type',
                      'operator' => '==',
                      'value' => 'aio_video',          // Target custom post type
                  ),
              ),
          ),
          'style' => 'default',
          'position' => 'acf_after_title',
          'label_placement' => 'top',
          'instruction_placement' => 'label',
          'active' => true,
          'description' => '',
      );

      // Register the field group
      acf_add_local_field_group($field_group);
  }
}

function remove_my_post_metaboxes() {
  remove_meta_box( 'authordiv','aio_video','normal' ); // Author Metabox
  remove_meta_box( 'commentstatusdiv','aio_video','normal' ); // Comments Status Metabox
  remove_meta_box( 'commentsdiv','aio_video','normal' ); // Comments Metabox
  remove_meta_box( 'postcustom','aio_video','normal' ); // Custom Fields Metabox
  remove_meta_box( 'postexcerpt','aio_video','normal' ); // Excerpt Metabox
  remove_meta_box( 'revisionsdiv','aio_video','normal' ); // Revisions Metabox
  remove_meta_box( 'slugdiv','aio_video','normal' ); // Slug Metabox
  remove_meta_box( 'trackbacksdiv','aio_video','normal' ); // Trackback Metabox
}