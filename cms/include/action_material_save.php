<?php
namespace Cetera;

/**
 * Fastsite CMS 3 
 * 
 * AJAX-backend. Сохранение материала.
 *
 * @package FastsiteCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
  
include('common_bo.php');

$res = array(
    'success' => false,
    'errors'  => array()
);

$math = $_REQUEST['table'];

$od = ObjectDefinition::findByTable($_REQUEST['table']);
if (!isset($_POST['id'])) {
	$m = DynamicFieldsObject::fetch($_POST, $od);
}
else {
	if (isset($_POST['publish']) && !$_POST['publish']) {
		unset($_POST['publish']);
	}
	
	$m = DynamicFieldsObject::getByIdType($_POST['id'], $od);
	$m->setFields($_POST);
}

$m->save(false);
$m->lock($user->id);

$res['success'] = TRUE; 
$res['id'] = $m->id;
if ((int)$_REQUEST['catalog_id'] >= 0) $res['alias'] = $m->alias;

echo json_encode($res); 