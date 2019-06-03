<?php
// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

// Get give plugin settings.
$give_settings = give_get_settings();


// Remove plugin settings.
unset( $give_settings['iats_sandbox_testing'] );
unset( $give_settings['iats_sandbox_agent_code'] );
unset( $give_settings['iats_sandbox_agent_password'] );
unset( $give_settings['iats_live_agent_code'] );
unset( $give_settings['iats_live_agent_password'] );


// Update option.
update_option( 'give_settings', $give_settings );
