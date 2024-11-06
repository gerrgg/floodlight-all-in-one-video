<?php

function _render_fld_video_youtube($video){
  $video_id = get_field('field_video_id', $video) ?? null;
  $thumbnail = get_field('field_thumbnail', $video) ?? null;
  $play_icon = get_field('field_play_icon', $video) ?? null;
  $video_width = get_field('field_video_width', $video) ?? null;
  $video_height = get_field('field_video_height', $video) ?? null;
  $aspect_ratio = intval($video_height) / intval($video_width) * 100 . '%';
  $autoplay = get_field('field_autoplay', $video) ?? null;
  wp_enqueue_script('aio-youtube');

  printf(
    '<div class="aio-video-wrapper %s" style="padding-top: %s; %s">
      <div id="%s" class="aio-video-youtube" data-delay="%s" data-videoId="%s" data-width="%s" data-height="%s" data-autoplay="%s"></div>
      <i class="play-icon" style="%s"></i>
    </div>', 
    $thumbnail ? '--has-thumbnail' : '',
    $aspect_ratio,
    $thumbnail ? sprintf('background: url(\'%s\') center/cover no-repeat', $thumbnail) : '',
    $video->post_title,
    $thumbnail ? 'true' : 'false',
    $video_id,
    $video_width,
    $video_height,
    $autoplay ? 'true' : 'false',
    $play_icon ? sprintf('background: url(\'%s\') center/contain no-repeat', $play_icon) : ''
  );
}
