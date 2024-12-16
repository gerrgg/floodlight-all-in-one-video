// Load the IFrame Player API code asynchronously.
var tag = document.createElement("script");
tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName("script")[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

var players = {};

function onYouTubeIframeAPIReady() {
  const videos = Array.from(document.querySelectorAll(".aio-video-youtube"));

  videos.forEach((v) => {
    const delay = v.dataset.delay === "true";
    const playIcon = v.parentElement.querySelector(".play-icon");

    if (delay === false) {
      createPlayer(v);
    } else {
      v.addEventListener("click", () => {
        createPlayer(v, 0);
        playIcon.remove();
      });
    }
  });
}

function createPlayer(v, muted = 1) {
  console.log("creating player");

  players[v.id] = new YT.Player(v, {
    height: v.dataset.height,
    width: v.dataset.width,
    videoId: v.dataset.videoid, // Example video ID
    playerVars: {
      autoplay: 1, // Enable autoplay
      mute: muted, // Mute or unmute based on the `muted` argument
    },
    events: {
      onReady: (event) => {
        // console.log("Player ready for:", v.id);
        event.target.playVideo();
      },
      onStateChange: (event) => {
        // console.log("Player state changed for:", v.id, "State:", event.data);
      },
    },
  });
}

function onPlayerReady(event) {}

function onPlayerReadyAutoplay(event) {
  const playerElement = event.target.g; // The iframe element

  // Use the Intersection Observer API to detect when the player enters the viewport
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          event.target.playVideo();
        } else {
          event.target.pauseVideo();
        }
      });
    },
    {
      threshold: 0.5,
    }
  );

  // Observe the player element
  observer.observe(playerElement);
}

function onPlayerStateChange(event) {
  // Player state change
}
