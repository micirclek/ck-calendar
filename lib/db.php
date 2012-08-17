<?php

/*
 * Circle K Calendar
 *
 * Copyright 2012 Michigan District of Circle K
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * constructs the set statement for an UPDATE query
 *
 * This constructs the set portion of a mysql update query based upon the
 * below described array of arrays and returns the statement as a string
 *   name: the name of the field in the database
 *   type: the type that the field should be treated as
 *     (see __db_escape_values for a list of types)
 *   value: the value the field should be set to, completely raw
 *
 * @param mysqli $mysqli a mysqli object (for escaping the values
 * @param array $items an array of items (as described above)
 * @return string the set part of the mysql query (e.g. "a=b,c=d")
 */
function db_get_set_statement($mysqli, $items)
{
	$escape = function($item) use ($mysqli) {
		return array('name' => $item['name'],
		             'value' => __db_escape_values($mysqli, $item['type'], $item['value']));
	};

	$set = function($item) {
		return $item['name'] . '=' . $item['value'];
	};

	$items = array_map($escape, $items);
	$items = array_map($set, $items);
	return implode(',', $items);
}

/**
 * constructs the body of the insert statement for an insert query
 *
 * This constructs the main body of an insert statement (e.g.
 * "(k) VALUES (v1),(v2)").  The keys should be an array of arrays with name
 * (the name of the key) and type (the type) set while values should be an
 * array of arrays of the values, indexed using the key names
 *
 * @param mysqli $mysqli a mysqli object
 * @param array $keys an array of descriptions of the key fields
 * @param array $values an array of value sets
 * @return string the mysql code
 */
function db_get_insert_statement($mysqli, $keys, $values)
{
	$query = '';

	foreach ($keys as $key) {
		$query_keys[] = '`' . $key['name'] . '`';
	}
	$query .= '(' . implode(',', $query_keys) . ')';

	$value_array = array();
	foreach ($values as $value) {
		$value_items = array();
		foreach ($keys as $key) {
			if (array_key_exists($key['name'], $value)) {
				$value_items[] = __db_escape_values($mysqli, $key['type'], $value[$key['name']]);
			} else {
				$value_items[] = 'DEFAULT';
			}
		}
		$value_array[] = '(' . implode(',', $value_items) . ')';
	}
	$query .= ' VALUES ' . implode(',', $value_array);

	return $query;
}

/**
 * escapes a value based upon the type
 *
 * This function will escape a value based upon the specified type.  The type
 * should be one of the following:
 *   string: a string
 *   string_n: a string that may be null (will be set to null if empty)
 *   datetime: a datetime field
 *   user: a user id
 *   committee: a committee id
 *   event: an event id
 *   int: an integer value
 *   int_n: an integer that may be null (will be set to null if 0 or empty)
 *   double: a decimal value
 *   float: a decimal value (identical to double)
 *   bool: 0 or 1
 *
 * @param mysqli $mysqli a mysqli object
 * @param $type the type the value should be cast as
 * @param $value the unescaped value of the field
 */
function __db_escape_values($mysqli, $type, $value)
{
	switch ($type) {
		case 'string_n': //string that can be null
			if (!$value) {
				return 'NULL';
				break;
			}
		case 'string':
			return '\'' . $mysqli->real_escape_string($value) . '\'';
			break;
		case 'datetime':
			return '\'' . date(MYSQL_DATETIME_FMT, strtotime($value)) . '\'';
			break;
		case 'user':
		case 'committee':
		case 'event':
		case 'int_n':
			if (!$value) {
				return 'NULL';
			}
		case 'int':
			return (string)intval($value);
			break;
		case 'double':
		case 'float':
			return (string)doubleval($value);
			break;
		case 'bool':
			return ($value) ? '1' : '0';
			break;
		default:
			throw new Exception('invalid type' . $type);
			break;
	}
}

?>
