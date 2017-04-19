<?php
namespace Cetera;
include_once('common_bo.php');

$nodes = array();
$od = new ObjectDefinition($_REQUEST['type']);   

$list =  $od->getMaterials();
if (isset($_REQUEST['query'])) $list->where('name like "%'.$_REQUEST['query'].'%"');

foreach( $list as $material )
{
      			$name = htmlspecialchars($material->name);
      			$name = str_replace("\n",'',$name);
      			$name = str_replace("\r",'',$name);
                $nodes[] = array(
                    'text' => $name,
                    'id'   => 'material-'.$material->id.'-'.$od->table.'-'.$od->id,
                    'iconCls'  => 'tree-material',
                    'qtip' => '',
                    'leaf' => TRUE,
                    'disabled' => FALSE
                );	
}

echo json_encode($nodes);