<?php
/**
 * Check if iATS payment gateway active or not.
 *
 * @since 1.0
 * @return bool
 */
function give_is_iats_active() {
	$give_settings = give_get_settings();
	$is_active     = false;

	if (
		array_key_exists( 'iatspayments', $give_settings['gateways'] )
		&& ( 1 == $give_settings['gateways']['iatspayments'] )
	) {
		$is_active = true;
	}

	return $is_active;
}


/**
 * Get card name by card type
 *
 * Note: Only Limit credit card type supported by iATS payment gateway.
 *
 * @since 1.0
 *
 * @param string $card_type Credit card type.
 *
 * @return string
 */
function give_iats_get_card_name_by_type( $card_type ) {
	$card_name = '';

	switch ( $card_type ) {
		case 'visa':
		case 'visaelectron':
			$card_name = 'VISA';
			break;

		case 'mastercard':
			$card_name = 'MC';
			break;

		case 'amex':
			$card_name = 'AMX';
			break;

		case 'discover':
			// DSC only supported for USD currency.
			if ( 'USD' === give_get_currency() ) {
				$card_name = 'DSC';
			}

			break;

		case 'maestro':
			// MAESTRO only supported for GBP currency.
			if ( 'GBP' === give_get_currency() ) {
				$card_name = 'MAESTRO';
			}
			break;
	}

	return $card_name;
}


/**
 * Get iATS paymetn gateways server.
 *
 * @return string $server Server name.
 */
function give_iats_get_server_name() {
	$currency = give_get_currency();
	$server   = '';

	if ( in_array( $currency, array( 'USD', 'CAD' ) ) ) {
		$server = 'NA';
	} elseif ( in_array( $currency, array( 'GBP', 'EUR' ) ) ) {
		$server = 'UK';
	}

	return $server;
}


/**
 * Get payment method label.
 *
 * @return string
 */
function give_iats_get_payment_method_label() {
	$give_settings = give_get_settings();

	return ( empty( $give_settings['iats_payment_method_label'] ) ? __( 'Credit Card', 'give-iatspayments' ) : $give_settings['iats_payment_method_label'] );
}


/**
 * Check if sandbox mode is enabled or disabled.
 *
 * @return bool
 */
function give_iats_is_sandbox_mode_enabled() {
	$give_settings = give_get_settings();

	return give_is_setting_enabled( $give_settings['iats_sandbox_testing'] );
}


/**
 * Get iats agent credentials.
 *
 * @return array
 */
function give_iats_get_agent_credentials() {
	$give_settings = give_get_settings();
	$credentials   = array(
		'code'     => $give_settings['iats_sandbox_agent_code'],
		'password' => $give_settings['iats_sandbox_agent_password'],
	);

	if ( ! give_iats_is_sandbox_mode_enabled() ) {
		$credentials = array(
			'code'     => $give_settings['iats_live_agent_code'],
			'password' => $give_settings['iats_live_agent_password'],
		);
	}

	return $credentials;

}