$(document).ready(function() {
	$('input[type=datetime-local]').each(function() {
		$(this).datetimepicker({
			dateFormat: 'yy-mm-dd',
			ampm: true,
			timeFormat: 'h:mmtt'
		});
	});
});
