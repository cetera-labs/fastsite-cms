<?php
namespace Cetera;
include('common_bo.php');

if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

$d = $application->getConn()->fetchAll('SELECT A.name, IFNULL(B.value, A.value) as value, A.describ, A.id, A.value as value_orig
	FROM vars A LEFT JOIN vars_servers B ON (A.id=B.var_id and B.server_id=?) 
	ORDER BY A.name', array((int)$_REQUEST['id']));

echo json_encode(array(
    'success' => true,
    'rows'    => $d
));
