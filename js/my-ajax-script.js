window.getAioVideos = jQuery.ajax({
  url: my_ajax_object.ajax_url,
  type: "POST",
  data: {
    action: "get_aio_video_posts",
    nonce: my_ajax_object.nonce,
  },
  success: function (response) {
    if (response.success) {
      console.log(response.data);
      // Do something with the response data
    } else {
      console.log("Failed to fetch posts.");
    }
  },
  error: function (xhr, status, error) {
    console.log("AJAX error: " + error);
  },
});
