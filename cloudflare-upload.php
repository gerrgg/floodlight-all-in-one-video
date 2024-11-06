<?php
	/*
		Plugin Name: Floodlight Video Embeds
	*/
	//acf field
	//cron job to remove 'waiting for upload' status of a certain age
	class cloudflareUpload {
		const API_KEY = 'XuNYA_QSZ14-Ei644A9pgeZnUgDJRx4RAuIcCne4';
		const ACCOUNT_ID = '48a026aca6318f6152db520799c95956';

		function __construct() {
			add_action('init', array($this, 'create_post_type'));
			add_action('wp_ajax_fld_video_begin_upload', array($this, 'ajax_begin_upload'));
			add_action('wp_ajax_fld_video_save', array($this, 'ajax_save'));
			add_action('wp_ajax_fld_video_cancel', array($this, 'ajax_cancel'));
			add_action('wp_ajax_fld_video_get_status', array($this, 'ajax_get_status'));
			add_action('wp_ajax_fld_video_replace', array($this, 'ajax_replace'));
			add_action('wp_ajax_fld_video_cancel_replace', array($this, 'ajax_replace_cancel'));

			add_action('manage_fld_video_posts_custom_column', array($this, 'posts_column_data'), 10, 2);
			add_filter('manage_fld_video_posts_columns', array($this, 'posts_columns'));
		}

		public function posts_column_data($column, $post_id) {
			if ($column == 'thumbnail') {
				$video_id = get_post_meta($post_id, '_video_id', true);
				if ($video_id) {
					echo self::get_thumbnail_for_video($video_id);
				}
			}
		}

		public static function get_thumbnail_for_video($video_id, $extra_style = "") {
			return '<img src="https://videodelivery.net/' . $video_id . '/thumbnails/thumbnail.gif?time=0s&width=250&duration=4s&fit=clip" style="max-width:100%;height:auto;' . $extra_style . '" width="250" alt="Video Thumbnail">';
		}

    public static function get_thumbnail_url_for_video($video_id, $width = 250) {
			return 'https://videodelivery.net/' . $video_id . '/thumbnails/thumbnail.gif?time=0s&width='. $width .'&duration=4s&fit=clip';
		}

		public static function get_thumbnail_for_post($post_id, $extra_style = "") {
			$video_id = get_post_meta($post_id, '_video_id', true);
			if ($video_id) {
				return self::get_thumbnail_for_video($video_id, $extra_style);
			}
			return "";
		}

		public function posts_columns($columns) {
			$result = array();
			foreach($columns as $key => $val) {
				$result[$key] = $val;

				if ($key == 'title') {
					$result['thumbnail'] = 'Thumbnail';
				}
			}

			return $result;
		}

		public function ajax_get_status() {
			$post_id = (int)$_GET['post_id'];

			$video_id = get_post_meta($post_id, '_uploading_video_id', true);

			$headers = array();
			$headers[] = 'Authorization: Bearer ' . self::API_KEY;
			$headers[] = 'Content-Type:application/json';

			$url = 'https://api.cloudflare.com/client/v4/accounts/' . self::ACCOUNT_ID . '/stream/' . $video_id;
			$curl = curl_init( $url );
			
			curl_setopt($curl, CURLOPT_URL, $url);			
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

			$response = curl_exec( $curl );

			if ($response == false) {
				$response = curl_error($curl);
			} else {
				$response = json_decode($response);
			}

			curl_close( $curl );

			wp_send_json($response);
		}

		public function ajax_begin_upload() {
			//post_id, filesize, filename
			$post_id = (int)$_GET['post_id'];
			$filesize = (int)$_GET['filesize'];
			$filename = $_GET['filename'];
			$duration = (int)$_GET['duration'];

			//add some padding for the duration just in case
			$duration += 10;

			//limited to 1 hour
			if ($duration > 3600) {
				$duration = 3600;
			}

			$meta = array();
			$meta['name'] = $filename;
			$meta['post_id'] = $post_id;
			$meta['site'] = get_site_url();

			$metadata = '';
			$metadata .= 'maxDurationSeconds ' . base64_encode($duration);
			$metadata .= ',meta ' . base64_encode(json_encode($meta));
			//$metadata .= ',expiry ' . base64_encode(gmdate('Y-m-d\TH:i:sP', strtotime("+180 seconds")));

			$headers = array();
			$headers[] = 'Authorization: Bearer ' . self::API_KEY;
			$headers[] = 'Content-Type:application/json';
			$headers[] = 'Tus-Resumable: 1.0.0';
			$headers[] = 'Upload-Length: ' . $filesize;
			$headers[] = 'Upload-Metadata: ' . $metadata;


			$url = 'https://api.cloudflare.com/client/v4/accounts/' . self::ACCOUNT_ID . '/stream?direct_user=true';
			$curl = curl_init( $url );
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt( $curl, CURLOPT_POST, true );
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt($curl, CURLOPT_HEADER, 1);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			$response = curl_exec( $curl );

			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$header = substr($response, 0, $header_size);

			curl_close( $curl );

			$header = explode("\r\n", $header);

			$location = "";
			$video_id = "";

			foreach($header as $row) {
				if (stripos($row,'location: ') === 0) {
					$location = substr($row, 10);
				} elseif (stripos($row,'stream-media-id: ') === 0) {
					$video_id = substr($row, 17);
				}
			}

			if ($location && $video_id) {
				update_post_meta($post_id, '_uploading_video_id', $video_id);

				$p = get_post($post_id);
				if ($p->post_status == 'auto-draft') {
					wp_update_post(array(
						'ID' => $post_id,
						'post_status' => 'draft'
					));
				}
				header("Access-Control-Expose-Headers: Location");
				header("Access-Control-Allow-Headers: *");
				header("Access-Control-Allow-Origin: *");
				header("Location: " . $location);
				status_header(204);
				exit();
			} else {
				status_header(500);
				exit();
			}
		}

		public function ajax_cancel() {
			$post_id = (int)$_POST['post_id'];

			update_post_meta($post_id, '_uploading_video_id', '');

			wp_send_json(array(
				'success' => true
			));
		}

		public function ajax_save() {
			$post_id = (int)$_POST['post_id'];

			$in_progress_video_id = get_post_meta($post_id, '_uploading_video_id', true);

			update_post_meta($post_id, '_uploading_video_id', '');
			update_post_meta($post_id, '_video_id', $in_progress_video_id);
			update_post_meta($post_id, '_begin_replace', '0');

			wp_send_json(array(
				'success' => true
			));
		}

		public function ajax_replace() {
			$post_id = (int)$_POST['post_id'];

			update_post_meta($post_id, '_begin_replace', '1');

			wp_send_json(array(
				'success' => true
			));
		}

		public function ajax_replace_cancel() {
			$post_id = (int)$_POST['post_id'];

			update_post_meta($post_id, '_begin_replace', '0');

			wp_send_json(array(
				'success' => true
			));
		}

		public function create_post_type() {
			register_post_type('fld_video', array(
				'label' => 'Cloudflare Videos',
				'show_ui' => true,
				'menu_icon' => 'dashicons-video-alt2',
				'supports' => array('title', 'revisions'),
				'rewrite' => false,
				'delete_with_user' => false,
				'register_meta_box_cb' => array($this, 'add_meta_boxes'),
        'show_in_menu' => 'edit.php?post_type=aio_video',
			));
		}

		public function add_meta_boxes() {
			add_meta_box(
				'fld_video_edit',
				'Edit Video',
				array($this, 'fld_video_edit_meta_box'),
				null,
				'normal',
				'high'
			);
		}

		public function fld_video_edit_meta_box() {
			$post_id = get_the_ID();
			
			$in_progress_video_id = get_post_meta($post_id, '_uploading_video_id', true);
			$video_id = get_post_meta($post_id, '_video_id', true);
			$begin_replace = get_post_meta($post_id, '_begin_replace', true);

			if ($begin_replace != 1 && empty($video_id) && !empty($in_progress_video_id)) {
				update_post_meta($post_id, '_uploading_video_id', '');
				update_post_meta($post_id, '_video_id', $in_progress_video_id);
				$video_id = $in_progress_video_id;
				$in_progress_video_id = "";
			}

			if ($in_progress_video_id) {
				$this->show_in_progress($in_progress_video_id);
			} else if (!$video_id || $begin_replace == 1) {
				$this->show_upload_ui($begin_replace == 1);
			} else {
				$this->show_current_ui($video_id);
			}
		}

		private function show_current_ui($video_id) {
?>
<h3>Video</h3>
<div style="position:relative">
	<div style="padding-top:56.25%"></div>
	<iframe	src="https://iframe.videodelivery.net/<?php echo $video_id; ?>"
		style="border: none;width:100%;height:100%;position:absolute;top:0;left:0;"
		height="720"
		width="1280"
		allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;"
		allowfullscreen="true"></iframe>
</div>
<br>
<div>
	<button id="fld-video-replace">Replace</button>
</div>
<script>
	jQuery(function() {
		jQuery('#fld-video-replace').click(function() {
			jQuery.post(
				ajaxurl + '?action=fld_video_replace',
				{
					post_id: <?php the_ID(); ?>
				},
				function(result) {
					location.reload();
				},
				'json'
			);
			return false;
		});
	});
</script>
<?php
		}

		private function show_in_progress($video_id) {
?>
<h3>Video (Draft)</h3>
<div style="position:relative">
	<div style="padding-top:56.25%"></div>
	<iframe	src="https://iframe.videodelivery.net/<?php echo $video_id; ?>"
		style="border: none;width:100%;height:100%;position:absolute;top:0;left:0;"
		height="720"
		width="1280"
		allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;"
		allowfullscreen="true"></iframe>
</div>
<br>
<div>
	<button id="fld-video-save">Save</button>
	<button id="fld-video-cancel">Cancel</button>
</div>
<script>
	jQuery(function() {
		jQuery('#fld-video-save').click(function() {
			jQuery.post(
				ajaxurl + '?action=fld_video_save',
				{
					post_id: <?php the_ID(); ?>
				},
				function(result) {
					location.reload();
				},
				'json'
			);
			return false;
		});
		jQuery('#fld-video-cancel').click(function() {
			jQuery.post(
				ajaxurl + '?action=fld_video_cancel',
				{
					post_id: <?php the_ID(); ?>
				},
				function(result) {
					location.reload();
				},
				'json'
			);
			return false;
		});
	});
</script>
<?php
		}

		private function show_upload_ui($allow_cancel = false) {
?>
<script src="<?php echo plugin_dir_url(__FILE__); ?>/js/tus.min.js"></script>
<script src="<?php echo plugin_dir_url(__FILE__); ?>/js/cloudflare-upload.js"></script>
<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__); ?>upload.css">
<div id="fld-video-upload" data-post-id="<?php the_ID(); ?>">
	<h3 class="label"><button id="btn-fld-video-upload">
		<input type="file" id="fld-video-file" hidden accept="video/*">
		<span>Drag a video or click here to upload.</span>
	</button></h3>
	<div class="progress"></div>
</div>
<br>
<div>
	<button id="fld-video-cancel-upload">Cancel</button>
	<?php
		if ($allow_cancel) {
	?>
	<button id="fld-video-cancel-replace">Cancel</button>
	<?php
		}
	?>
</div>

<?php
		}
	}

	new cloudflareUpload();
