<?php
namespace Cetera;

include('common_bo.php');

$bo = $application->getBo();

$data = array();
$total = 0;

if (!isset($_REQUEST['sort'])) {
    $_REQUEST['sort'] = 'dat';
    $_REQUEST['dir'] = 'DESC';
}

if (isset($_REQUEST['filter']) && is_array($_REQUEST['filter']) && count($_REQUEST['filter'])) {     
	$query = '
		SELECT SQL_CALC_FOUND_ROWS 
		  A.*, B.login
		FROM event_log A 
		LEFT JOIN users B ON (B.id = A.user_id)
		WHERE code IN ("'.implode('","', $_REQUEST['filter']).'")
		ORDER BY '.$_REQUEST['sort'].' '.$_REQUEST['dir'].', id DESC';
	 
	if (isset($_REQUEST['start']) && isset($_REQUEST['limit']))
		$query .= ' LIMIT '.(int)$_REQUEST['start'].','.(int)$_REQUEST['limit'];

	$r = $application->getConn()->fetchAll($query);

	foreach ($r as $f) {
		$e = $bo->getEventById($f['code']);
		if ($e)	$f['name'] = $e['name']; else $f['name'] = $f['code'];
		$data[] = $f;
	}

	$total = $application->getConn()->fetchColumn('SELECT FOUND_ROWS()',[],0);
}

echo json_encode(array(
    'success' => true,
    'total'   => $total,
    'rows'    => $data
));