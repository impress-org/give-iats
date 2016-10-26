jQuery(document).ready(function ($) {
	console.log('js loaded');
	// Show/Hide fields.
	var sandbox_setting_field = $('#iats_sandbox_testing');

	sandbox_setting_field.on('change', function () {
		console.log('event performing');
		if (sandbox_setting_field.is(':checked')) {
			$('#iats_live_agent_code').closest('.cmb-row').hide();
			$('#iats_live_agent_password').closest('.cmb-row').hide();
			$('#iats_sandbox_agent_code').closest('.cmb-row').show();
			$('#iats_sandbox_agent_password').closest('.cmb-row').show();

		} else {
			$('#iats_live_agent_code').closest('.cmb-row').show();
			$('#iats_live_agent_password').closest('.cmb-row').show();
			$('#iats_sandbox_agent_code').closest('.cmb-row').hide();
			$('#iats_sandbox_agent_password').closest('.cmb-row').hide();
		}
	}).change();
});
