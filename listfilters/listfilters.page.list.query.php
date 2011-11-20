<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=page.list.query
[END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');

if ($sqlfilters && $filterway)
{
	$where['filter'] = '(' . implode(" $filterway ", $sqlfilters) . ')';
	if (is_array($sqlparams))
	{
		$params = array_merge($params, $sqlparams);
	}
}

if ($filters)
{
	$list_url_path = array_merge($list_url_path, array('filters' => $filters));
	$list_url = cot_url('page', $list_url_path);
}

/**
 * Returns TRUE if the filter is active and meets the provided value.
 * Returns TRUE if the filter is not active, if the last argument is omitted.
 * 
 * @param string $type Filter type
 * @param string $field Field name
 * @param string $value Value that was filtered on (optional)
 * @return bool
 */
function listfilter_active($type, $field, $value = NULL)
{
	global $list_url_path;
	return ($list_url_path['filters'][$type][$field] == $value);
}

/**
 * Returns the number of items that would be shown if the filter were applied.
 * 
 * @param string $type Filter type
 * @param string $field Field name
 * @param string $value Value that was filtered on (optional)
 * @return bool
 */
function listfilter_count($type, $field, $value = NULL)
{
	global $c, $db, $db_pages, $filters, $filterway;
	$params = array();
	$categories = implode("','", cot_structure_children('page', $c));
	$where = "WHERE page_cat IN ('$categories')";
	$GLOBALS['cfg']['display_errors'] = true;
	if ($value === null)
	{
		if ($filters[$type][$field])
		{
			unset($filters[$type][$field]);
		}
	}
	else
	{
		$filters[$type][$field] = $value;
	}
	list($sqlfilters, $sqlparams) = listfilter_build($filters);
	if ($sqlfilters && $filterway)
	{
		$where .= ' AND (' . implode(" $filterway ", $sqlfilters) . ')';
		$params = array_merge($params, $sqlparams);
	}
	return (int)$db->query("SELECT COUNT(*) FROM $db_pages $where", $params)->fetchColumn();
}

/**
 * Returns the current URL with query parameters for this filter added to it, or
 * if the last argument is omitted, the current URL with this filter removed 
 * from it.
 * 
 * @param string $type Filter type
 * @param string $field Field name
 * @param string $value Value to filter on (optional)
 * @return string
 */
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

/**
 * Returns the URL query parameter for all currently active filters.
 * 
 * @return string
 */
function listfilter_urlparam()
{
	global $list_url_path;
	return http_build_query(array('filters' => $list_url_path['filters']));
}

/**
 * Returns the current URL without filters.
 * 
 * @return string
 */
function listfilter_plainurl()
{
	global $list_url_path;
	$params = $list_url_path;
	if($params['filters'])
	{
		unset($params['filters']);
	}
	return cot_url('page', $params);
}

/**
 * Wrapper for cot_checkbox()
 *
 * @param string $type Filter type
 * @param string $field Field name
 * @param string $value Value to filter on
 * @param int $default Default checked state (0 or 1)
 * @param string $title Alternative label title (defaults to $value)
 * @return string
 */
function listfilter_form_checkbox($type, $field, $value, $default = 0, $title = NULL)
{
	global $list_url_path;
	if (!function_exists('cot_checkbox')) include cot_incfile('forms');
	if ($title === NULL) $title = $value;
	$chosen = (isset($list_url_path['filters'][$type][$field])) ?
		($list_url_path['filters'][$type][$field] == $value) : (bool)$default;
	return cot_checkbox($chosen, "filters[$type][$field]", $title, 
		'id="filter_'.$type.'_'.$field.'"', $value);
}

/**
 * Wrapper for cot_inputbox()
 *
 * @param string $type Filter type
 * @param string $field Field name
 * @param string $default Default value for the input field
 * @return string
 */
function listfilter_form_inputbox($type, $field, $default = '')
{
	global $list_url_path;
	if (!function_exists('cot_inputbox')) include cot_incfile('forms');
	$value = $list_url_path['filters'][$type][$field];
	if (!$value) $value = $default;
	return cot_inputbox('text', "filters[$type][$field]", $value, 
		'id="filter_'.$type.'_'.$field.'"');
}

/**
 * Wrapper for cot_inputbox() with HTML5 input type 'number'
 *
 * @param string $type Filter type
 * @param string $field Field name
 * @param float $min Minimum allowed value
 * @param float $max Maximum allowed value
 * @param float $step Allowed number interval (optional, defaults to 1)
 * @param float $default Default value (optional, defaults to $min)
 * @return string
 */
function listfilter_form_numberbox($type, $field, $min, $max, $step = 1, $default = NULL)
{
	global $list_url_path;
	if (!function_exists('cot_inputbox')) include cot_incfile('forms');
	$value = $list_url_path['filters'][$type][$field];
	if (!$value) $value = ($default !== NULL) ? $default : $min;
	return cot_inputbox('number', "filters[$type][$field]", (float)$value, 
		'min="'.(float)$min.'" max="'.(float)$max.'" step="'.(float)$step.'" id="filter_'.$type.'_'.$field.'"');
}

/**
 * Wrapper for cot_radiobox()
 *
 * @param string $type Filter type
 * @param string $field Field name
 * @param string $options Comma-separated list of options
 * @param string $default Option selected by default
 * @param string $titles Comma-separated list of alternative label titles (defaults to $options)
 * @return string
 */
function listfilter_form_radiobox($type, $field, $options, $default = '', $titles = NULL)
{
	global $list_url_path;
	if (!function_exists('cot_radiobox')) include cot_incfile('forms');
	$options = explode(',', $options);
	$titles = ($titles !== NULL && count($options) == count(explode(',', $titles))) ?
		explode(',', $titles) : $options;
	$chosen = $list_url_path['filters'][$type][$field];
	if (!$chosen) $chosen = $default;
	if ($type == 'in') $chosen = explode(',', $chosen);
	return cot_radiobox($chosen, "filters[$type][$field]", $options, $titles, true, 
		'id="filter_'.$type.'_'.$field.'"');
}

/**
 * Wrapper for cot_inputbox() with HTML5 input type 'range'
 * Note that this doesn't support filter type 'rng', for that you will need to 
 * use JavaScript (jQuery UI for example).
 *
 * @param string $type Filter type
 * @param string $field Field name
 * @param float $min Minimum allowed value
 * @param float $max Maximum allowed value
 * @param float $step Allowed number interval (optional, defaults to 1)
 * @param float $default Default value (optional, defaults to $min)
 * @return string
 */
function listfilter_form_rangebox($type, $field, $min, $max, $step = 1, $default = NULL)
{
	global $list_url_path;
	if (!function_exists('cot_inputbox')) include cot_incfile('forms');
	$value = $list_url_path['filters'][$type][$field];
	if (!$value) $value = ($default !== NULL) ? $default : $min;
	return cot_inputbox('range', "filters[$type][$field]", (float)$value, 
		'min="'.(float)$min.'" max="'.(float)$max.'" step="'.(float)$step.'" id="filter_'.$type.'_'.$field.'"');
}

/**
 * Wrapper for cot_selectbox()
 *
 * @param string $type Filter type
 * @param string $field Field name
 * @param string $options Comma-separated list of options
 * @param string $default Option selected by default
 * @param string $titles Comma-separated list of alternative label titles (defaults to $options)
 * @return string
 */
function listfilter_form_selectbox($type, $field, $options, $default = '', $titles = NULL)
{
	global $list_url_path;
	if (!function_exists('cot_selectbox')) include cot_incfile('forms');
	$options = explode(',', $options);
	$titles = ($titles !== NULL && count($options) == count(explode(',', $titles))) ?
		explode(',', $titles) : $options;
	$chosen = $list_url_path['filters'][$type][$field];
	if (!$chosen) $chosen = $default;
	if ($type == 'in') $chosen = explode(',', $chosen);
	return cot_selectbox($chosen, "filters[$type][$field]", $options, $titles, true, 
		'id="filter_'.$type.'_'.$field.'"');
}

?>