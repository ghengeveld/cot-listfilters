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

?>