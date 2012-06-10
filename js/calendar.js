function set_month(month, year) {
	document.month_select.month.value = month;
	document.month_select.year.value = year;
	document.month_select.submit();
	return false;
}
