// Play/Pause videos when they come into/out of the viewport
const mp4Videos = Array.from(
  document.querySelectorAll(".aio-video-wrapper.mp4 video")
);

mp4Videos.forEach((video) => {
  // Ensure videos are muted and set to autoplay before playing
  video.muted = true;
  video.autoplay = true;

  // Create an IntersectionObserver to detect when the video enters or leaves the viewport
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        video.play(); // Play the video when it's in the viewport
      } else {
        video.pause(); // Pause the video when it leaves the viewport
      }
    });
  });

  // Start observing the video element
  observer.observe(video);
});

document.addEventListener("DOMContentLoaded", function () {
  const lazyVideos = Array.from(
    document.querySelectorAll(".aio-video-wrapper.mp4 video.lazy")
  );

  // Check if IntersectionObserver is supported
  if ("IntersectionObserver" in window) {
    const videoObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const video = entry.target;
          video.src = video.getAttribute("data-src");
          video.muted = true;
          video.autoplay = true;
          video.load();
          video.classList.add("loaded");
          observer.unobserve(video);
        }
      });
    });

    // Start observing the lazy videos
    lazyVideos.forEach((video) => {
      videoObserver.observe(video);
    });
  } else {
    // Fallback for browsers that don’t support IntersectionObserver
    lazyVideos.forEach((video) => {
      video.src = video.getAttribute("data-src");
      video.muted = true;
      video.autoplay = true;
      video.load();
      video.classList.add("loaded");
    });
  }
});
