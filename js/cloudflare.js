document.addEventListener("DOMContentLoaded", function() {
  const wrapper = document.querySelector('.aio-video-wrapper');
  const iframe = wrapper.querySelector('iframe');
  
  if (iframe) {
    iframe.onload = () => {
      // Add a class to remove the loader and show video when ready
      setTimeout(() => {
        wrapper.classList.add('video-ready');
      },10000)
    };
  }
});
