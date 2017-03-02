<?php

/**
 * Class Give_iATS_Notices
 *
 * @since 1.0
 */
class Give_iATS_Notices {

	/**
	 * @var
	 */
	static private $instance;

	/**
	 * Give_iATS_Notices constructor.
	 */
	private function __construct() {
	}

	/**
	 * Get reject code list.
	 *
	 * @return array
	 */
	private function get_reject_code_list() {
		return array(
			1         => esc_html__( 'Agent code has not been set up on the authorization system. Please call iATS at 1-888-955-5455.', 'give-iats' ),
			2         => esc_html__( 'Unable to process transaction. Verify and re-enter credit card information.', 'give-iats' ),
			3         => esc_html__( 'Invalid Customer Code.', 'give-iats' ),
			4         => esc_html__( 'Incorrect expiration date.', 'give-iats' ),
			5         => esc_html__( 'Invalid transaction. Verify and re-enter credit card information.', 'give-iats' ),
			6         => esc_html__( 'Please have cardholder call the number on the back of the card.', 'give-iats' ),
			7         => esc_html__( 'Lost or stolen card.', 'give-iats' ),
			8         => esc_html__( 'Invalid card status.', 'give-iats' ),
			9         => esc_html__( 'Restricted card status. Usually on corporate cards restricted to specific sales.', 'give-iats' ),
			10        => esc_html__( 'Error. Please verify and re-enter credit card information.', 'give-iats' ),
			11        => esc_html__( 'General decline code. Please have client call the number on the back of credit card.', 'give-iats' ),
			12        => esc_html__( 'Incorrect CVV2 or Expiry date.', 'give-iats' ),
			14        => esc_html__( 'The card is over the limit.', 'give-iats' ),
			15        => esc_html__( 'General decline code. Please have client call the number on the back of credit card.', 'give-iats' ),
			16        => esc_html__( 'Invalid charge card number. Verify and re-enter credit card information.', 'give-iats' ),
			17        => esc_html__( 'Unable to authorize transaction. Authorizer needs more information for approval.', 'give-iats' ),
			18        => esc_html__( 'Card not supported by institution.', 'give-iats' ),
			19        => esc_html__( 'Incorrect CVV2 security code.', 'give-iats' ),
			22        => esc_html__( 'Bank timeout. Bank lines may be down or busy. Re-try transaction later.', 'give-iats' ),
			23        => esc_html__( 'System error. Re-try transaction later.', 'give-iats' ),
			24        => esc_html__( 'Charge card expired.', 'give-iats' ),
			25        => esc_html__( 'Capture card. Reported lost or stolen.', 'give-iats' ),
			26        => esc_html__( 'Invalid transaction, invalid expiry date. Please confirm and retry transaction.', 'give-iats' ),
			27        => esc_html__( 'Please have cardholder call the number on the back of the card.', 'give-iats' ),
			32        => esc_html__( 'Invalid charge card number.', 'give-iats' ),
			39        => esc_html__( 'Contact IATS 1-888-955-5455.', 'give-iats' ),
			40        => esc_html__( 'Invalid card number. Card not supported by IATS.', 'give-iats' ),
			41        => esc_html__( 'Invalid Expiry date.', 'give-iats' ),
			42        => esc_html__( 'CVV2 required.', 'give-iats' ),
			43        => esc_html__( 'Incorrect AVS.', 'give-iats' ),
			45        => esc_html__( 'Credit card name blocked. Call iATS at 1-888-955-5455.', 'give-iats' ),
			46        => esc_html__( 'Card tumbling. Call iATS at 1-888-955-5455.', 'give-iats' ),
			47        => esc_html__( 'Name tumbling. Call iATS at 1-888-955-5455.', 'give-iats' ),
			48        => esc_html__( 'IP blocked. Call iATS at 1-888-955-5455.', 'give-iats' ),
			49        => esc_html__( 'Velocity 1 – IP block. Call iATS at 1-888-955-5455.', 'give-iats' ),
			50        => esc_html__( 'Velocity 2 – IP block. Call iATS at 1-888-955-5455.', 'give-iats' ),
			51        => esc_html__( 'Velocity 3 – IP block. Call iATS at 1-888-955-5455.', 'give-iats' ),
			52        => esc_html__( 'Credit card BIN country blocked. Call iATS at 1-888-955-5455.', 'give-iats' ),
			100       => esc_html__( 'DO NOT REPROCESS. Call iATS at 1-888-955-5455.', 'give-iats' ),
			'timeout' => esc_html__( 'The system has not responded in the time allotted. Call iATS at 1-888-955-5455.', 'give-iats' ),
		);
	}

	/**
	 * Get rejected codes.
	 *
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
		add_action( 'give_pre_form', array( $this, 'show_frontend_notices' ) );
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
	}

	/**
	 * Set frontend error message if any.
	 *
	 * @param string $form_id Form ID.
	 */
	public function show_frontend_notices( $form_id ) {
		// Bailout.
		if ( ! isset( $_GET['give-iats-message'] ) || ( $form_id != $_GET['form-id'] ) ) {
			return;
		}

		// Error code
		$error_code = sanitize_text_field( $_GET['give-iats-message'] );

		if ( $error_message = $this->get_reject_code( $error_code ) ) {

			// Show error.
			give_output_error( $error_message, true, "iats-error-{$error_code}" );
		}
	}

	/**
	 * Set admin error message if any.
	 *
	 * @param string $form_id Form ID.
	 */
	public function show_admin_notices( $form_id ) {

		// Bailout.
		if ( ! is_admin() || ! isset( $_GET['give-iats-message'] ) ) {
			return;
		}

		// Error code
		$error_code = sanitize_text_field( $_GET['give-iats-message'] );

		if ( $error_message = $this->get_reject_code( $error_code ) ) {
			?>
            <div class="notice notice-error is-dismissible">
                <p>
                    <strong><?php _e( 'iATS donation refund error: ', 'give-iats' ); ?></strong><?php echo $error_message; ?>
                </p>
            </div>
			<?php
		}
	}
}

Give_iATS_Notices::get_instance()->setup_hook();
