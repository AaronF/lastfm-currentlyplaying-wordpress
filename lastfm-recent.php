<?php
/**
 * Plugin Name:			Last.fm recent
 * Plugin URI:			https://github.com/AaronF/lastfm-currentlyplaying-wordpress
 * Description:			Display your currently playing last.fm item via a shortcode
 * Version:				1.0.0
 * Requires at least:	5.0
 * Requires PHP:		7.0
 * Author:				Aaron Fisher
 * Author URI:			https://aaronfisher.net
 * Text Domain:			last-fm-recent
 * Domain Path:			/languages
 */


if(!class_exists("last_fm_recent")){
	class lastFmRecent {
		static $instance = false;
		private $base_api_url = "http://ws.audioscrobbler.com/2.0/";

		private function __construct(){
			add_action('admin_init',	array($this, 'register_settings'));
			add_action('init',			array($this, 'register_shortcodes'));

			//Register front end ajax endpoints
			add_action('wp_ajax_lastfm_currently_playing',			array($this, 'lfm_currently_playing_ajax'));
			add_action('wp_ajax_nopriv_lastfm_currently_playing',	array($this, 'lfm_currently_playing_ajax'));
		}

		public static function getInstance() {
			if (!self::$instance)
				self::$instance = new self;
			return self::$instance;
		}

		/**
		 * Register admin settings
		 */
		public function register_settings(){
			add_settings_section(
				'lfmrcnt_settings_section',
				'Last.fm Settings',
				array($this, 'lfmrcnt_settings_section_cb'),
				'general'
			);

			register_setting('general', 'lfmrcnt_settings_apikey');
			add_settings_field(
				'lfmrcnt_settings_apikey',
				'Last.fm API Key',
				array($this, 'lfmrcnt_settings_apikey_cb'),
				'general',
				'lfmrcnt_settings_section'
			);

			register_setting('general', 'lfmrcnt_settings_profilename');
			add_settings_field(
				'lfmrcnt_settings_profilename',
				'Last.fm Profile Name',
				array($this, 'lfmrcnt_settings_profilename_cb'),
				'general',
				'lfmrcnt_settings_section'
			);
		}

		public function lfmrcnt_settings_section_cb(){
			//echo '<p>WPOrg Section Introduction.</p>';
		}
		
		//Setting - Last.fm API key
		public function lfmrcnt_settings_apikey_cb(){
			$setting = get_option('lfmrcnt_settings_apikey');
			?>
			<input type="text" name="lfmrcnt_settings_apikey" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
			<?php
		}

		//Setting - Last.fm profile name
		public function lfmrcnt_settings_profilename_cb(){
			$setting = get_option('lfmrcnt_settings_profilename');
			?>
			<input type="text" name="lfmrcnt_settings_profilename" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
			<?php
		}

		/**
		 * Register front-end shortcodes
		 */
		public function register_shortcodes(){
			add_shortcode("lfm_currently_playing", array($this, "lfm_currently_playing_shortcode"));
		}

		//Shortcode - Display currently playing
		public function lfm_currently_playing_shortcode(){
			wp_enqueue_script('lastfm-recent-js',
				plugins_url('/assets/js/lastfm-recent.js', __FILE__),
				array('jquery')
			);
			wp_localize_script('lastfm-recent-js', 'lastfm_recent', array(
				'ajax_url' => admin_url('admin-ajax.php'),
			));
			echo "<span class='lfm_currently_playing'>Loading...</span>";
		}



		private function lfm_api_getrecenttracks(){
			$api_key		= get_option('lfmrcnt_settings_apikey');
			$profile_name	= get_option('lfmrcnt_settings_profilename');
			if($api_key && $profile_name){
				$body_string = "?method=user.getrecenttracks&user={$profile_name}&api_key={$api_key}&format=json";

				$api_body = wp_remote_retrieve_body(wp_remote_get($this->base_api_url.$body_string));

				return $api_body;
			}
		}

		public function lfm_currently_playing_ajax(){
			$recent_tracks = json_decode($this->lfm_api_getrecenttracks(), true);

			if(!empty($recent_tracks["recenttracks"]["track"]) && count($recent_tracks["recenttracks"]["track"]) > 0){
				$last_track = $recent_tracks["recenttracks"]["track"][0];
				if(!empty($last_track)){
					$response = array(
						"name"			=> $last_track["name"],
						"artist"		=> $last_track["artist"]["#text"],
						"elapsed_time"	=> (isset($last_track["date"]["uts"]) ? $this->get_elapsed_time((int)$last_track["date"]["uts"]) : null)
					);

					echo json_encode($response);
				}
			}

			die();
		}

		/**
		 * Credit: https://stackoverflow.com/a/9619947
		 */
		private function get_elapsed_time($time){
			if(!is_numeric($time)){
				$time = strtotime($time);
			}

			$periods = array("second", "minute", "hour", "day", "week", "month", "year", "age");
			$lengths = array("60","60","24","7","4.35","12","100");

			$now = time();

			$difference = $now - $time;
			if ($difference <= 10 && $difference >= 0){
				return $tense = 'just now';
			} elseif($difference > 0) {
				$tense = 'ago';
			} elseif($difference < 0) {
				$tense = 'later';
			}

			for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
				$difference /= $lengths[$j];
			}

			$difference = round($difference);

			$period = $periods[$j] . ($difference >1 ? 's' :'');
			return "{$difference} {$period} {$tense} ";
		}
	}

	lastFmRecent::getInstance();
}
?>