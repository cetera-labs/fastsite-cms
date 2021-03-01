<?php
namespace Cetera;
/************************************************************************************************
Список материалов связанных с исходным по полю
*************************************************************************************************/

try {
   
    include('common_bo.php');
    
    $m = Material::getById((int)$_REQUEST['parent_id'], 0, $_REQUEST['parent_type']);
    $materials = [];    
    
    if ($_REQUEST['field_name'] == 'links_in') {
        
        $db = Application::getInstance()->getDbConnection();
        
        $list = [];

        if ($m->idcat >= 0) {
            $r = $db->query("select A.alias, A.id, B.name, B.type, B.describ from types A, types_fields B where B.id=A.id and B.len=".$m->idcat." and (B.type=".FIELD_LINK." or B.type=".FIELD_LINKSET.")");
            while ($f = $r->fetch()) {
                if ($f['type'] == FIELD_LINK) {
                    $r1 = $db->query("SELECT id FROM ".$f['alias']." WHERE ".$f['name']."=".$m->id);
                } else {
                    $r1 = $db->query("SELECT id FROM ".$f['alias']."_".$m->table."_".$f['name']." WHERE dest=".$m->id);
                }
                while ($f1 = $r1->fetch()) {
                    $list[] = [
                        'material_id' => $f1['id'],
                        'type_id' => $f['id'],
                        'field_name' => $f['name'],
                        'field_describ' => $f['describ'],
                        'field_type' => $f['type'],
                    ];
                }          
            }
        }
        
        $r = $db->query("select A.alias, A.id, B.name, B.describ, B.type from types A, types_fields B where B.id=A.id and B.len = ".$m->objectDefinition->id." and B.type=".FIELD_MATSET);
        while ($f = $r->fetch()) {
            $r1 = $db->query("SELECT id FROM ".$f['alias']."_".$m->table."_".$f['name']." WHERE dest=".$m->id);
            while ($f1 = $r1->fetch()) {
                $list[] = [
                    'material_id' => $f1['id'],
                    'type_id' => $f['id'],
                    'field_name' => $f['name'],
                    'field_describ' => $f['describ'],
                    'field_type' => $f['type'],
                ];
            } 
        }
        
        $r = $db->fetchAll("SELECT B.name, A.id, A.alias, B.describ, B.type from types A, types_fields B where B.id=A.id and B.type=".FIELD_LINKSET2);
        foreach ($r as $f) {
            $r1 = $db->query("SELECT id FROM ".$f['alias']."_".$f['name']." where dest_type=".$m->objectDefinition->id." and dest_id=".$m->id);
            while ($f1 = $r1->fetch()) {
                $list[] = [
                    'material_id' => $f1['id'],
                    'type_id' => $f['id'],
                    'field_name' => $f['name'],
                    'field_describ' => $f['describ'],
                    'field_type' => $f['type'],
                ];
            }         
        }
        
        foreach ($list as $l) {

            try {
                $f = Material::getById($l['material_id'], $l['type_id']);
            }
            catch(\Exception $e) {
                continue;
            }
            
            $materials[] = [
                'id' => $f->id,
                'name' => $f->name,
                'alias' => $f->alias,
                'catalog' => $f->catalog?$f->catalog->getPath()->implode(function($catalog, $index, $first, $last, $total) { return (!$first?'/':'').$catalog->alias; }):'',
                'icon' => $f->published,
                'type_id' => $l['type_id'],
                'field_name' => $l['field_name'],
                'field_describ' => $l['field_describ'],   
                'field_type' => $l['field_type'],            
            ];        
        }        

    }
    else {

        $mat_type = (int)$_REQUEST['mat_type'];
        $field_name = $_REQUEST['field_name'];

        $list = $m->getLinkedObjects($mat_type, $field_name);
        
        if (isset($_REQUEST['limit']) && isset($_REQUEST['page'])) {
            $list->setItemCountPerPage($_REQUEST['limit'])->setCurrentPageNumber($_REQUEST['page']);
        }
        
        if (isset($_REQUEST['query'])) {
            $query = '%'.$_REQUEST['query'].'%';
            $list->where("name like '$query'");
        }
        
        foreach ($list as $f) {
            
            $materials[] = [
                'id' => $f->id,
                'name' => $f->name,
                'alias' => $f->alias,
                'catalog' => $f->catalog?$f->catalog->getPath()->implode(function($catalog, $index, $first, $last, $total) { return (!$first?'/':'').$catalog->alias; }):'',
                'icon' => $f->published,
                'type_id' => $mat_type,
                'field_name' => $field_name,
                'field_describ' => '',   
                'field_type' => 0,
            ];        
        }        
    
    }
    
    echo json_encode(array(
        'success' => true,
        'total'   => count($list),
        'rows'    => $materials
    ));
    
} catch (Exception $e) {

    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage(),
        'rows'    => false
    ));

}