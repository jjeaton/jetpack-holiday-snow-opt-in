<?php
/**
 * Jetpack Holiday Snow Opt-In
 *
 * Make Jetpack's Holiday Snow feature accessible by only showing it if user has opted-in by clicking a snowflake displayed on the page.
 *
 * @package   jetpack-holiday-snow-opt-in
 * @author    Josh Eaton <josh@josheaton.org>
 * @license   GPL-2.0+
 * @link      http://www.josheaton.org/
 * @copyright 2013 Josh Eaton
 *
 * @wordpress-plugin
 * Plugin Name: Jetpack Holiday Snow Opt-In
 * Plugin URI:  http://www.josheaton.org/
 * Description: Make Jetpack's Holiday Snow feature accessible by only showing it if user has opted-in by clicking a snowflake displayed on the page.
 * Version:     0.1.0
 * Author:      Josh Eaton
 * Author URI:  http://www.josheaton.org/
 * Text Domain: jetpack-holiday-snow-opt-in
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Jetpack_Holiday_Snow_OptIn class
 *
 * @package Jetpack_Holiday_Snow_OptIn
 * @author  Josh Eaton <josh@josheaton.org>
 */
class Jetpack_Holiday_Snow_OptIn {

	protected $version = '0.1.0';
	protected $plugin_slug = 'jetpack-holiday-snow-opt-in';
	protected static $instance = null;
	protected $plugin_screen_hook_suffix = null;
	protected $plugin_path = null;
	public $snowing;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     0.1.0
	 */
	private function __construct() {

		// Get plugin path
		$this->plugin_path = dirname( plugin_dir_path( __FILE__ ) );

		// Initialize snow status
		$this->snowing = false;

		add_action( 'admin_notices', array( $this, 'jetpack_active_check' ), 10 );

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Add a filter so we can control the snow
		add_filter( 'jetpack_holiday_chance_of_snow', array( $this, 'should_it_snow' ) );

		// Check for snow cookies (yum!) or snow control requests
		add_action( 'init',      array( $this, 'process_snow_opt_in' ), 1 );

		// Add our snow control and styles
		add_action( 'wp_head',   array( $this, 'add_snow_css'    ) );
		add_action( 'wp_footer', array( $this, 'add_snow_opt_in' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.1.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.1.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	public function jetpack_active_check() {

		$screen = get_current_screen();

		if ( $screen->parent_file !== 'plugins.php' )
			return;

		// check for active and bail if true
		if ( is_plugin_active( 'jetpack/jetpack.php' ) )
			return;

		// not active. show message
		echo '<div id="message" class="error fade below-h2"><p><strong>'.__( 'Jetpack Holiday Snow Opt-In requires Jetpack to function.', 'jetpack-holiday-snow-opt-in' ).'</strong></p></div>';

		// hide activation method
		unset( $_GET['activate'] );

		// deactivate YOURSELF
		deactivate_plugins( plugin_basename( __FILE__ ) );

	}

	public function should_it_snow( $snow ) {

		return $this->snowing;

	}

	public function add_snow_css() {

		// check if the snow option is enabled, if not, bail
		if ( ! get_option( jetpack_holiday_snow_option_name() ) ) {
			return;
		}

		?>
<style type="text/css">
@-webkit-keyframes spin {
	from { -webkit-transform: rotate(0deg);   }
	to   { -webkit-transform: rotate(360deg); }
}
@keyframes spin {
	from { transform: rotate(0deg);   }
	to   { transform: rotate(360deg); }
}
#jetpack-holiday-snow-opt-in {
	position: absolute;
	width: 30px;
	height: 30px;
	top: 0;
	right: 10%;
	background-color: #000000;
	background-color: rgba(0, 0, 0, 0.7);
	border-bottom-right-radius: 5px;
	border-bottom-left-radius: 5px;
}
#jetpack-holiday-snow-opt-in * {
	color: #fff;
}
#jetpack-holiday-snow-opt-in a {
	display: block;
	padding: 2px 7px;
	text-decoration: none;
}
#jetpack-holiday-snow-opt-in a:hover span {
	display: block;
	-webkit-animation: spin 6s linear infinite;
	animation:         spin 6s linear infinite;
	color: #fff;
}
body.admin-bar #jetpack-holiday-snow-opt-in {
	top: 28px;
}
</style>
		<?php
	}

	public function add_snow_opt_in() {

		// check if the snow option is enabled, if not, bail
		if ( ! get_option( jetpack_holiday_snow_option_name() ) ) {
			return;
		}

		// Change our link depending on the weather.
		if ( $this->snowing ) {
			$show_snow = '0';
			$title = __( 'Click to stop the snow', 'jetpack-holiday-snow-opt-in' );
		} else {
			$show_snow = '1';
			$title = __( 'Click here to make it snow!', 'jetpack-holiday-snow-opt-in' );
		}

		echo '<div id="jetpack-holiday-snow-opt-in">';
			echo '<a href="' . add_query_arg( 'show_snow', $show_snow ) .  '" title="' . esc_attr( $title ) . '"><span>&#xFF0A;</span></a>';
		echo '</div>';
	}

	public function has_snow_cookie() {
		if ( isset( $_COOKIE[ 'show_me_the_snow' ] ) && '1' == $_COOKIE[ 'show_me_the_snow' ] ) {
			return true;
		}

		return false;
	}

	public function process_snow_opt_in() {

		// check if the snow option is enabled, if not, bail
		if ( ! get_option( jetpack_holiday_snow_option_name() ) ) {
			return;
		}

		// If we want snow and haven't asked it to change
		if ( $this->has_snow_cookie() && ! isset($_GET['show_snow']) ) {
			$this->snowing = true;
			return;
		}

		// If we haven't yet asked for snow, and haven't asked it to change
		if ( !$this->has_snow_cookie() && ! isset($_GET['show_snow']) ) {
			$this->snowing = false;
			return;
		}

		// Only continue if we're changing snow states.

		$show_snow =  $_GET['show_snow'];

		// If user wants snow, set the cookie saying so
		// If not, remove the cookie
		if ( $show_snow == '1' ) {
			setcookie( 'show_me_the_snow', '1', time()+3600*24*30, COOKIEPATH, COOKIE_DOMAIN, false);
			$this->snowing = true;
		} else {
			setcookie( 'show_me_the_snow', '1', time()-3600*24*30, COOKIEPATH, COOKIE_DOMAIN, false);
			$this->snowing = false;
		}
	}

} // end class Jetpack_Holiday_Snow_OptIn

// Get the class instance
add_action( 'plugins_loaded', array( 'Jetpack_Holiday_Snow_OptIn', 'get_instance' ) );
