<?php
namespace Cetera;
include('common_bo.php');

if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

$data = array();

$id = (int)$_REQUEST['id'];

$r = fssql_query("
	SELECT A.name, IFNULL(B.value, A.value) as value, A.describ, A.id, A.value as value_orig
	FROM vars A LEFT JOIN vars_servers B ON (A.id=B.var_id and B.server_id=$id) 
	ORDER BY A.name");

$d = array();

while ($f = mysql_fetch_assoc($r)) $d[] = $f;


echo json_encode(array(
    'success' => true,
    'rows'    => $d
));
?>
