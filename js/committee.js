$(document).ready(function() {
	var committee_form;

	committee_form = $('form#committee-manage-form');
	committee_form.ajaxForm({
		dataType: 'json',
		success: function(response) {
			if (response.status === 'success' || response.status === 'warning') {
				window.location.reload();
			} else {
				alert('Error: ' + response.payload.msg);
			}
		},
		beforeSubmit: function(data, $form, options) {
			if (typeof(Globals.committee_id) != 'undefined' && Globals.committee_id != 0) {
				data.push({name: 'committee_id', value: Globals.committee_id});
				options.url = '2/committee_edit.php';
			} else {
				options.url = '2/committee_add.php';
			}
		}
	});

	$('.edit').click(function() {
		var row, form;
		row = $(this).parent('tr');
		Globals.committee_id = row.attr('data-committee-id');
		$('#committee-form-submit').text('Edit Committee');
		form = committee_form[0];
		form.name.value = row.children('.name').text();
		form.access_chair.value = row.children('.access_chair').attr('data-access');
		form.access_member.value = row.children('.access_member').attr('data-access');
	});

	$('#committee-form-reset').click(function() {
		$('#committee-form-submit').text('Add Committee');
		Globals.committee_id = 0;
	});

});
