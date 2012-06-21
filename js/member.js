$(document).ready(function() {
	var member_manage_form;
	Globals.years = [];
	Globals.year_selected = 0;

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

	member_manage_form = $('form#member-manage-form');
	if (member_manage_form.length > 0) {
		function add_year(year) {
			var year_end;
			year_end = +year + 1;
			$('#years').append($('<option>', {value: year}).text(year + '-' + year_end));
			$('#years').attr('size', (+$('#years').attr('size')) + 1);
		}

		function reset_yearly() {
			$('#years option').remove();
			$('#years').attr('size', '2');
			Globals.year_selected = 0;
			Globals.years = [];
		}

		$('form#member-manage-form').ajaxForm({
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success' || response.status === 'warning') {
					if (response.status === 'warning') {
						alert('Warning: ' + response.payload.msg + ', please check data again');
					}
					if (typeof(Globals.user_id) != 'undefined' && Globals.user_id != 0) {
						member_manage_form.resetForm();
					} else {
						window.location.reload();
					}
				} else {
					alert('Error: ' + response.payload.msg);
				}
			},
			beforeSubmit: function(data, $form, options) {
				var year_data;
				for (var year in Globals.years) {
					year_data = Globals.years[year];
					for (var item in year_data) {
						data.push({name: 'yearly[' + year + '][' + item + ']', value: (year_data[item] !== null ? year_data[item] : '' )});
					}
				}
				if (typeof(Globals.user_id) != 'undefined' && Globals.user_id != 0) {
					data.push({name: 'user_id', value: Globals.user_id});
					options.url = '2/member_edit.php';
				} else {
					options.url = '2/member_add.php';
				}
				return true;
			}
		});

		$('.edit').click(function() {
			var row;

			reset_yearly();

			row = $(this).parent('tr');
			Globals.user_id = row.attr('data-user-id');
			$('#member-form-submit').text('Edit Member');
			$.get('2/member_get.php', {user_id: Globals.user_id}, function(response) {
				var form;
				form = member_manage_form[0];
				form.first_name.value = response.payload.user_data.first_name;
				form.last_name.value = response.payload.user_data.last_name;
				form.email.value = response.payload.user_data.email;
				form.phone.value = response.payload.user_data.phone;
				Globals.years = response.payload.user_yearly;
				for (var i in response.payload.user_yearly) {
					add_year(i);
				}
			}, 'json');
		});

		$('#years').change(function(e) {
			var form, data;

			if ((Globals.year_selected = +$(this).val()) === 0) {
				return;
			}
			form = member_manage_form[0];
			data = Globals.years[Globals.year_selected];
			form.committee_id.value = data.committee_id;
			form.committee_position.value = data.committee_position;
			form.date_paid.value = data.date_paid;
		});

		member_manage_form.find('[name=committee_id]').change(function() {
			if (Globals.year_selected != 0) {
				Globals.years[Globals.year_selected].committee_id = $(this).val();
			}
		});

		member_manage_form.find('[name=committee_position]').change(function() {
			if (Globals.year_selected != 0) {
				Globals.years[Globals.year_selected].committee_position = $(this).val();
			}
		});

		member_manage_form.find('[name=date_paid]').change(function() {
			if (Globals.year_selected != 0) {
				Globals.years[Globals.year_selected].date_paid = $(this).val();
			}
		});

		$('#add-year').click(function() {
			var year, now;
			now = new Date();
			year = prompt('Which year?', now.getFullYear());
			if (year === null)
				return false;
			year = +/[0-9]{4}/.exec(year);
			if (year === null) {
				alert('Please enter a valid year');
				return false;
			}
			if (!(Globals.years[year] === undefined || Globals.years[year].remove)) {
				alert('Year already exists');
				return false;
			}
			Globals.years[year] = {
				year: year,
				committee_id: null,
				committee_position: 'Member',
				date_paid: ''
			}
			add_year(year);
			$('#years').val(year);
			$('#years').change();
		});

		member_manage_form.bind('reset', function() {
			$('#member-form-submit').text('Add Member');
			Globals.user_id = 0;
			reset_yearly();
		});

		$('#remove-year').click(function() {
			if (Globals.fear_selected === 0) {
				return false;
			}
			if (!confirm("Are you sure you want to remove this year?")) {
				return false;
			}

			Globals.years[Globals.year_selected].remove = true;
			$('#years option[value=' + Globals.year_selected + ']').remove();
			$('#years').attr('size', $('#years').attr('size') - 1);
			Globals.year_selected = $('#years option').last().val() || 0;
			$('#years').val(Globals.year_selected);
			$('#years').change();
		});
	}
});
