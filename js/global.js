$(document).ready(function() {
	if (navigator.appName === 'Microsoft Internet Explorer') { //hack for IE < v9
		$('a>button').click(function() {
			window.location = $(this).parent('a').attr('href');
		});
	}
});
