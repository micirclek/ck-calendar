<?php

function make_form_options($options, $value = NULL)
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

/**
 * constructs a form based upon the information passed in
 *
 * The form should be described by an array with type information for each value
 *
 * name: The name of the form input as well as what index should be used to look up the saved
 * title: The title for the form input that should be shown to the user
 * type: the type of the input
 *   user: a user
 *   text: simple text
 *   number: a number
 *   datetime-local: a local datetime
 *   textarea: a block textarea
 *   select: a dropdown select
 * options: An array of options, the exact semantics vary based upon the type
 *   simple type: extra parameters to specify in the input tag
 *   select: the items to be chosen from
 *
 * @param array $form_info an array of form items (see description above)
 * @param array $saved an array of saved values
 * @return string the inner content for the described form form
 */
function form_construct($form_info, $saved = NULL)
{
	$form = '';

	foreach ($form_info as $item) {
		$form .= "<div class='control-group'>";
		$form .= "<label class='control-label'>" . $item['title'] . "</label>";
		$form .= "<div class='controls'>";
		switch ($item['type']) {
			case 'user':
				$form .= "<input class='user-input' name='" . $item['name'] .
				         "' type='number' step='1' min='1'";
				if ($saved && array_key_exists($item['name'], $saved)) {
					$form .= " value='" . $saved[$item['name']] . "'";
				}
				$form .= " />";
				break;
			case 'text':
			case 'number':
			case 'datetime-local':
				$form .= "<input name='" . $item['name'] . "' type='" . $item['type'] . "'";
				if ($saved && array_key_exists('options', $item)) {
					foreach ($item['options'] as $key => $val) {
						$form .= $key . "='" . $val . "'";
					}
				}
				if ($saved && array_key_exists($item['name'], $saved)) {
					$form .= " value='";
					switch ($item['type']) {
						case 'number':
						case 'text':
							$form .= $saved[$item['name']];
							break;
						case 'datetime-local':
							$form .= date(DISPLAY_DATE_FMT . ' ' . DISPLAY_TIME_FMT, $saved[$item['name']]);
							break;
					}
					$form .= "'";
				}
				$form .= " />";
				break;
			case 'textarea':
				$form .= "<textarea name='" . $item['name'] . "' rows='3'>";
				if ($saved && array_key_exists($item['name'], $saved)) {
					$form .= $saved[$item['name']];
				}
				$form .= "</textarea>";
				break;
			case 'select':
				$form .= "<select name='" . $item['name'] . "'>";
				$form .= make_form_options($item['options'], ($saved && array_key_exists($item['name'], $saved) ? $saved[$item['name']] : NULL));
				$form .= "</select>";
				break;
		}
		$form .= "</div></div>";
	}

	return $form;
}

?>
