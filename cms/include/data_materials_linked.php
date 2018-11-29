<?php
namespace Cetera;
/************************************************************************************************
Список материалов связанных с исходным по полю
*************************************************************************************************/

try {
   
    include('common_bo.php');
    
    $m = Material::getById((int)$_REQUEST['parent_id'], 0, $_REQUEST['parent_type']);
	
	$list = $m->getLinkedObjects((int)$_REQUEST['mat_type'], $_REQUEST['field_name'])->setItemCountPerPage($_REQUEST['limit'])->setCurrentPageNumber($_REQUEST['page']);
    
	if (isset($_REQUEST['query'])) {
		$query = '%'.$_REQUEST['query'].'%';
		$list->where("name like '$query'");
	}
	
    $materials = [];
    
    foreach ($list as $f) {          
        $materials[] = [
			'id' => $f->id,
			'name' => $f->name,
			'alias' => $f->alias,
			'dat' => $f->dat,
			'catalog' => $f->catalog->name,
			'icon' => $f->published,
		];        
    }
    
    echo json_encode(array(
        'success' => true,
        'total'   => $list->getCountAll(),
        'rows'    => $materials
    ));
    
} catch (Exception $e) {

    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage(),
        'rows'    => false
    ));

}