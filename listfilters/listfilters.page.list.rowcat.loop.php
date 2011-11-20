<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=page.list.rowcat.loop
[END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');

$categories = cot_structure_children('page', $x);
if ($categories)
{
	$sub_count = null;
	$cachename = 'subcount_'.$x;
	if ($sqlfilters && $filterway)
	{
		$cachename .= '_' . md5(implode($filterway, $sqlfilters));
	}
	if($cache && $cache->mem)
	{
		if ($cache->mem->exists($cachename, 'listfilters'))
		{
			$sub_count = (int)$cache->mem->get($cachename, 'listfilters');
		}
	}
	if ($sub_count === null)
	{
		$params = array();
		$categories = implode("','", $categories);
		$where = "WHERE page_cat IN ('$categories')";
		if ($sqlfilters && $filterway)
		{
			$where .= ' AND (' . implode(" $filterway ", $sqlfilters) . ')';
			$params = array_merge($params, $sqlparams);
		}
		$sub_count = (int)$db->query("SELECT COUNT(*) FROM $db_pages $where", $params)->fetchColumn();
		$cache && $cache->mem && $cache->mem->store($cachename, $sub_count, 'listfilters');
	}
	$t->assign('LIST_ROWCAT_COUNT', $sub_count);
}

?>