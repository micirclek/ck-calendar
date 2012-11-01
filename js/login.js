$(document).ready(function() {
	$('#login-submit').click(function() {
		var email, pw, persistent;
		email = $('#login-email').val();
		password = $('#login-pw').val();
		persistent = $('#login-persistent').attr('checked') ? 1 : 0;

		$.post(
			'2/login.php',
			{email: email, password: password, persistent: persistent},
			function(response) {
				if (response.status === 'success' || response.status === 'warning') {
					window.location.reload();
				} else {
					alert(response.payload.msg);
				}
			}, 'json'
		);
	});

	$('#login input').keypress(function(e) {
		if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
			$('#login #login-submit').click();
		}
	});
});
