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

	$('form#member-change-password-form').ajaxForm({
		dataType: 'json',
		success: function(response) {
			if (response.status === 'success' || response.status === 'warning') {
				alert('Your password has now been changed');
				window.location = 'index.php';
			} else {
				alert('Erorr: ' + response.payload.msg);
			}
		},
		beforeSubmit: function(data, $form, options) {
			if (typeof(Globals) !== 'undefined' && typeof(Globals.user_id) !== 'undefined') {
				data.push({name: 'user_id', value: Globals.user_id});
			}
			return true;
		}
	});
});
