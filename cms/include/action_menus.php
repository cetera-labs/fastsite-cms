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

    $d = json_decode($_REQUEST['children'], true);    
    $m = Menu::getById($_REQUEST['id']);
    $m->_data = parse_data($d);
    $m->save();
}

$res['success'] = true;

echo json_encode($res); 

function parse_data($d) {
    $data = [];
    foreach ($d as $c) {
        $f = explode('-', $c['data']);
        //list($t, $id, $table, $type)         
		
		if ($f[0] == 'url') {
			$data[] = [
				'url'  => str_replace('%DASH%','-',$f[1]),
				'name' => str_replace('%DASH%','-',$f[3]),
                'children' => parse_data($c['children']),
			];
		}
		else {
			if ($f[0] != 'material') {
				$f[2] = Catalog::TABLE;
				$f[3] = Catalog::TYPE;
			}
			$data[] = array(
				'id'    => $f[1],
				'table' => $f[2],
				'type'  => $f[3],
			);
		}
    }

     return $data;
}