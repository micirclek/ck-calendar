$(document).ready(function() {
	var signup_form, edit_form, hours_submit_form, add_form;

	function add_event_id(data, $form, options) {
		data.push({name: 'event_id', value: Globals.event_id});
		return true;
	}

	signup_form = $('form#event-signup')
	if (signup_form.length > 0) {
		signup_form.ajaxForm({
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success' || response.status === 'warning') {
					window.location.reload();
				} else {
					alert(response.payload.msg);
				}
			},
			beforeSubmit: add_event_id
		});
	}

	edit_form = $('form#event-edit');
	if (edit_form.length > 0) {
		edit_form.ajaxForm({
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success' || response.status === 'warning') {
					window.location.reload();
				} else {
					alert('Error modifying event: ' + response.payload.msg);
				}
			},
			beforeSubmit: function(data, $form, options) {
				for (var i in data) {
					if (data[i].name === 'start_ts') {
						data[i].name = 'start_time';
					} else if (data[i].name === 'end_ts') {
						data[i].name = 'end_time';
					}
				}
				return add_event_id(data, $form, options);
			}
		});
	}


	hours_submit_form = $('form#hours-submit');
	if (hours_submit_form.length > 0) {
		hours_submit_form.ajaxForm({
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success' || response.status === 'warning') {
					window.location.reload();
				} else {
					alert('Error submitting hours: ' + response.payload.msg);
				}
			},
			beforeSubmit: add_event_id
		});
	}

	add_event_form = $('form#event-add-form');
	if (add_event_form.length > 0) {
		add_event_form.ajaxForm({
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success' || response.status === 'warning') {
					window.history.back();
				} else {
					alert('Error creating event: ' + response.payload.msg);
				}
			},
			beforeSubmit: function(data, $form, options) {
				for (var i in data) {
					if (data[i].name === 'start_ts') {
						data[i].name = 'start_time';
					} else if (data[i].name === 'end_ts') {
						data[i].name = 'end_time';
					}
				}
				return true;
			}
		});
	}

	$('.remove').click(function() {
		var data, row, target, post_data, reload;
		row = $(this).parent('tr');
		data = /([a-z]+)-([0-9]+)/.exec(row.attr('id'));
		if (data[1] === 'signup') {
			target = '2/signup_remove.php';
			post_data = {signup_id: data[2]};
		} else if (data[1] === 'hours') {
			target = '2/hours_remove.php';
			post_data = {hours_id: data[2]};
		}

		$.post(target, post_data, function(response) {
			if (response.status === 'success' || response.status === 'warning') {
				if (row.hasClass('reload')) {
					window.location.reload();
				} else {
					row.hide('slow', function() {$(this).remove()});
				}
			} else {
				alert(response.payload.msg);
			}
		}, 'json');
	});
});
