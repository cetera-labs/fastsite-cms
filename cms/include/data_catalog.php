<?php
namespace Cetera;

include('common_bo.php');

$data = array();

if (isset($_GET['id']))
{
	$id = (int)$_GET['id'];
	
	if (!$user->allowCat(PERM_CAT_ADMIN,$id) || ($id == 0 && !$user->allowAdmin()))
	{ 
		throw new \Exception('Недостаточно полномочий для совершения этого действия');
	}
	$catalog = Catalog::getById($id);
	$data[] = $catalog->boArray();
}

echo json_encode(array(
    'success' => true,
    'rows'    => $data
));