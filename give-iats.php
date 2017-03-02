<?php
/**
 * Plugin Name: Give - iATS Gateway
 * Plugin URI: http://givewp.com
 * Description: Process online donations via the iATS payment gateway.
 * Author: WordImpress
 * Author URI: https://wordimpress.com
 * Version: 1.0
 * Text Domain: give-iatspayments
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/WordImpress/Give-iATS
 */

// iATS Constants.
if ( ! defined( 'GIVE_IATS_VERSION' ) ) {
	define( 'GIVE_IATS_VERSION', '1.0' );
}
if ( ! defined( 'GIVE_IATS_MIN_GIVE_VERSION' ) ) {
	define( 'GIVE_IATS_MIN_GIVE_VERSION', '1.8.4' );
}
if ( ! defined( 'GIVE_IATS_PLUGIN_FILE' ) ) {
	define( 'GIVE_IATS_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'GIVE_IATS_PLUGIN_DIR' ) ) {
	define( 'GIVE_IATS_PLUGIN_DIR', dirname( __FILE__ ) );
}
if ( ! defined( 'GIVE_IATS_PLUGIN_URL' ) ) {
	define( 'GIVE_IATS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'GIVE_IATS_BASENAME' ) ) {
	define( 'GIVE_IATS_BASENAME', plugin_basename( __FILE__ ) );
}

/**
 * Class Give_iATS_Gateway
 */
final class Give_iATS_Gateway {

	/**
	 * Instance.
	 *
	 * @since  1.0
	 * @access static
	 * @var Give_iATS_Gateway $instance
	 */
	static private $instance;


	/**
	 * Singleton pattern.
	 *
	 * Give_iATS_Gateway constructor.
	 */
	private function __construct() {
		// Run at a later priority than creating the instance.
		add_action( 'init', array( $this, 'load_textdomain' ), 11 );
	}


	/**
	 * Get instance.
	 *
	 * @since  1.0
	 * @access static
	 * @return Give_iATS_Gateway
	 */
	static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}


	/**
	 * Load files.
	 *
	 * @since  1.0
	 * @access public
	 * @return Give_iATS_Gateway
	 */
	public function load_files() {

		if ( is_admin() ) {
			// Add actions.
			require_once 'includes/admin/give-iats-activation.php';
			require_once 'includes/admin/give-iats-actions.php';
		}

		// iATS payment gateways core.
		require_once 'includes/lib/iATSPayments/iATS.php';

		// Credit card validator core.
		require_once 'includes/lib/php-credit-card-validator/src/CreditCard.php';

		// Load helper functions.
		require_once 'includes/functions.php';

		// Add error notice if any.
		require_once 'includes/class-give-iats-notices.php';

		// Load plugin settings.
		require_once 'includes/admin/class-give-iats-gateways-settings.php';

		// Process payments.
		require_once 'includes/give-iats-payment-processing.php';

		return self::$instance;
	}


	/**
	 * Setup hooks. CURRENTLY UNUSED.
	 *
	 * @since  1.0
	 * @access public
	 * @return Give_iATS_Gateway
	 */
	function setup_hooks() {

		// Admin only scripts.
		if ( ! is_admin() ) {
			return self::$instance;
		}
		// Load scripts and style.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		return self::$instance;
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param $hook
	 */
	public function enqueue_scripts( $hook ) {
		if ( isset( $_GET['tab'] ) && 'gateways' === $_GET['tab'] ) {
			wp_enqueue_script( 'iats-admin-settings', plugins_url( '/assets/js/admin/admin-settings.js', __FILE__ ), array( 'jquery' ) );
		}
	}

	/**
	 * Load the text domain.
	 *
	 * @access private
	 * @since  1.0
	 *
	 * @return void
	 */
	public function load_textdomain() {

		// Set filter for plugin's languages directory.
		$give_iats_lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$give_iats_lang_dir = apply_filters( 'give_iats_languages_directory', $give_iats_lang_dir );

		// Traditional WordPress plugin locale filter.
		$locale = apply_filters( 'plugin_locale', get_locale(), 'give-iats' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'give-iats', $locale );

		// Setup paths to current locale file.
		$mofile_local  = $give_iats_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/give-iats/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/give-iats folder.
			load_textdomain( 'give-iats', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/give-iats/languages/ folder.
			load_textdomain( 'give-iats', $mofile_local );
		} else {
			// Load the default language files.
			load_plugin_textdomain( 'give-iats', false, $give_iats_lang_dir );
		}

	}
}

/**
 * Initialize the plugin
 */
function give_iats_plugin_init() {
	// We need Give to continue.
	if ( give_iats_check_environment() ) {
		Give_iATS_Gateway::get_instance()->load_files();
	}
}

add_action( 'init', 'give_iats_plugin_init' );


/**
 * Check the environment before starting up.
 *
 * @since 1.0
 *
 * @return bool
 */
function give_iats_check_environment() {

	// Check for if give plugin activate or not.
	$is_give_active = defined( 'GIVE_PLUGIN_BASENAME' ) ? true : false;

	// Check to see if Give is activated, if it isn't deactivate and show a banner
	if ( current_user_can( 'activate_plugins' ) && ! $is_give_active ) {
		add_action( 'admin_notices', 'give_iats_activation_notice' );
		add_action( 'admin_init', 'give_iats_deactivate_self' );
		return false;
	}

	// Check minimum Give version.
	if ( defined( 'GIVE_VERSION' ) && version_compare( GIVE_VERSION, GIVE_IATS_MIN_GIVE_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'give_iats_min_version_notice' );
		add_action( 'admin_init', 'give_iats_deactivate_self' );
		return false;
	}

	return true;

}

/**
 * Deactivate self. Must be hooked with admin_init.
 *
 * Currently hooked via give_iats_check_environment()
 */
function give_iats_deactivate_self() {
	deactivate_plugins( GIVE_IATS_BASENAME );
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}

/**
 * Notice for no Give core deactivated.
 *
 * @since 1.0
 */
function give_iats_activation_notice() {
	echo '<div class="error"><p>' . __( '<strong>Activation Error:</strong> You must have the <a href="https://givewp.com/" target="_blank">Give</a> plugin installed and activated for the iATS add-on to activate.', 'give-iats' ) . '</p></div>';
}

/**
 * Notice for min-version not met.
 *
 * @since 1.0
 */
function give_iats_min_version_notice() {
	echo '<div class="error"><p>' . sprintf( __( '<strong>Activation Error:</strong> You must have <a href="%1$s" target="_blank">Give</a> version %2$s+ for the iATS add-on to activate.', 'give-iats' ), 'https://givewp.com', GIVE_IATS_MIN_GIVE_VERSION ) . '</p></div>';
}
