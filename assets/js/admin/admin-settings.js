jQuery(document).ready(function ($) {
	// Show/Hide fields.
	var sandbox_radio_btns = $('input[name="iats_sandbox_testing"]:radio');

	sandbox_radio_btns.on('change', function () {
		var field_value = $('input[name="iats_sandbox_testing"]:radio:checked').val();

		if ('enabled' == field_value) {
			$('#iats_live_agent_code').closest('tr').hide();
			$('#iats_live_agent_password').closest('tr').hide();
			$('#iats_sandbox_agent_code').closest('tr').show();
			$('#iats_sandbox_agent_password').closest('tr').show();
		} else {
			$('#iats_live_agent_code').closest('tr').show();
			$('#iats_live_agent_password').closest('tr').show();
			$('#iats_sandbox_agent_code').closest('tr').hide();
			$('#iats_sandbox_agent_password').closest('tr').hide();
		}
	}).change();
});
