<?php
/**
 * Progress donation by iATS payment gateway
 *
 * @since 1.0
 *
 * @param $donation_data
 */
function give_process_iats_payment( $donation_data ) {
	if ( ! wp_verify_nonce( $donation_data['gateway_nonce'], 'give-gateway' ) ) {
		wp_die( esc_html__( 'Nonce verification has failed.', 'give-iatspayments' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	// Get card info.
	$card = Inacho\CreditCard::validCreditCard( $donation_data['post_data']['card_number'] );

	// Get agent credentials.
	$agent_credential = give_iats_get_agent_credentials();
	$agentCode        = $agent_credential['code'];            // Assigned by iATS
	$password         = $agent_credential['password'];        // Assigned by iATS

	// Process link.
	$iATS_PL = new iATS\ProcessLink( $agentCode, $password, give_iats_get_server_name() );

	$request = array(
		'creditCardNum'    => $donation_data['post_data']['card_number'],
		'creditCardExpiry' => $donation_data['post_data']['card_expiry'],
		'cvv2'             => $donation_data['post_data']['card_cvc'],
		'firstName'        => $donation_data['post_data']['give_first'],
		'lastName'         => $donation_data['post_data']['give_last'],
		'address'          => $donation_data['post_data']['card_address'],
		'address2'         => $donation_data['post_data']['card_address_2'], // Custom data.
		'city'             => $donation_data['post_data']['card_city'],
		'state'            => $donation_data['post_data']['card_state'],
		'country'          => $donation_data['post_data']['billing_country'],
		'zipCode'          => $donation_data['post_data']['card_zip'],
		'total'            => $donation_data['post_data']['give-amount'],
		'comment'          => 'givewp',
		'currency'         => give_get_currency(),
		'mop'              => give_iats_get_card_name_by_type( $card['type'] ),
	);

	// Make the API call using the ProcessLink service.
	$response = $iATS_PL->processCreditCard( $request );

	// Verify successful call
	if ( 'OK' != substr( trim( $response['AUTHORIZATIONRESULT'] ), 0, 2 ) ) {
		give_record_gateway_error( __( ' Error', 'give-iatspayments' ), sprintf( __( 'There was an error processing your payment (iATS\'s API was down). Error: %s', 'give-iatspayments' ), json_encode( $response ) ), 0 );

		// Redirect to donation form.
		give_send_back_to_checkout( array(
			'payment-mode'      => $donation_data['post_data']['give-gateway'],
			'give-iats-message' => $response['code'],
		) );
	}

	$form_id  = intval( $donation_data['post_data']['give-form-id'] );
	$price_id = isset( $donation_data['post_data']['give-price-id'] ) ? $donation_data['post_data']['give-price-id'] : '';

	// Collect payment data.
	$donation_payment_data = array(
		'price'           => $donation_data['price'],
		'give_form_title' => $donation_data['post_data']['give-form-title'],
		'give_form_id'    => $form_id,
		'give_price_id'   => $price_id,
		'date'            => $donation_data['date'],
		'user_email'      => $donation_data['user_email'],
		'purchase_key'    => $donation_data['purchase_key'],
		'currency'        => give_get_currency(),
		'user_info'       => $donation_data['user_info'],
		'status'          => 'pending',
		'gateway'         => 'iatspayments',
	);

	// Record the pending payment.
	$payment = give_insert_payment( $donation_payment_data );

	// Verify donation payment.
	if ( ! $payment ) {
		// Record the error.
		give_record_gateway_error(
			esc_html__( 'Payment Error', 'give' ),
			/* translators: %s: payment data */
			sprintf(
				esc_html__( 'Payment creation failed before process iATS gateway. Payment data: %s', 'give' ),
				json_encode( $donation_payment_data )
			),
			$payment
		);

		// Problems? Send back.
		give_send_back_to_checkout( '?payment-mode=' . $donation_data['post_data']['give-gateway'] );
	}

	// Update payment status.
	give_update_payment_status( $payment, 'publish' );

	// Update transaction id.
	give_set_payment_transaction_id( $payment, $response['TRANSACTIONID'] );

	// Add iats payment response meta.
	update_post_meta( $payment, 'iats_donation_response', $response );

	// Send to success page.
	give_send_to_success_page();
}

add_action( 'give_gateway_iatspayments', 'give_process_iats_payment' );

/**
 * Validate donation data for iATS payment gateways.
 *
 * @since 1.0
 *
 * @param $donation_data
 */
function give_iats_varify_donation_data( $donation_data ) {
	// Bailout: Validation only for iats payment gateway.
	if ( 'iatspayments' !== $donation_data['gateway'] ) {
		return;
	}

	// Validate credit card.
	$card      = Inacho\CreditCard::validCreditCard( $donation_data['cc_info']['card_number'] );
	$card_name = give_iats_get_card_name_by_type( $card['type'] );

	if ( empty( $card_name ) ) {
		give_set_error( 'give-credit-card-type', __( 'This card is not supported. Please use another card for donation.', 'give-iatspayments' ) );
	}
}

add_action( 'give_checkout_error_checks', 'give_iats_varify_donation_data', 99999 );


/**
 * Process refund.
 *
 * @since 1.0
 *
 * @param bool   $do_change
 * @param int    $donation_id
 * @param string $new_status
 * @param string $old_status
 *
 * @return bool
 */
function give_iats_donation_refund( $do_change, $donation_id, $new_status, $old_status ) {
	$donation = new Give_Payment( $donation_id );

	// Bailout.
	if ( 'refunded' !== $new_status || 'iatspayments' !== $donation->gateway || empty( $_POST['give_refund_in_iats'] ) ) {
		return $do_change;
	}

	// Get agent credentials.
	$agent_credential = give_iats_get_agent_credentials();
	$agentCode        = $agent_credential['code'];            // Assigned by iATS
	$password         = $agent_credential['password'];        // Assigned by iATS

	// Process link.
	$iATS_PL = new iATS\ProcessLink( $agentCode, $password, give_iats_get_server_name() );

	$request = array(
		'transactionId' => give_get_payment_transaction_id( $donation->ID ),
		'total'         => - $donation->total,
		'comment'       => sprintf( __( "Refund for donation %d", 'give-iatspayments' ), $donation->ID ),
	);

	// Make the API call using the ProcessLink service.
	$response = $iATS_PL->processCreditCardRefundWithTransactionId( $request );

	// Verify successful call
	if ( 'OK' != substr( trim( $response['AUTHORIZATIONRESULT'] ), 0, 2 ) ) {
		$url_data = parse_url( $_SERVER['REQUEST_URI'] );

		// Build query
		$url_query = array_merge(
			wp_parse_args( $url_data['query'] ),
			array( 'give-iats-message' => $response['code'] )
		);

		$url = home_url( "/{$url_data['path']}?" . http_build_query( $url_query ) );

		// Redirect.
		wp_safe_redirect( $url );
		exit();
	}

	// Add iats payment response meta.
	update_post_meta( $donation->ID, 'iats_refund_response', $response );

	// Add refund transaction id.
	give_update_payment_meta( $donation->ID, '_give_payment_refund_id', $response['TRANSACTIONID'] );

	return true;
}

add_filter( 'give_should_update_payment_status', 'give_iats_donation_refund', 10, 4 );


/**
 * Show refund id.
 *
 * @since 1.0
 *
 * @param $donation_id
 */
function give_iats_show_refund_transaction_id( $donation_id ) {
	/* @var Give_Payment $donation Give_Payment object. */
	$donation = new Give_Payment( $donation_id );

	// Bailout.
	if ( 'refunded' !== $donation->status || 'iatspayments' !== $donation->gateway ) {
		return;
	}

	if ( $refund_id = give_get_payment_meta( $donation_id, '_give_payment_refund_id', true ) ):
		?>
		<div class="give-admin-box-inside">
			<p>
				<strong><?php esc_html_e( 'Refund ID:', 'give' ); ?></strong>&nbsp;
				<?php echo $refund_id; ?>
			</p>
		</div>
		<?php
	endif;
}

add_action( 'give_view_order_details_payment_meta_after', 'give_iats_show_refund_transaction_id' );
