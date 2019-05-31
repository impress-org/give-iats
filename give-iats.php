<?php
/**
 * Plugin Name: Give - iATS Payment Solutions
 * Plugin URI: https://givewp.com/addons/iats-gateway/
 * Description: Process online donations via the iATS payment gateway.
 * Author: GiveWP
 * Author URI: https://givewp.com
 * Version: 1.0.5
 * Text Domain: give-iats
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/impress-org/give-iats
 */

// iATS Constants.
if ( ! defined( 'GIVE_IATS_VERSION' ) ) {
	define( 'GIVE_IATS_VERSION', '1.0.5' );
}
if ( ! defined( 'GIVE_IATS_MIN_GIVE_VERSION' ) ) {
	define( 'GIVE_IATS_MIN_GIVE_VERSION', '2.3.0' );
}
if ( ! defined( 'GIVE_IATS_PLUGIN_FILE' ) ) {
	define( 'GIVE_IATS_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'GIVE_IATS_PLUGIN_DIR' ) ) {
	define( 'GIVE_IATS_PLUGIN_DIR', dirname( GIVE_IATS_PLUGIN_FILE ) );
}
if ( ! defined( 'GIVE_IATS_PLUGIN_URL' ) ) {
	define( 'GIVE_IATS_PLUGIN_URL', plugin_dir_url( GIVE_IATS_PLUGIN_FILE ) );
}
if ( ! defined( 'GIVE_IATS_BASENAME' ) ) {
	define( 'GIVE_IATS_BASENAME', plugin_basename( GIVE_IATS_PLUGIN_FILE ) );
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
	 * Notices (array)
	 *
	 * @since 1.0.4
	 *
	 * @var array
	 */
	public $notices = array();

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
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Setup Give iATS Gateway.
	 *
	 * @since  1.0.4
	 * @access private
	 */
	private function setup() {

		// Give init hook.
		add_action( 'give_init', array( $this, 'init' ), 10 );
		add_action( 'admin_init', array( $this, 'check_environment' ), 999 );
		add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
	}

	/**
	 * Load files.
	 *
	 * @since  1.0
	 * @access public
	 * @return Give_iATS_Gateway
	 */
	public function init() {

		if ( ! $this->get_environment_warning() ) {
			return;
		}

		$this->licensing();
		$this->activation_banner();

		if ( is_admin() ) {
			// Add actions.
			require_once GIVE_IATS_PLUGIN_DIR . '/includes/admin/plugins-list.php';
			require_once GIVE_IATS_PLUGIN_DIR . '/includes/admin/admin-actions.php';
		}

		// iATS payment gateways core.
		require_once GIVE_IATS_PLUGIN_DIR . '/includes/lib/iATSPayments/iATS.php';

		// Credit card validator core.
		require_once GIVE_IATS_PLUGIN_DIR . '/includes/lib/php-credit-card-validator/src/CreditCard.php';

		// Load helper functions.
		require_once GIVE_IATS_PLUGIN_DIR . '/includes/functions.php';

		// Add error notice if any.
		require_once GIVE_IATS_PLUGIN_DIR . '/includes/class-give-iats-notices.php';

		// Load plugin settings.
		require_once GIVE_IATS_PLUGIN_DIR . '/includes/admin/class-give-iats-gateways-settings.php';

		// Process payments.
		require_once GIVE_IATS_PLUGIN_DIR . '/includes/payment-processing.php';

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
			wp_enqueue_script( 'iats-admin-settings', plugins_url( '/assets/js/admin/admin-settings.js', GIVE_IATS_PLUGIN_FILE ), array( 'jquery' ) );
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
		$give_iats_lang_dir = dirname( plugin_basename( GIVE_IATS_PLUGIN_FILE ) ) . '/languages/';
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

	/**
	 * Check plugin environment.
	 *
	 * @since  1.0.4
	 * @access public
	 *
	 * @return bool
	 */
	public function check_environment() {
		// Flag to check whether plugin file is loaded or not.
		$is_working = true;

		// Load plugin helper functions.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		/* Check to see if Give is activated, if it isn't deactivate and show a banner. */
		// Check for if give plugin activate or not.
		$is_give_active = defined( 'GIVE_PLUGIN_BASENAME' ) ? is_plugin_active( GIVE_PLUGIN_BASENAME ) : false;

		if ( empty( $is_give_active ) ) {
			// Show admin notice.
			$this->add_admin_notice( 'prompt_give_activate', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> plugin installed and activated for Give - iATS to activate.', 'give-iats' ), 'https://givewp.com' ) );
			$is_working = false;
		}

		return $is_working;
	}

	/**
	 * Check plugin for Give environment.
	 *
	 * @since  1.0.4
	 * @access public
	 *
	 * @return bool
	 */
	public function get_environment_warning() {
		// Flag to check whether plugin file is loaded or not.
		$is_working = true;

		// Verify dependency cases.
		if (
			defined( 'GIVE_VERSION' )
			&& version_compare( GIVE_VERSION, GIVE_IATS_MIN_GIVE_VERSION, '<' )
		) {

			/* Min. Give. plugin version. */
			// Show admin notice.
			$this->add_admin_notice( 'prompt_give_incompatible', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> core version %s for the Give - iATS add-on to activate.', 'give-iats' ), 'https://givewp.com', GIVE_IATS_MIN_GIVE_VERSION ) );

			$is_working = false;
		}

		return $is_working;
	}

	/**
	 * Allow this class and other classes to add notices.
	 *
	 * @since 1.0.4
	 *
	 * @param $slug
	 * @param $class
	 * @param $message
	 */
	public function add_admin_notice( $slug, $class, $message ) {
		$this->notices[ $slug ] = array(
			'class'   => $class,
			'message' => $message,
		);
	}

	/**
	 * Display admin notices.
	 *
	 * @since 1.0.4
	 */
	public function admin_notices() {

		$allowed_tags = array(
			'a'      => array(
				'href'  => array(),
				'title' => array(),
				'class' => array(),
				'id'    => array(),
			),
			'br'     => array(),
			'em'     => array(),
			'span'   => array(
				'class' => array(),
			),
			'strong' => array(),
		);

		foreach ( (array) $this->notices as $notice_key => $notice ) {
			echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
			echo wp_kses( $notice['message'], $allowed_tags );
			echo '</p></div>';
		}

	}

	/**
	 * Implement Give Licensing for Give iATS Gateway Add On.
	 *
	 * @since  1.0.4
	 * @access private
	 */
	private function licensing() {
		if ( class_exists( 'Give_License' ) ) {
			new Give_License( GIVE_IATS_PLUGIN_FILE, 'iATS Payment Solutions', GIVE_IATS_VERSION, 'WordImpress' );
		}
	}

	/**
	 * Give iATS Activation Banner
	 *
	 * Includes and initializes Give activation banner class.
	 *
	 * @since 1.0.4
	 */
	function activation_banner() {

		// Check for activation banner inclusion.
		if ( ! class_exists( 'Give_Addon_Activation_Banner' )
		     && file_exists( GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php' )
		) {

			include GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php';
		}

		// Initialize activation welcome banner.
		if ( class_exists( 'Give_Addon_Activation_Banner' ) ) {

			// Only runs on admin
			$args = array(
				'file'              => GIVE_IATS_PLUGIN_FILE,
				'name'              => __( 'iATS Gateway', 'give-iats' ),
				'version'           => GIVE_IATS_VERSION,
				'settings_url'      => admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=iats-payments' ),
				'documentation_url' => 'http://docs.givewp.com/addon-iats',
				'support_url'       => 'https://givewp.com/support/',
				'testing'           => false, //Never leave as true!
			);

			new Give_Addon_Activation_Banner( $args );

		}

	}
}

/**
 * Class instance of Give_iATS_Gateway
 *
 * @since 1.0.4
 *
 * @return Give_iATS_Gateway
 */
function Give_iATS_Gateway() {
	return Give_iATS_Gateway::get_instance();
}

Give_iATS_Gateway();