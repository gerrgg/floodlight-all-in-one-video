const vimeoVideos = Array.from(document.querySelectorAll('.aio-video-wrapper.vimeo iframe'));

vimeoVideos.forEach(video => {
  var player = new Vimeo.Player(video);
  const wrapper = video.parentElement
  const autoplay = wrapper.dataset.autoplay;
  const hasThumbnail = wrapper.classList.contains('--has-thumbnail');
  const playIcon = wrapper.querySelector('.play-icon');

  if( autoplay === 'true'){
    var observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          // The iframe is in view, start playing the video
          player.play().catch(function(error) {
              console.error('Error playing the video:', error);
          });
        } else {
          // The iframe is out of view, pause the video
          player.pause().catch(function(error) {
              console.error('Error pausing the video:', error);
          });
        }
      });
    }, {
      threshold: 0.5  // Trigger when 50% of the iframe is visible
    });
  
    observer.observe(video);
  }

  if( hasThumbnail ){
    wrapper.addEventListener('click', () => {
      wrapper.classList.remove('hide-iframe');
      playIcon.remove()
      player.play().catch(function(error) {
        console.error('Error playing the video:', error);
      });
      console.log('click');
    })
  }
})

