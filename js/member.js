$(document).ready(function() {

	$('form#member-register-form').ajaxForm({
		dataType: 'json',
		success: function(response) {
			if (response.status === 'success' || response.status === 'warning') {
				alert('Thank you for registering, you are now logged in!');
				window.location = 'index.php';
			} else {
				alert('Error: ' + response.payload.msg);
			}
		}
	});
});
