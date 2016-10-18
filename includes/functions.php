<?php
/**
 * Check if iATS payment gateway active or not.
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
			$card_name = 'DSC';
			break;

		case 'maestro':
			$card_name = 'MAESTRO';
			break;
	}

	return $card_name;
}