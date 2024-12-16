<?php

function _render_fld_video_mp4($video){
  $video_url = get_field('field_my_file', $video) ?? null;
  $thumbnail = get_field('field_thumbnail', $video) ?? null;
  $play_icon = get_field('field_play_icon', $video) ?? null;
  $video_width = get_field('field_video_width', $video) ?? null;
  $video_height = get_field('field_video_height', $video) ?? null;
  $autoplay = get_field('field_autoplay', $video) ?? null;
  $background = get_field('field_background', $video) ?? null;
  $lazyload = get_field('field_lazy', $video) ?? null;
  $aspect_ratio = intval($video_height) / intval($video_width) * 100 . '%';

  $controls = $autoplay ? 'controls muted' : 'controls';

  if( $background ){
    $controls = 'muted autoplay loop';
  }

  wp_enqueue_script('aio-mp4');

  printf(
    '<div class="aio-video-wrapper %s mp4 %s" style="padding-top: %s;">
        <video class="%s" width="%s" height="%s" %s %s %s playsinline >
          <source src="%s" type="video/mp4">
        </video> 
      <i class="play-icon" style="%s"></i>
    </div>', 
    $thumbnail ? '--has-thumbnail hide-iframe' : '',
    $background ? 'background' : '',
    $aspect_ratio,
    $lazyload ? 'lazy' : '',
    $video_width,
    $video_height,
    $lazyload ? sprintf('preload="none" data-src="%s"', $video_url) : '',
    $thumbnail ? sprintf('poster=%s', $thumbnail) : '',
    $controls,
    $lazyload ? '' : $video_url,
    $play_icon ? sprintf('background: url(\'%s\') center/contain no-repeat', $play_icon) : ''
  );
}
