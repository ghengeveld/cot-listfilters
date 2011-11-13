<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=page.list.query
[END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');

if ($sqlfilters)
{
	$where['filter'] = $sqlfilters;
	$params = array_merge($params, $sqlparams);
}

if ($filters)
{
	$list_url_path = array_merge($list_url_path, array('filters' => $filters));
	$list_url = cot_url('page', $list_url_path);
}

function listfilter_active($type, $field, $value = NULL)
{
	global $list_url_path;
	return ($list_url_path['filters'][$type][$field] == $value);
}

function listfilter_url($type, $field, $value = NULL)
{
	global $list_url_path;
	$params = $list_url_path;
	if ($value === NULL || listfilter_active($type, $field, $value))
	{
		unset($params['filters'][$type][$field]);
	}
	else
	{
		$params['filters'][$type][$field] = $value;
	}
	return cot_url('page', $params);
}

function listfilter_urlparam()
{
	global $list_url_path;
	return http_build_query(array('filters' => $list_url_path['filters']));
}

function listfilter_form_checkbox($type, $field, $value, $title = NULL)
{
	global $list_url_path;
	if (!function_exists('cot_checkbox')) include cot_incfile('forms');
	if ($title === NULL) $title = $value;
	$chosen = ($list_url_path['filters'][$type][$field] == $value);
	return cot_checkbox($chosen, "filters[$type][$field]", $title, 'id="filter_'.$type.'_'.$field.'"', $value);
}

function listfilter_form_inputbox($type, $field, $values, $titles = '')
{
	global $list_url_path;
	if (!function_exists('cot_inputbox')) include cot_incfile('forms');
	$value = $list_url_path['filters'][$type][$field];
	return cot_inputbox('text', "filters[$type][$field]", $value, 'id="filter_'.$type.'_'.$field.'"');
}

function listfilter_form_radiobox($type, $field, $values, $titles = '')
{
	global $list_url_path;
	if (!function_exists('cot_radiobox')) include cot_incfile('forms');
	$values = explode(',', $values);
	$titles = explode(',', $titles);
	if (count($values) != count($titles)) $titles = $values;
	$chosen = $list_url_path['filters'][$type][$field];
	if ($type == 'in') $chosen = explode(',', $chosen);
	return cot_radiobox($chosen, "filters[$type][$field]", $values, $titles, true, 'id="filter_'.$type.'_'.$field.'"');
}

function listfilter_form_selectbox($type, $field, $values, $titles = '')
{
	global $list_url_path;
	if (!function_exists('cot_selectbox')) include cot_incfile('forms');
	$values = explode(',', $values);
	$titles = explode(',', $titles);
	if (count($values) != count($titles)) $titles = $values;
	$chosen = $list_url_path['filters'][$type][$field];
	if ($type == 'in') $chosen = explode(',', $chosen);
	return cot_selectbox($chosen, "filters[$type][$field]", $values, $titles, true, 'id="filter_'.$type.'_'.$field.'"');
}

?>