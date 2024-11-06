(function () {
  tinymce.create("tinymce.plugins.my_shortcode_button", {
    init: function (editor, url) {
      let values = [];
      let done = false;

      console.log(editor);

      // editor.ui.registry.addIcon(
      //   "triangleUp",
      //   '<svg height="24" width="24"><path d="M12 0 L24 24 L0 24 Z" /></svg>'
      // );

      editor.addButton("my_shortcode_button", {
        text: " FLD Video",
        icon: "dashicon dashicons-format-video",
        onclick: function () {
          if (done === false) {
            jQuery.ajax({
              url: my_tinymce_object.ajax_url,
              type: "POST",
              data: {
                action: "get_aio_video_posts",
                nonce: my_tinymce_object.nonce,
              },
              success: function (response) {
                done = true;
                if (response.success) {
                  response.data.forEach((r) => {
                    values.push({ text: r.title, value: r.id });
                  });
                  console.log(response.data, values);
                  // Do something with the response data
                } else {
                  console.log("Failed to fetch posts.");
                }
              },
              error: function (xhr, status, error) {
                console.log("AJAX error: " + error);
              },
            });
          }

          // Open a dialog to collect arguments
          editor.windowManager.open({
            title: "Insert Shortcode",
            body: [
              {
                type: "listbox",
                name: "videoID",
                label: "AIO Video",
                values,
              },
            ],
            onsubmit: function (e) {
              // Insert content when the form is submitted
              editor.insertContent('[aio_video id="' + e.data.videoID + '"]');
            },
          });
        },
      });
    },
  });
  tinymce.PluginManager.add(
    "my_shortcode_button",
    tinymce.plugins.my_shortcode_button
  );
})();
