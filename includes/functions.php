<?php
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
 * Get iATS payment gateways server.
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

	return ( empty( $give_settings['iats_payment_method_label'] ) ? __( 'Credit Card', 'give-iats' ) : $give_settings['iats_payment_method_label'] );
}


/**
 * Get iats agent credentials.
 *
 * @return array
 */
function give_iats_get_agent_credentials() {

	$give_settings = give_get_settings();

	// Test credentials.
	$credentials = array(
		'code'     => $give_settings['iats_sandbox_agent_code'],
		'password' => $give_settings['iats_sandbox_agent_password'],
	);

	// Live credentials.
	if ( ! give_is_test_mode() ) {
		$credentials = array(
			'code'     => $give_settings['iats_live_agent_code'],
			'password' => $give_settings['iats_live_agent_password'],
		);
	}

	return $credentials;

}


/**
 * Format iATS expiration date.
 * 
 * @param $donation_data
 *
 * @return string
 */
function give_iats_format_expiration_date( $donation_data ) {

	$month = isset( $donation_data['card_info']['card_exp_month'] ) ? $donation_data['card_info']['card_exp_month'] : '';
	$year  = isset( $donation_data['card_info']['card_exp_year'] ) ? substr( $donation_data['card_info']['card_exp_year'], - 2 ) : '';

	if ( empty( $month ) || empty( $year ) ) {
		return false;
	}

	// No single digit months.
	if ( 1 === strlen( $month ) ) {
		$month = '0' . $month;
	}


	return trim( $month . '/' . $year );

}


/**
 * Do not print cc field in donation form.
 *
 * Note: We do not need credit card field in donation form but we need billing detail fields.
 *
 * @since 1.0
 *
 * @param $form_id
 *
 * @return bool
 */
function give_iats_cc_form_callback( $form_id ) {

	//Remove Address Fields if user has option enabled.
	if ( ! give_is_setting_enabled(give_get_option( 'iats_billing_details' ) )) {
		remove_action( 'give_after_cc_fields', 'give_default_cc_address_fields' );
	}

	//Ensure CC field is in place properly.
	do_action( 'give_cc_form', $form_id );
}

add_action( 'give_iats_cc_form', 'give_iats_cc_form_callback' );

/**
 * Override number of decimals settings.
 *
 * @since 1.0.2
 *
 * @param integer $number_decimals Number of decimals.
 *
 * @return int
 */
function give_iats_scripts_vars( $number_decimals ) {

	if ( give_is_gateway_active( 'iats' ) ) {
		$number_decimals = 2;
	}

	return apply_filters( 'give_iats_number_decimals', $number_decimals );
}

add_filter( 'give_sanitize_amount_decimals', 'give_iats_scripts_vars', 20, 1 );


/**
 * Override number of decimals from the Form HTML tags.
 *
 * @since 1.0.2
 *
 * @param array  $form_html_tags
 * @param object $form
 *
 * @return mixed
 */
function give_form_add_iats_settings( $form_html_tags, $form ) {

	// Set Number of decimal 2 forcefully if iATS gateway enabled.
	if ( give_is_gateway_active( 'iats' ) ) {
		$form_html_tags['data-number_decimals'] = 2;
	}

	return apply_filters( 'give_iats_form_html_tags', $form_html_tags );
}

add_filter( 'give_form_html_tags', 'give_form_add_iats_settings', 0, 2 );