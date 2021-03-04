<?php
namespace Cetera;

include('common_bo.php');

try {
   
    if (isset($_REQUEST['type'])) {
        $obj = DynamicFieldsObject::getByIdType($_REQUEST['id'], $_REQUEST['type']);
    }
    elseif (isset($_REQUEST['section'])) {
        $obj = Section::getById($_REQUEST['section'])->getMaterialById($_REQUEST['id']);
    }
    
    $data = [
        'success' => true,
        'fields'  => $obj->fields
    ];
    
    echo json_encode($data);
    
} catch (\Exception $e) {

    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage(),
        'rows'    => false
    ));

}
