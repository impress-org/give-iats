<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if iATS dependency enable or not.
 *
 * TODO  : This code will deprecate in 1.8, so update it then.
 * @since 1.0
 */
function give_iats_check_dependancies() {
	// Bailout
	if ( ! isset( $_POST['give_settings_saved'] ) ) {
		return;
	}

	$reset_settings = false;

	switch ( esc_attr( $_GET['tab'] ) ) {
		case 'general':
			// Check dependencies.
			if ( ! in_array( $_POST['currency'], array( 'USD', 'CAD', 'GBA', 'EUR' ) ) ) {
				$reset_settings = true;

				// Show notice.
				give_iats_disable_by_currency();

			}
			break;

		case 'gateways':
			// Check dependencies.
			if (
				! isset( $_POST['test_mode'] )
				&& ( empty( $_POST['iats_live_agent_code'] ) || empty( $_POST['iats_live_agent_password'] ) )
			) {
				$reset_settings = true;
				give_iats_disable_by_agent_credentials();

			} elseif (
				isset( $_POST['test_mode'] )
				&& ( empty( $_POST['iats_sandbox_agent_code'] ) || empty( $_POST['iats_sandbox_agent_password'] ) )
			) {
				$reset_settings = true;
				give_iats_disable_by_agent_credentials();
			}
	}

	// Bailout.
	if ( ! $reset_settings ) {
		return;
	}

	// Deactivate iats payment gateways: It has some currency dependency.
	unset( $_POST['gateways']['iatspayments'] );
}

add_action( 'admin_notices', 'give_iats_check_dependancies' );


/**
 * Add message when iATS disable by currency.
 *
 * @param array $messages
 *
 * @return mixed
 */
function give_iats_disable_by_currency() {
	?>
	<div class="notice notice-error is-dismissible">
		<p>
			<?php echo esc_html__( 'iATS payment gateway disabled automatically because you do not have required currency ( USD, CAD, GBA, EUR ).', 'give-iatspayments' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Add message when iATS disable by agent credentials.
 *
 * @param array $messages
 *
 * @return mixed
 */
function give_iats_disable_by_agent_credentials() {
	?>
	<div class="notice notice-error is-dismissible">
		<p>
			<?php echo __( 'iATS payment gateway disabled automatically because agent credentials is not correct.', 'give-iatspayments' ); ?>
		</p>
	</div>
	<?php
}