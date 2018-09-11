<?php
namespace Cetera;
include_once('common_bo.php');
header('Content-type: application/json; charset=UTF-8');

$exclude = -1;
if (isset($_GET['exclude'])) $exclude = (int)$_GET['exclude'];
$rule = Util::get('rule');
$nolink = Util::get('nolink', TRUE);
$only = Util::get('only');
$materials = Util::get('materials', TRUE);
$nocatselect = Util::get('nocatselect', TRUE);
$norootselect = Util::get('norootselect', TRUE);
$exclude_mat = Util::get('exclude_mat', TRUE);
$matsort = Util::get('matsort');

$nodes = array();
$node = $_REQUEST['node'];

if ($only)
{
	$od = ObjectDefinition::findById($only); 
	$only = $od->id;	
}

if ($node == 'root') {

    $nodes[] = array(
        'text' => 'root',
		'name' => 'root',
        'id'   => 'item-0',
		'item_id'  => 0,
        'iconCls'  => 'tree-folder-visible',
        'qtip' => '',
        'leaf' => FALSE,
        'mtype' => 0,
        'disabled' => ($nocatselect || $norootselect || $only > 0)?TRUE:FALSE
    );

} else {

    list($dummy, $id) = explode('-',$node);
    $c = Catalog::getById($id);
    if ($c) {
		if (!$c->isLink()) {
			foreach ($c->children as $child)
			{
				$a = process_child($child, $rule, $only, $nolink, $exclude, $nocatselect);    
				if (is_array($a)) $nodes[] = $a;  
			}  
		}		
       
        if ($materials && ((!$only || $c->prototype->materialsType==$only) && $c->prototype->materialsType))
        {                            
            $where = 'id<>'.$exclude_mat;
            if ($_GET['query'])
                $where .= ' and name LIKE '.$application->getConn()->quote('%'.$_GET['query'].'%');
            //$m = $c->getMaterials('name', $where, $matsort, '', 500, 0);
			$m = $c->getMaterials()->where($where)->setItemCountPerPage(500);
			
            foreach ($m as $material) {
      			$name = htmlspecialchars('['.$material->id.'] '.$material->name);
      			$name = str_replace("\n",'',$name);
      			$name = str_replace("\r",'',$name);
                $nodes[] = array(
                    'text' => $name,
                    'id'   => 'material-'.$material->id.'-'.$material->table.'-'.$material->type,
                    'iconCls'  => 'tree-material',
                    'qtip' => '',
                    'leaf' => TRUE,
                    'disabled' => FALSE
                );
            }
        }
    }
}

echo json_encode($nodes);
    
function process_child($child, $rule, $only, $nolink, $exclude, $nocatselect) {
    global $user;
    
    if ($child->id == $exclude) return FALSE;
    if (!$user->allowCat(PERM_CAT_VIEW,$child->id)) return FALSE;
    
	if ($rule) { 
	    if (is_int($rule))
	        $right = $user->allowCat($rule,$child->id);
		else {
		  $rul = explode('u',$rule); 
          $right = 0;
		  for ($i=0; $i<sizeof($rul); $i++)
		    $right = $right | $user->allowCat($rul[$i],$child->id);
		}
	} else $right = 1;
	
    if ($only) { if ($child->materialsType != $only) { $right = 0; } }
	  
	if (($child->isLink())&&($nolink)) $right = 0;

    $cls = 'tree-folder-visible';
    if ($child instanceof Server) $cls = 'tree-server';
    if ($child->isLink()) $cls = 'tree-folder-link';
    if ($child->hidden) $cls = 'tree-folder-hidden';
	
	try {
		if ($child->materialsType) {
			$od = ObjectDefinition::findById($child->materialsType);
			$mtype_name = $od->getDescriptionDisplay();
		}
		else {
			$mtype_name = '';
		}
	}
	catch (\Exception $e) {
		$mtype_name = '';
	}
	
    return array(
        'text'  => '<span class="tree-alias">'.$child->alias.'</span>'.$child->name,
		'name'  => $child->name, 
        'alias' => $child->alias,
        'id'    => 'item-'.$child->id,
		'item_id' => $child->id,
        'iconCls'=> $cls,
        'qtip'  => $child->describ,
        'leaf'  => FALSE,
        'link'  => (int)$child->isLink(),
		'isServer'  => (int)$child->isServer(),
        'mtype' => $child->materialsType,
        'disabled' => ($right && !$nocatselect)?FALSE:TRUE,
		'date'  => $child->dat,
		'mtype_name' => $mtype_name,
    );
}