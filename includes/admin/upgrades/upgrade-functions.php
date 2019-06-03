<?php
/**
 * Upgrade Functions
 *
 * @package    Give-iATS
 * @subpackage Admin/Upgrades
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @since      1.0.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Perform automatic database upgrades when necessary.
 *
 * @since  1.0.5
 * 
 * @return void
 */
function give_iats_do_automatic_upgrades() {
	
	$did_upgrade       = false;
	$give_iats_version = preg_replace( '/[^0-9.].*/', '', Give_Cache_Setting::get_option( 'give_iats_version' ) );

	if ( ! $give_iats_version ) {
		// 1.0 is the first version to use this option so we must add it.
		$give_iats_version = '1.0';
	}

	switch ( true ) {

		case version_compare( $give_iats_version, '1.0.5', '<' ):
			give_iats_v105_upgrades();
			$did_upgrade = true;
			break;
	}

	if ( $did_upgrade || version_compare( $give_iats_version, GIVE_IATS_VERSION, '<' ) ) {
		update_option( 'give_iats_version', preg_replace( '/[^0-9.].*/', '', GIVE_IATS_VERSION ), false );
	}
}

add_action( 'admin_init', 'give_iats_do_automatic_upgrades', 0 );
add_action( 'give_upgrades', 'give_iats_do_automatic_upgrades', 0 );

/**
 * Upgrade routine for 1.0.5
 * 
 * @since 1.0.5
 *
 * @return void
 */
function give_iats_v105_upgrades() {

	$legacy_gateway_label = give_get_option( 'iats_payment_method_label', __( 'Credit Card', 'give-iats' ) );
	$gateways_label       = give_get_option( 'gateways_label', array() );

	// Set the legacy payment method label in the new meta key.
	$gateways_label['iats'] = $legacy_gateway_label;

	// Update the new meta key which handle the payment method labels.
	give_update_option( 'gateways_label', $gateways_label );
}