jQuery(function() {
	var jq = jQuery('#fld-video-upload');
	var post_id = jq.data('post-id');
	var lbl = jq.find('.label button span');
	var has_video = false;
	var working_file = false;
	var working_file_duration = 0;
	var busy_upload = false;

	var btn_cancel_replace = jQuery('#fld-video-cancel-replace');

	if (btn_cancel_replace.length) {
		btn_cancel_replace.click(function() {
			jQuery.post(
				ajaxurl + '?action=fld_video_cancel_replace',
				{
					post_id: post_id
				},
				function() {
					location.reload();
				},
				'json'
			);

			return false;
		});
	}

	function check_progress() {
		setTimeout(function() {
			jQuery.get(
				ajaxurl + '?action=fld_video_get_status',
				{
					post_id: post_id
				},
				function(result) {
					if (typeof result == "string") {
						show_label('Error: ' + result);
					} else {
						if (result && result.result && result.result.status) {
							if (result.result.readyToStream) {
								show_label('Complete.');
								jQuery('#publish').click();
							} else {
								var status = result.result.status;
								if (typeof status.state != 'undefined') {
									if (status.state == 'inprogress' || status.state == 'ready') {
										show_label('Processing: ' + status.step + ' ' + (status.pctComplete*1) + '%');
									} else {
										show_label('Processing: ' + status.state);
									}
									check_progress();
								}
							}
						}
					}
				},
				'json'
			);
		}, 3000);
	}

	function show_label(text) {
		lbl.text(text);
	}
	function set_working_file(file, video, duration) {
		working_file = file;
		has_video = true;
		working_file_duration = Math.ceil(duration);
		show_label('Starting upload...');
		jq.append(video);
		jq.addClass('video-preview');

		jQuery('#poststuff')
			.find('input[type="submit"]:not([disabled])')
			.attr('disabled', 'disabled')
			.addClass('fld-video-disabled-button');

		begin_upload();
	}

	function begin_upload() {
		if (busy_upload) {
			return false;
		}
		busy_upload = true;

		var filesize = working_file.size;
		var filename = working_file.name;

		var progress = jQuery('#fld-video-upload .progress');
		progress.css('width', 0);

		var btn_cancel = jQuery('#fld-video-cancel-upload');
		btn_cancel.show();

		btn_cancel_replace.hide();

		btn_cancel.click(function() {
			jQuery.post(
				ajaxurl + '?action=fld_video_cancel',
				{
					post_id: post_id
				},
				function(result) {
					location.reload();
				},
				'json'
			);
			return false;
		});

		var upload = new tus.Upload(working_file, {
			endpoint: ajaxurl + "?action=fld_video_begin_upload&post_id=" + post_id + "&filesize=" + filesize + '&filename=' + encodeURIComponent(filename) + '&duration=' + encodeURIComponent(working_file_duration),
			chunkSize:  5242880,
			retryDelays: [0, 3000, 5000/*, 10000, 20000, 60000, 120000, 180000*/],
			metadata: {
				name: working_file.name,
				duration: working_file_duration
			},
			onError: function(error) {
				jq.find('video').remove();
				jq.removeClass('video-preview');
				progress.css('width', 0);

				show_label(error);
			},
			onProgress: function(bytesUploaded, bytesTotal) {
				var percentage = (bytesUploaded / bytesTotal * 100).toFixed(2);
				progress.css('width', percentage + '%');
			},
			onSuccess: function() {
				btn_cancel.hide();
				show_label("Complete! Processing...");

				jQuery('#poststuff')
					.find('input[type="submit"].fld-video-disabled-button')
					.removeAttr('disabled');

				check_progress();
			}
		});

		upload.start();
	};

	var btn_select = jQuery('#btn-fld-video-upload');
	var jq_file_input = jQuery('#fld-video-file');
	btn_select.click(function() {
		if (!has_video) {
			jq_file_input.click();
		}
		return false;
	});
	jq_file_input.click(function(ev) {
		ev.stopPropagation();
	});

	function process_file(f) {
		if (f.type.match('video.*')) {
			show_label('Processing...');
			
			var video = document.createElement('video');
			video.preload = 'metadata';
			video.controls = true;
			
			video.onloadedmetadata = function() {
				var duration = video.duration;
				if (duration > 0) {
					if (duration > 1 && duration <= 3600) {
						set_working_file(f, video, duration);
					} else {
						show_label('Video must have a duration greater than 1 second and less than 1 hour.');
					}
				} else {
					show_label('Video has duration of 0. Is it a real video file?');	
				}
			};
			video.onerror = function() {
				show_label('Unable to determine length of video. Is it a real video file?');
			};
			video.src = URL.createObjectURL(f);
			
		} else {
			show_label('Please select a video file.');
		}
	}

	jq_file_input.change(function(ev) {
		if (!has_video) {
			var files = jq_file_input.get(0).files;
			if (files.length == 1) {
				process_file(files[0]);
			} else {
				show_label('Please select one file.');
			}
		}
	});

	jq.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
		e.preventDefault();
		e.stopPropagation();
	})
	.on('dragover dragenter', function() {
		if (!has_video)
			jq.addClass('active-drag');
	})
	.on('dragleave dragend drop', function() {
		if (!has_video)
			jq.removeClass('active-drag');
	})
	.on('drop', function(e) {
		if (has_video) {
			return false;
		}
		var files = e.originalEvent.dataTransfer.files;
		if (files.length == 1) {
			var f = files[0];

			process_file(f);
		} else {
			show_label('Please select 1 file.');
		}
	});
});