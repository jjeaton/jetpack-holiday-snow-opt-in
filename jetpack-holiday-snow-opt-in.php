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
 * Version:     0.1.5
 * Author:      Josh Eaton
 * Author URI:  http://www.josheaton.org/
 * Text Domain: jetpack-holiday-snow-opt-in
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
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

	protected $version = '0.1.5';
	protected $plugin_slug = 'jetpack-holiday-snow-opt-in';
	protected static $instance = null;
	protected $plugin_screen_hook_suffix = null;
	protected $plugin_path = null;
	public $snowing;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     0.1.1
	 */
	private function __construct() {

		// Get plugin path.
		$this->plugin_path = dirname( plugin_dir_path( __FILE__ ) );

		// Initialize snow status.
		$this->snowing = false;
		$this->jetpack = false;

		add_action( 'plugins_loaded', array( $this, 'jetpack_active_check' ), 15 );

		// Load the plugin if we're in season.
		add_action( 'init', array( $this, 'run_if_in_season' ), 1 );

		// Check for snow cookies (yum!) or snow control requests.
		add_action( 'init', array( $this, 'process_snow_opt_in' ), 2 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.1.1
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function jetpack_active_check() {
		$this->jetpack = false;

		// Confirm Jetpack is loaded and the holiday-snow.php file is loaded.
		if ( class_exists( 'Jetpack' ) && function_exists( 'jetpack_is_holiday_snow_season' ) ) {
			$this->jetpack = true;
			return $this->jetpack;
		}

		return $this->jetpack;
	}

	public function run_if_in_season() {
		if ( ! $this->jetpack ) {
			return;
		}

		if ( ! jetpack_is_holiday_snow_season() ) {
			return;
		}

		// Add a filter so we can control the snow.
		add_filter( 'jetpack_holiday_chance_of_snow', array( $this, 'should_it_snow' ) );

		// Add our snow control and styles.
		add_action( 'wp_head', array( $this, 'add_snow_css' ) );
		add_action( 'wp_footer', array( $this, 'add_snow_opt_in' ) );
	}

	public function should_it_snow( $snow ) {
		return $this->snowing;
	}

	public function add_snow_css() {
		// Check if the snow option is enabled, if not, bail.
		if ( ! get_option( jetpack_holiday_snow_option_name() ) ) {
			return;
		}

		?>
<style type="text/css">
#jetpack-holiday-snow-opt-in {
	position: absolute;
	z-index: 9999;
	width: 30px;
	height: 30px;
	top: 0;
	right: 10%;
	background-color: #000000;
	background-color: rgba(0, 0, 0, 0.7);
	border-bottom-right-radius: 5px;
	border-bottom-left-radius: 5px;
	cursor: hand;
}
#jetpack-holiday-snow-opt-in * {
	color: #fff;
}
#jetpack-holiday-snow-opt-in a {
	display: block;
	padding: 2px 7px;
	text-decoration: none;
}
body.admin-bar #jetpack-holiday-snow-opt-in {
	top: 28px;
}
</style>
		<?php
	}

	public function add_snow_opt_in() {
		// Check if the snow option is enabled, if not, bail.
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
			echo '<a href="' . esc_url( add_query_arg( 'show_snow', $show_snow ) ) .  '" title="' . esc_attr( $title ) . '"><span>&#xFF0A;</span></a>';
		echo '</div>';
	}

	public function has_snow_cookie() {
		if ( isset( $_COOKIE['show_me_the_snow'] ) // Input var okay.
			&& 1 === absint( $_COOKIE['show_me_the_snow'] ) ) {
			return true;
		}

		return false;
	}

	public function process_snow_opt_in() {
		if ( ! $this->jetpack ) {
			return;
		}

		// Check if the snow option is enabled, if not, bail.
		if ( ! get_option( jetpack_holiday_snow_option_name() ) || ! jetpack_is_holiday_snow_season() ) {
			return;
		}

		// If we want snow and haven't asked it to change.
		if ( $this->has_snow_cookie() && ! isset( $_GET['show_snow'] ) ) { // Input var okay.
			$this->snowing = true;
			return;
		}

		// If we haven't yet asked for snow, and haven't asked it to change.
		if ( ! $this->has_snow_cookie() && ! isset( $_GET['show_snow'] ) ) { // Input var okay.
			$this->snowing = false;
			return;
		}

		// Only continue if we're changing snow states.
		$show_snow = absint( $_GET['show_snow'] ); // Input var okay.

		// If user wants snow, set the cookie saying so
		// If not, remove the cookie.
		if ( 1 === $show_snow ) {
			setcookie( 'show_me_the_snow', '1', time() + 3600 * 24 * 30, COOKIEPATH, COOKIE_DOMAIN, false );
			$this->snowing = true;
		} else {
			setcookie( 'show_me_the_snow', '1', time() - 3600 * 24 * 30, COOKIEPATH, COOKIE_DOMAIN, false );
			$this->snowing = false;
		}
	}
} // end class Jetpack_Holiday_Snow_OptIn

// Get the class instance.
add_action( 'plugins_loaded', array( 'Jetpack_Holiday_Snow_OptIn', 'get_instance' ) );
