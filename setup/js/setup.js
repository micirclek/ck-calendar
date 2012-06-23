function db_setup(hide)
{
	$.get('2/db_setup.php', {}, function(response) {
		if (response.status ==='success') {
			if (typeof(hide) !== 'undefined') {
				$(hide).hide();
			}
			$('#success').show();
		} else if (response.status === 'warning' || response.status === 'error') {
			alert('Error: ' + response.payload.msg);
		}
	}, 'json');
}

$(document).ready(function() {

	$('form#fields').ajaxForm({
		dataType: 'json',
		success: function(response) {
			if (response.status === 'success') {
				$('#config').hide();
				db_setup();
			} else if (response.status === 'warning') {
				$('#file-path').text(response.payload.file_path);
				$('#file-contents').text(response.payload.file_contents);
				$('#config').hide();
				$('#error-file').show();
				alert('Error: we could not write to the database file.  Please copy the below code into the file ' + response.payload.file_path + ' and click done');
			} else if (response.status === 'error') {
				alert('Error: ' + response.payload.msg);
			}
		}
	});

	$('#file-done').click(function() {
		db_setup('#error-file');
	});
});
