<?php

/**
 * Class Give_iATS_Gateway_Settings
 *
 * @since 1.0
 */
class Give_iATS_Gateway_Settings {
	/**
	 * @access private
	 * @var Give_iATS_Gateway_Settings $instance
	 */
	static private $instance;

	/**
	 * @access private
	 * @var string $section_id
	 */
	private $section_id;

	/**
	 * @access private
	 * @var string $section_label
	 */
	private $section_label;

	/**
	 * Give_iATS_Gateway_Settings constructor.
	 */
	private function __construct() {
	}

	/**
	 * get class object.
	 *
	 * @return Give_iATS_Gateway_Settings
	 */
	static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Setup hooks.
	 */
	public function setup_hooks() {
		$this->section_id    = 'iats';
		$this->section_label = __( 'iATS Payments', 'give-iats' );

		// Add payment gateway to payment gateways list.
		add_filter( 'give_payment_gateways', array( $this, 'add_gateways' ) );

		// Add settings.
		add_filter( 'give_settings_gateways', array( $this, 'add_settings' ), 99999 );

		// Add setting to donation edit screen.
		add_action( 'give_view_order_details_before', array( $this, 'give_iats_admin_payment_js' ), 100 );
	}

	/**
	 * Add payment gateways to gateways list.
	 *
	 * @param array $gateways array of payment gateways.
	 *
	 * @return array
	 */
	public function add_gateways( $gateways ) {
		$gateways[ $this->section_id ] = array(
			'admin_label'    => $this->section_label,
			'checkout_label' => give_iats_get_payment_method_label(),
		);

		return $gateways;
	}

	/**
	 * Add setting section.
	 *
	 * @param array $sections Array of section.
	 *
	 * @return array
	 */
	public function add_section( $sections ) {
		$sections[ $this->section_id ] = $this->section_label;

		return $sections;
	}

	/**
	 * Add plugin settings.
	 *
	 * @param array $settings Array of setting fields.
	 *
	 * @return array
	 */
	public function add_settings( $settings ) {
		$iats_settings = array(
			array(
				'name' => __( 'iATS Payments', 'give-iats' ),
				'desc' => '<hr>',
				'id'   => 'give_iats_title',
				'type' => 'give_title',
			),
			array(
				'name'    => __( 'Payment Method Label', 'give-iats' ),
				'id'      => 'iats_payment_method_label',
				'type'    => 'text',
				'default' => __( 'Credit Card', 'give-iats' ),
				'desc'    => __( 'Payment method label will be appear on frontend.', 'give-iats' ),
			),
			array(
				'name' => __( 'Live Agent Code', 'give-iats' ),
				'id'   => 'iats_live_agent_code',
				'type' => 'text',
				'desc' => __( 'Required agent code provided by iATS.', 'give-iats' ),
			),
			array(
				'name' => __( 'Live Agent Password', 'give-iats' ),
				'id'   => 'iats_live_agent_password',
				'type' => 'api_key',
				'desc' => __( 'Required password provided by iATS.', 'give-iats' ),
			),
			array(
				'name' => __( 'Sandbox Agent Code', 'give-iats' ),
				'id'   => 'iats_sandbox_agent_code',
				'type' => 'text',
				'desc' => __( 'Required agent code provided by iATS.', 'give-iats' ),
			),
			array(
				'name' => __( 'Sandbox Agent Password', 'give-iats' ),
				'id'   => 'iats_sandbox_agent_password',
				'type' => 'api_key',
				'desc' => __( 'Required password provided by iATS.', 'give-iats' ),
			),
			array(
				'title'       => __( 'Collect Billing Details', 'give-iats' ),
				'id'          => 'iats_billing_details',
				'type'        => 'radio_inline',
				'options'     => array(
					'enabled'  => esc_html__( 'Enabled', 'give-iats' ),
					'disabled' => esc_html__( 'Disabled', 'give-iats' ),
				),
				'default'     => 'disabled',
				'description' => __( 'This option will enable the billing details section for iATS which requires the donor\'s address to complete the donation. These fields are not required by iATS to process the transaction, but you may have the need to collect the data.', 'give-iats' ),
			),
		);

		return array_merge( $settings, $iats_settings );
	}

	/**
	 * Load Transaction-specific admin javascript
	 *
	 * @param int $payment_id
	 */
	function give_iats_admin_payment_js( $payment_id = 0 ) {
		// Bailout.
		if ( 'iats' !== give_get_payment_gateway( $payment_id ) ) {
			return;
		}
		?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$('select[name=give-payment-status]').change(function () {

					if ('refunded' == $(this).val()) {
						$(this)
							.closest('div')
							.append('<p id="give-iats-refund"><input type="checkbox" id="give_refund_in_iats" name="give_refund_in_iats" value="1"/><label for="give_refund_in_iats"><?php _e( 'Refund Charge in iATS?', 'give-iats' ); ?></label></p>');
					} else {
						$('#give-iats-refund').remove();
					}

				});
			});
		</script>
		<?php

	}
}

Give_iATS_Gateway_Settings::get_instance()->setup_hooks();
