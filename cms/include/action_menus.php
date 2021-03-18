<?php
namespace Cetera;
/**
 * Fastsite CMS 3 
 * 
 * AJAX-backend действия с разделами 
 *
 * @package FastsiteCMS
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
        $f = explode('-', $c);
        //list($t, $id, $table, $type)         
		
		if ($f[0] == 'url') {
			$data[] = array(
				'url'  => str_replace('%DASH%','-',$f[1]),
				'name' => str_replace('%DASH%','-',$f[3])
			);
		}
		else {
		
			if ($f[0] != 'material') {
				f[2] = Catalog::TABLE;
				f[3]  = Catalog::TYPE;
			}
			$data[] = array(
				'id'    => $f[1],
				'table' => $f[2],
				'type'  => f[3],
			);
		
		}
    }
    
    $m = Menu::getById($_REQUEST['id']);
    $m->_data = $data;
    $m->save();
}

$res['success'] = true;

echo json_encode($res); 