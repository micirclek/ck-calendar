$(document).ready(function() {
	$('input.datetime').each(function() {
		var datetime;
		$(this).hide();
		datetime = /(\d{4}-\d{2}-\d{2}) (\d{1,2}:\d{2}(am|pm))/.exec($(this).val());

		$(this)
			.before(
				$('<input>')
					.attr('type', 'text')
					.attr('name', $(this).attr('name') + '-date')
					.addClass('input-small')
					.datepicker({dateFormat: 'yy-mm-dd'})
					.val(datetime[1])
				.after(' ')
				.after(
					$('<input>')
						.attr('type', 'text')
						.attr('name', $(this).attr('name') + '-time')
						.addClass('input-small')
						.timePicker({
							show24Hours: false,
							step: 15
						})
						.val(datetime[2])
				)
		);
	});

	$('input.date').each(function() {
		$(this).datepicker({dateFormat: 'yy-mm-dd'});
	});

	$('input.multidate').each(function() {
		var _this = $(this);
		var obj, initial;
		$(this).hide();
		obj = $('<div>').css('width', '206px');
		$(this).before(obj);

		initial = [];
		if ($(this).val())
			initial = $.parseJSON($(this).val());

		Calendar.setup({
			cont: obj[0],
			fdow: 0,
			weekNumbers: false,
			selectionType: Calendar.SEL_MULTIPLE,
			selection: initial,
			onSelect: function() {
				_this.val(JSON.stringify(this.selection.get()));
			}
		});
	});

	$('input.user-input').each(function() {
		var _this = $(this)
		var _that;
		_this.hide();
		_this.after(_that = $('<input>')
			.attr('type', 'text')
			.attr('name', _this.attr('name') + '-text')
			.autocomplete({
				source: function(request, callback) {
					$.get('2/member_list.php', request, function(response) {
						if (response.status === 'success') {
							return callback(response.payload.suggestions);
						} else {
							return callback([]);
						}
					}, 'json');
				},
				select: function(event, ui) {
					_this.val(ui.item.value);
					$(this).val(ui.item.label);
					return false;
				}
			})
		);
		$.get('2/member_get.php', {user_id: _this.val()}, function(response) {
			if (response.status === 'success') {
				_that.val(response.payload.user_data.email);
			}
		}, 'json');
	});
});
