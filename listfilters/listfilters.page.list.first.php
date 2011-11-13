<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=page.list.first
[END_COT_EXT]
==================== */

/**
 * Advanced filtering for page lists
 * 
 * Filter types:
 *  eq - Equals (page_$field = $value)
 *  ne - Not Equal (page_$field != $value)
 *  lt - Less Than (page_$field < $value)
 *  lte - Less Than or Equal (page_$field <= $value)
 *  gt - Greater Than (page_$field > $value)
 *  gte - Greater Than or Equal (page_$field >= $value)
 *  in - SQL IN operator (page_$field IN ($value1, $value2, $value3)) Values must be comma seperated
 *  rng - SQL BETWEEN operator (page_$field BETWEEN $value1 AND $value2) Values must be seperated with a tilde (1~2)
 */

defined('COT_CODE') or die('Wrong URL');

if (cot_auth('plug', 'listfilters', 'R'))
{
	$operators = array(
		'eq' => "(%s = %s)",
		'ne' => "(%s != %s)",
		'lt' => '(%s < %s)',
		'lte' => '(%s <= %s)',
		'gt' => '(%s > %s)',
		'gte' => '(%s >= %s)',
		'in' => '(%s IN (%s))',
		'rng' => '(%s BETWEEN %s AND %s)'
	);

	$filters = (array)cot_import('filters', 'G', 'ARR');
	$filterway = strtoupper(cot_import('way', 'G', 'ALP'));
	if (!in_array($filterway, array('AND', 'OR', 'XOR'))) $filterway = 'AND';

	if ($o && $p)
	{
		if (!is_array($o)) $o = array($o);
		if (!is_array($p)) $p = array($p);
		$filters['eq'] = array_combine($o, $p);
		unset($o);
		unset($p);
	}
	
	if ($filters)
	{
		$sqlfilters = array();
		$sqlparams = array();
		$fieldexists = array();
		foreach ($filters as $type => $filter)
		{
			$type = strtolower($type);
			if (!is_array($filter)) continue;
			foreach ($filter as $field => $value)
			{
				switch ($type)
				{
					case 'lt':
					case 'lte':
					case 'gt':
					case 'gte':
						$value = cot_import($value, 'D', 'NUM');
						if ($value === null)
						{
							unset($filters[$type][$field]);
							continue 2;
						}
						break;
					case 'in':
						$value = explode(',', cot_import($value, 'D', 'TXT'));
						break;
					case 'rng':
						$value = explode('~', cot_import($value, 'D', 'TXT'));
						if (count($value) != 2)
						{
							unset($filters[$type][$field]);
							continue 2;
						}
						break;
					default:
						$value = cot_import($value, 'D', 'TXT');
						break;
				}
				$field = 'page_' . cot_import($field, 'D', 'ALP');
				if ($fieldexists[$field] === null)
				{
					$fieldexists[$field] = $db->fieldExists($db_pages, $field);
				}
				if (!$fieldexists[$field])
				{
					unset($filters[$type][$field]);
					continue;
				}
				if (!is_array($value)) $value = array($value);
				foreach ($value as &$val)
				{
					$encval = md5($val);
					$sqlparams[$encval] = $val;
					$val = ":$encval";
				}
				$sqlfilters[] = ($type == 'rng') ? 
					sprintf($operators[$type], $field, $value[0], $value[1]) : 
					sprintf($operators[$type], $field, implode(',', $value));
			}
		}
		if ($sqlfilters)
		{
			$sqlfilters = '(' . implode(" $filterway ", $sqlfilters) . ')';
		}
	}
}

?>