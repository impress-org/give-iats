<?php
/**
 * Plugin Name: Give - iATS Payments
 * Plugin URI: http://givewp.com
 * Description: The most robust, flexible, and intuitive way to accept donations on WordPress with Give plugin by iATS payment gateway.
 * Author: WordImpress
 * Author URI: https://wordimpress.com
 * Version: 1.0
 * Text Domain: give-iatspayments
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/WordImpress/Give-iATS
 *
 * Give - iATS Payments is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Give - iATS Payments is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Give - iATS Payments. If not, see <https://www.gnu.org/licenses/>.
 *
 * A Tribute to Open Source:
 *
 * "Open source software is software that can be freely used, changed, and shared (in modified or unmodified form) by anyone. Open
 * source software is made by many people, and distributed under licenses that comply with the Open Source Definition."
 *
 * -- The Open Source Initiative
 *
 * Give - iATS Payments is a tribute to the spirit and philosophy of Open Source. We at WordImpress gladly embrace the Open Source philosophy both
 * in how Give - iATS Payments itself was developed, and how we hope to see others build more from our code base.
 *
 * Give - iATS Payments would not have been possible without the tireless efforts of WordPress and the surrounding Open Source projects and their talented developers. Thank you all for your contribution to WordPress.
 *
 * - The WordImpress Team
 *
 */


/**
 * Class Give_iATS_Gateway
 */
class Give_iATS_Gateway {
	function __construct() {
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

		// Load scripts and style.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @param $hook
	 */
	function enqueue_scripts( $hook ) {
		if( 'gateways' === give_get_current_setting_tab() && 'iatspayments' === give_get_current_setting_section() ) {
			wp_enqueue_script( 'iats-admin-settings', plugins_url( '/assets/js/admin/admin-settings.js', __FILE__), array('jquery') );
		}
	}
}

new Give_iATS_Gateway();