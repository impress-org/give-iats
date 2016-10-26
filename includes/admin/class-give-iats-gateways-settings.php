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
		$this->section_id    = 'iatspayments';
		$this->section_label = __( 'iATS Payments', 'give-iatspayments' );

		// Add payment gateway to payment gateways list.
		add_filter( 'give_donation_gateways', array( $this, 'add_gateways' ) );

		// Add settings.
		add_filter( 'give_settings_gateways', array( $this, 'add_settings' ), 99999 );
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
				'name' => __( 'iATS Settings', 'give-iatspayments' ),
				'desc' => '<hr>',
				'id'   => 'give_iats_title',
				'type' => 'give_title'
			),
			array(
				'name'   => esc_html__( 'Sandbox Testing', 'give-iatspayments' ),
				'id'      => 'iats_sandbox_testing',
				'type'    => 'checkbox',
				'desc'    => '',
				'default' => '1'
			),
			array(
				'name'   => esc_html__( 'Payment method label', 'give-iatspayments' ),
				'id'      => 'iats_payment_method_label',
				'type'    => 'text',
				'default' => esc_html__( 'Credit Card', 'give-iatspayments' ),
				'desc'    => __( 'Payment method label will be appear on frontend.', 'give-iatspayments' ),
			),
			array(
				'name'   => esc_html__( 'Sandbox Agent Code', 'give-iatspayments' ),
				'id'      => 'iats_sandbox_agent_code',
				'type'    => 'text',
				'default' => 'TEST88',
				'desc'    => __( 'Required agent code provided by iATS.', 'give-iatspayments' ),
			),
			array(
				'name'   => __( 'Sandbox Agent Password', 'give-iatspayments' ),
				'id'      => 'iats_sandbox_agent_password',
				'type'    => 'text',
				'default' => 'TEST88',
				'desc'    => esc_html__( 'Required password provided by iATS.', 'give-iatspayments' ),
			),
			array(
				'name' => esc_html__( 'Live Agent Code', 'give-iatspayments' ),
				'id'    => 'iats_live_agent_code',
				'type'  => 'text',
				'desc'  => __( 'Required agent code provided by iATS.', 'give-iatspayments' ),
			),
			array(
				'name' => __( 'Live Agent Password', 'give-iatspayments' ),
				'id'    => 'iats_live_agent_password',
				'type'  => 'text',
				'desc'  => esc_html__( 'Required password provided by iATS.', 'give-iatspayments' ),
			)
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
		if ( 'iatspayments' !== give_get_payment_gateway( $payment_id ) ) {
			return;
		}
		?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$('select[name=give-payment-status]').change(function () {

					if ('refunded' == $(this).val() ) {
						$(this)
							.closest('div')
							.append('<p id="give-iats-refund"><input type="checkbox" id="give_refund_in_iats" name="give_refund_in_iats" value="1"/><label for="give_refund_in_iats"><?php _e( 'Refund Charge in iATS?', 'give-iatspayments' ); ?></label></p>');
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