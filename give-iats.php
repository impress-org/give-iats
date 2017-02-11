<?php
/**
 * Plugin Name: Give - iATS Gateway
 * Plugin URI: http://givewp.com
 * Description: iATS payment gateway.
 * Author: WordImpress
 * Author URI: https://wordimpress.com
 * Version: 1.0
 * Text Domain: give-iatspayments
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/WordImpress/Give-iATS
 */


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

		if ( is_admin() ) {
			// Add actions.
			require_once 'includes/admin/actions.php';
		}

		return self::$instance;
	}


	/**
	 * Setup hooks.
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
}

// Initiate plugin.
function give_iats_plugin_init() {
	if ( class_exists( 'Give' ) ) {
		Give_iATS_Gateway::get_instance()
		                 ->load_files();
	}
}

add_action( 'plugins_loaded', 'give_iats_plugin_init' );
