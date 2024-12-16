<?php

function _render_fld_video_cloudflare($video){
  // Get the necessary ACF field values
  $video = get_field('field_my_file_cloudflare', $video); // Cloudflare stream ID
  $thumbnail = get_field('field_thumbnail', $video) ?? null;
  $play_icon = get_field('field_play_icon', $video) ?? null;
  $video_width = get_field('field_video_width', $video) ?? null;
  $video_height = get_field('field_video_height', $video) ?? null;
  $aspect_ratio = intval($video_height) / intval($video_width) * 100 . '%';

  $video_id = get_post_meta($video, '_video_id', true);

  if( $background ){
    $controls = 'muted autoplay loop';
  }
  
  wp_enqueue_script('aio-cloudflare');

  // Build the Cloudflare video URL
  $video_url = "https://iframe.videodelivery.net/$video_id"; // Cloudflare stream URL

  printf(
    '<div class="aio-video-wrapper %s cloudflare" style="padding-top: %s;">
      <img class="poster" src="%s" />
      <iframe src="%s?autoplay=true&muted=true&loop=true" width="%s" height="%s" allow="autoplay" playsinline></iframe>
    </div>',
    $background ? 'background' : '',
    $aspect_ratio,
    cloudflareUpload::get_thumbnail_url_for_video($video_id, 600),
    $video_url,
    $video_width,
    $video_height
  );
}
