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

		if ( is_admin() ) {

			// Add section to payment gateways tab.
			add_filter( 'give_get_sections_gateways', array( $this, 'add_section' ) );

			// Add section settings.
			add_filter( 'give_get_settings_gateways', array( $this, 'add_settings' ) );
		}
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
		$current_section = give_get_current_setting_section();

		if ( $this->section_id == $current_section ) {
			$settings = array(
				array(
					'id'   => 'give_iats_payments_setting',
					'type' => 'title',
				),
				array(
					'title'   => esc_html__( 'Sandbox Testing', 'give-iatspayments' ),
					'id'      => 'iats_sandbox_testing',
					'type'    => 'radio_inline',
					'desc'    => '',
					'default' => 'enabled',
					'options' => array(
						'enabled'  => esc_html__( 'Enabled', 'give-iatspayments' ),
						'disabled' => esc_html__( 'Disabled', 'give-iatspayments' ),
					),
				),
				array(
					'title'   => esc_html__( 'Payment method label', 'give-iatspayments' ),
					'id'      => 'iats_payment_method_label',
					'type'    => 'text',
					'default' => esc_html__( 'Credit Card', 'give-iatspayments' ),
					'desc'    => __( 'Payment method label will be appear on frontend.', 'give-iatspayments' ),
				),
				array(
					'title'   => esc_html__( 'Sandbox Agent Code', 'give-iatspayments' ),
					'id'      => 'iats_sandbox_agent_code',
					'type'    => 'text',
					'default' => 'TEST88',
					'desc'    => __( 'Required agent code provided by iATS.', 'give-iatspayments' ),
				),
				array(
					'title'   => __( 'Sandbox Agent Password', 'give-iatspayments' ),
					'id'      => 'iats_sandbox_agent_password',
					'type'    => 'password',
					'default' => 'TEST88',
					'desc'    => esc_html__( 'Required password provided by iATS.', 'give-iatspayments' ),
				),
				array(
					'title' => esc_html__( 'Live Agent Code', 'give-iatspayments' ),
					'id'    => 'iats_live_agent_code',
					'type'  => 'text',
					'desc'  => __( 'Required agent code provided by iATS.', 'give-iatspayments' ),
				),
				array(
					'title' => __( 'Live Agent Password', 'give-iatspayments' ),
					'id'    => 'iats_live_agent_password',
					'type'  => 'password',
					'desc'  => esc_html__( 'Required password provided by iATS.', 'give-iatspayments' ),
				),
				array(
					'id'   => 'give_iats_payments_setting',
					'type' => 'sectionend',
				),
			);
		}

		return $settings;
	}
}

Give_iATS_Gateway_Settings::get_instance()->setup_hooks();