<?php

function make_form_options($options, $value)
{
	$ret = '';
	foreach ($options as $key => $val) {
		$ret .= "<option value='" . $key . "'";
		if ($key == $value)
			$ret .= ' SELECTED';
		$ret .= '>' . $val . '</option>';
	}
	return $ret;
}

?>
