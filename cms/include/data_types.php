<?php
namespace Cetera;
/**
 * Cetera CMS 3 
 * 
 * Список типов материалов
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/

include('common_bo.php');

$data = array();

if (isset($_GET['mode']) && $_GET['mode'] == 'pages') {
	
	if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

    $r = fssql_query('SELECT page FROM types_fields group by page order by page ');
    while ($f = mysql_fetch_assoc($r)) {
        $data[] = $f;
    }
    
} elseif (isset($_GET['mode']) && $_GET['mode'] == 'fields') {
	
	if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);
	
	$fields_over = array();	
	if (isset($_GET['catalog']))
	{
		$r = fssql_query('SELECT field_id, force_show, force_hide FROM types_fields_catalogs WHERE catalog_id='.(int)$_GET['catalog'].' and type_id='.(int)$_REQUEST['type_id']);
		while($f = mysql_fetch_assoc($r)) $fields_over[$f['field_id']] = $f;
	}	

    $r = fssql_query('
		SELECT field_id as id, name, describ, fixed, shw, required, type, default_value, editor_user, editor, pseudo_type, len, page, tag 
		FROM types_fields
		WHERE id='.(int)$_REQUEST['type_id'].' 
		ORDER by tag');
   
   while ($f = mysql_fetch_assoc($r))
	{
        if (($f['type']==FIELD_LINK || $f['type']==FIELD_LINKSET) && $f['len'] > 0) {
            try {
              $c = Catalog::getById($f['len']);
              if ($c) $f['path'] = $c->getPath()->implode();
            } catch (\Exception $e) {}
        }
        if ($f['pseudo_type']) $f['type'] = $f['pseudo_type'];
        $f['type_name'] = $l_field_types[$f['type']];
        $f['fixed'] = (int)$f['fixed'];
        $f['shw'] = (int)$f['shw'];
        $f['required'] = (int)$f['required'];
        if ($f['type'] == FIELD_ENUM) {
            $r1 = fssql_query("select alias from types where id=".(int)$_REQUEST['type_id']);
            $alias = mysql_result($r1,0);
            $r1 = fssql_query("SHOW COLUMNS FROM $alias LIKE '".$f['name']."'");
            $g = mysql_fetch_row($r1);
            $f['len'] = substr($g[1],5,strlen($g[1])-6);
        }
		
		if (isset($_GET['catalog']))
		{
			$f['force_show'] = false;
			$f['force_hide'] = false;
			if (isset($fields_over[$f['id']])) {
				$f['force_show'] =  (boolean)$fields_over[$f['id']]['force_show'];
				$f['force_hide'] =  (boolean)$fields_over[$f['id']]['force_hide'];
			}	
		}
		
        $data[] = $f;
    }
    
} else {

    if ($_REQUEST['empty']) $data[] = array(
        'id'      => 0,
        'describ' => '- '.$translator->_('Без материалов').' -'
    ); 

    $query = 'SELECT * FROM types WHERE 1=1';
    if ($_REQUEST['exclude']) $query .= ' and id <> '.(int)$_REQUEST['exclude'];
    if ($_REQUEST['linkable']) $query .= ' and id not in ('.User::TYPE.','.Catalog::TYPE.')'; 

    $query .= ' ORDER by fixed desc, alias';
    $r = fssql_query($query);
    while ($f = mysql_fetch_assoc($r)) {
        $f['id'] = (int)$f['id'];
        $f['fixed'] = (int)$f['fixed'];
        if (!$f['describ']) $f['describ'] = $f['alias'];
        $data[] = $f;
    }
    
}


echo json_encode(array(
    'success' => true,
    'rows'    => $data
));