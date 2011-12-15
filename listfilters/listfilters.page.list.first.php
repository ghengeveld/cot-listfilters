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
 *  rng - SQL BETWEEN operator (page_$field BETWEEN $value1 AND $value2) Values must be seperated with two periods (1..2)
 */

defined('COT_CODE') or die('Wrong URL');

require_once cot_incfile('listfilters', 'plug');

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

$filters && list($sqlfilters, $sqlparams) = listfilter_build($filters);

?>