
const mp4Videos = Array.from(document.querySelectorAll('.aio-video-wrapper.mp4 video'));

mp4Videos.forEach(video => {
    // Create an IntersectionObserver
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          video.play();  // Play the video
        } else {
          video.pause(); // Pause the video if it's out of the viewport
        }
      });
    });

    // Start observing the video element
    observer.observe(video);
})

document.addEventListener("DOMContentLoaded", function () {
  const lazyVideos = Array.from(document.querySelectorAll('.aio-video-wrapper.mp4 video.lazy'));

  console.log({lazyVideos})
  if ("IntersectionObserver" in window) {
    const videoObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const video = entry.target;
          video.src = video.getAttribute("data-src");
          video.load();
          video.classList.add("loaded");
          observer.unobserve(video);
        }
      });
    });

    lazyVideos.forEach((video) => {
      videoObserver.observe(video);
    });
  } else {
    // Fallback for browsers that don’t support IntersectionObserver
    lazyVideos.forEach((video) => {
      video.src = video.getAttribute("data-src");
      video.load();
      video.classList.add("loaded");
    });
  }
});