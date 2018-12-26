<?php
namespace Cetera;

include('common_bo.php');

try {
   
	$obj = DynamicFieldsObject::getByIdType($_REQUEST['id'], $_REQUEST['type']);
    
    echo json_encode([
        'success' => true,
        'fields'  => $obj->fields
    ]);
    
} catch (\Exception $e) {

    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage(),
        'rows'    => false
    ));

}
