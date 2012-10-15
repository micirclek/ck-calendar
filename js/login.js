$(document).ready(function() {
	//TODO is persistent good?
	$('#login-form').ajaxForm({
		dataType: 'json',
		success: function(response) {
			if (response.status === 'success' || response.status === 'warning') {
				window.location.reload();
			} else {
				alert(response.payload.msg);
			}
		}
	});
});
