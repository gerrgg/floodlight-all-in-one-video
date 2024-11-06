<?php

function _render_fld_video_vimeo($video){
  $video_id = get_field('field_video_id', $video) ?? null;
  $thumbnail = get_field('field_thumbnail', $video) ?? null;
  $play_icon = get_field('field_play_icon', $video) ?? null;
  $video_width = get_field('field_video_width', $video) ?? null;
  $video_height = get_field('field_video_height', $video) ?? null;
  $aspect_ratio = intval($video_height) / intval($video_width) * 100 . '%';
  $autoplay = get_field('field_autoplay', $video) ?? null;
  wp_enqueue_script('aio-vimeo-sdk');
  wp_enqueue_script('aio-vimeo');

  printf(
    '<div class="aio-video-wrapper %s vimeo" style="padding-top: %s; %s" data-autoplay="%s">
      <iframe
        id="%s"
        src="https://player.vimeo.com/video/%s?%s"
        width="%s"
        height="%s"
        frameborder="0"
        allow="autoplay; fullscreen; picture-in-picture"
        allowfullscreen
      ></iframe>
      <i class="play-icon" style="%s"></i>
    </div>', 
    $thumbnail ? '--has-thumbnail hide-iframe' : '',
    $aspect_ratio,
    $thumbnail ? sprintf('background: url(\'%s\') center/cover no-repeat', $thumbnail) : '',
    $autoplay ? 'true' : 'false',
    $video->post_title,
    $video_id,
    $autoplay ? 'muted=1' : 'muted=0',
    $video_width,
    $video_height,
    $play_icon ? sprintf('background: url(\'%s\') center/contain no-repeat', $play_icon) : ''
  );
}