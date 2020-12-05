<?php
namespace Cetera;
/**
 * Fastsite CMS 3 
 * 
 * Список типов материалов
 *
 * @package FastsiteCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/

include('common_bo.php');

$data = array();

if (isset($_GET['mode']) && $_GET['mode'] == 'pages') {
	if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);
	$data = $application->getConn()->fetchAll('SELECT page FROM types_fields group by page order by page');    
} 
elseif (isset($_GET['mode']) && $_GET['mode'] == 'fields') {
	
	if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);
	
	$fields_over = array();	
	if (isset($_GET['catalog']))
	{
		$r = $application->getConn()->fetchAll('SELECT field_id, force_show, force_hide FROM types_fields_catalogs WHERE catalog_id=? and type_id=?', array((int)$_GET['catalog'],(int)$_REQUEST['type_id']));
		foreach($r as $f) $fields_over[$f['field_id']] = $f;
	}	

    $r = $application->getConn()->query('
		SELECT field_id as id, name, describ, fixed, shw, required, type, default_value, editor_user, editor, pseudo_type, len, page, tag 
		FROM types_fields
		WHERE id='.(int)$_REQUEST['type_id'].' ORDER by tag');
   
   while ($f = $r->fetch()) {
		$f['id'] = (int)$f['id'];
		
        if (($f['type']==FIELD_LINK || $f['type']==FIELD_LINKSET) && $f['len'] > 0) {
            try {
              $c = Catalog::getById($f['len']);
              if ($c) $f['path'] = $c->getPath()->implode();
            } catch (\Exception $e) {}
        }
        if ($f['pseudo_type']) $f['type'] = $f['pseudo_type'];
        $f['type_name'] = isset($l_field_types[$f['type']])?$l_field_types[$f['type']]:null;
        $f['fixed'] = (int)$f['fixed'];
        $f['shw'] = (int)$f['shw'];
        $f['required'] = (int)$f['required'];
        if ($f['type'] == FIELD_ENUM) {
            $alias = $application->getConn()->fetchColumn('select alias from types where id=?', array((int)$_REQUEST['type_id']));
            $g = $application->getConn()->fetchArray("SHOW COLUMNS FROM $alias LIKE '".$f['name']."'");
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
		
		$f['describDisplay'] = $application->decodeLocaleString($f['describ']);

        $data[] = $f;
    }
    
} 
else {

    if (isset($_REQUEST['empty'])) $data[] = [
        'id'      => 0,
		'describDisplay' => '- '.$translator->_('Без материалов').' -',
        'describ' => '- '.$translator->_('Без материалов').' -'
    ]; 

    $query = 'SELECT * FROM types WHERE 1=1';
    if (isset($_REQUEST['exclude'])) $query .= ' and id <> '.(int)$_REQUEST['exclude'];
    if (isset($_REQUEST['linkable'])) $query .= ' and id not in ('.User::TYPE.','.Catalog::TYPE.')'; 

    $query .= ' ORDER by fixed desc, alias';
    $r = $application->getConn()->query($query);
    while ($f = $r->fetch()) {
        $f['id'] = (int)$f['id'];
        $f['fixed'] = (int)$f['fixed'];
        if (!$f['describ']) $f['describ'] = $f['alias'];
		$f['describDisplay'] = $application->decodeLocaleString($f['describ']);
        $data[] = $f;
    }
    
}

echo json_encode([
    'success' => true,
    'rows'    => $data
]);