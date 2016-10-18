<?php

/**
 * Class Give_iATS_Notices
 */
class Give_iATS_Notices {
	static private $instance;

	private function __construct() {
	}

	/**
	 * Get reject code list.
	 *
	 * @return array
	 */
	private function get_reject_code_list() {
		return array(
			1         => esc_html__( 'Agent code has not been set up on the authorization system. Please call iATS at 1-888-955-5455.', 'give-iatspayments' ),
			2         => esc_html__( 'Unable to process transaction. Verify and re-enter credit card information.', 'give-iatspayments' ),
			3         => esc_html__( 'Invalid Customer Code.', 'give-iatspayments' ),
			4         => esc_html__( 'Incorrect expiration date.', 'give-iatspayments' ),
			5         => esc_html__( 'Invalid transaction. Verify and re-enter credit card information.', 'give-iatspayments' ),
			6         => esc_html__( 'Please have cardholder call the number on the back of the card.', 'give-iatspayments' ),
			7         => esc_html__( 'Lost or stolen card.', 'give-iatspayments' ),
			8         => esc_html__( 'Invalid card status.', 'give-iatspayments' ),
			9         => esc_html__( 'Restricted card status. Usually on corporate cards restricted to specific sales.', 'give-iatspayments' ),
			10        => esc_html__( 'Error. Please verify and re-enter credit card information.', 'give-iatspayments' ),
			11        => esc_html__( 'General decline code. Please have client call the number on the back of credit card.', 'give-iatspayments' ),
			12        => esc_html__( 'Incorrect CVV2 or Expiry date.', 'give-iatspayments' ),
			14        => esc_html__( 'The card is over the limit.', 'give-iatspayments' ),
			15        => esc_html__( 'General decline code. Please have client call the number on the back of credit card.', 'give-iatspayments' ),
			16        => esc_html__( 'Invalid charge card number. Verify and re-enter credit card information.', 'give-iatspayments' ),
			17        => esc_html__( 'Unable to authorize transaction. Authorizer needs more information for approval.', 'give-iatspayments' ),
			18        => esc_html__( 'Card not supported by institution.', 'give-iatspayments' ),
			19        => esc_html__( 'Incorrect CVV2 security code.', 'give-iatspayments' ),
			22        => esc_html__( 'Bank timeout. Bank lines may be down or busy. Re-try transaction later.', 'give-iatspayments' ),
			23        => esc_html__( 'System error. Re-try transaction later.', 'give-iatspayments' ),
			24        => esc_html__( 'Charge card expired.', 'give-iatspayments' ),
			25        => esc_html__( 'Capture card. Reported lost or stolen.', 'give-iatspayments' ),
			26        => esc_html__( 'Invalid transaction, invalid expiry date. Please confirm and retry transaction.', 'give-iatspayments' ),
			27        => esc_html__( 'Please have cardholder call the number on the back of the card.', 'give-iatspayments' ),
			32        => esc_html__( 'Invalid charge card number.', 'give-iatspayments' ),
			39        => esc_html__( 'Contact IATS 1-888-955-5455.', 'give-iatspayments' ),
			40        => esc_html__( 'Invalid card number. Card not supported by IATS.', 'give-iatspayments' ),
			41        => esc_html__( 'Invalid Expiry date.', 'give-iatspayments' ),
			42        => esc_html__( 'CVV2 required.', 'give-iatspayments' ),
			43        => esc_html__( 'Incorrect AVS.', 'give-iatspayments' ),
			45        => esc_html__( 'Credit card name blocked. Call iATS at 1-888-955-5455.', 'give-iatspayments' ),
			46        => esc_html__( 'Card tumbling. Call iATS at 1-888-955-5455.', 'give-iatspayments' ),
			47        => esc_html__( 'Name tumbling. Call iATS at 1-888-955-5455.', 'give-iatspayments' ),
			48        => esc_html__( 'IP blocked. Call iATS at 1-888-955-5455.', 'give-iatspayments' ),
			49        => esc_html__( 'Velocity 1 – IP block. Call iATS at 1-888-955-5455.', 'give-iatspayments' ),
			50        => esc_html__( 'Velocity 2 – IP block. Call iATS at 1-888-955-5455.', 'give-iatspayments' ),
			51        => esc_html__( 'Velocity 3 – IP block. Call iATS at 1-888-955-5455.', 'give-iatspayments' ),
			52        => esc_html__( 'Credit card BIN country blocked. Call iATS at 1-888-955-5455.', 'give-iatspayments' ),
			100       => esc_html__( 'DO NOT REPROCESS. Call iATS at 1-888-955-5455.', 'give-iatspayments' ),
			'timeout' => esc_html__( 'The system has not responded in the time allotted. Call iATS at 1-888-955-5455.', 'give-iatspayments' ),
		);
	}

	/**
	 * Get rejected codes.
	 * @see https://www.iatspayments.com/english/help/rejects.html
	 *
	 * @param int|null $code Error code value.
	 *
	 * @return string
	 */
	private function get_reject_code( $code = null ) {
		$reject_code_list = $this->get_reject_code_list();

		return ( ! is_null( $code ) && array_key_exists( $code, $reject_code_list ) ? $reject_code_list[ $code ] : '' );
	}

	/**
	 * Get instance.
	 *
	 * @return static
	 */
	static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Setup hooks.
	 */
	public function setup_hook() {
		add_action( 'give_pre_form_output', array( $this, 'show_notices' ) );
	}

	/**
	 * Set error message if any.
	 *
	 * @param string $form_id Form ID.
	 */
	public function show_notices( $form_id ) {
		// Bailout.
		if ( ! isset( $_GET['give-iats-message'] ) || ( $form_id != $_GET['form-id'] ) ) {
			return;
		}

		// Error code
		$error_code = sanitize_text_field( $_GET['give-iats-message'] );

		if ( $error_message = $this->get_reject_code( $error_code ) ) {

			// Show error.
			give_output_error( $error_message );
		}
	}
}

Give_iATS_Notices::get_instance()->setup_hook();