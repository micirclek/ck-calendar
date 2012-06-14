$(document).ready(function() {
	$('input[type=datetime-local]').each(function() {
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
});
