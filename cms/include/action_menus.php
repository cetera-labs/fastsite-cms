<?php
namespace Cetera;
/**
 * Cetera CMS 3 
 * 
 * AJAX-backend действия с разделами 
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/

include_once('common_bo.php');

$res = array(
    'success' => false,
);

if ($_REQUEST['action'] == 'create') {

    $m = Menu::create($_REQUEST['alias'], $_REQUEST['name']);
    $res['id'] = $m->id;
    
}

if ($_REQUEST['action'] == 'delete') {

    $m = Menu::getById($_REQUEST['id']);
    $m->delete();
    
}

if ($_REQUEST['action'] == 'rename') {

    $m = Menu::getById($_REQUEST['id']);
    $m->_name = $_REQUEST['name'];
    $m->_alias = $_REQUEST['alias'];
    $m->save();
    
}

if ($_REQUEST['action'] == 'save') {

    $data = array();
    if (is_array($_REQUEST['children'])) foreach ($_REQUEST['children'] as $c)
	{
        list($t, $id, $table, $type) = explode('-', $c);
		
		if ($t == 'url')
		{
			$data[] = array(
				'url'  => str_replace('%DASH%','-',$id),
				'name' => str_replace('%DASH%','-',$type)
			);
		}
		else 
		{
		
			if ($t != 'material') {
				$table = Catalog::TABLE;
				$type  = Catalog::TYPE;
			}
			$data[] = array(
				'id'    => $id,
				'table' => $table,
				'type'  => $type
			);
		
		}
    }
    
    $m = Menu::getById($_REQUEST['id']);
    $m->_data = $data;
    $m->save();
}

$res['success'] = true;

echo json_encode($res); 