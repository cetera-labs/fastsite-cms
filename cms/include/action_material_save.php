<?php
namespace Cetera;

/**
 * Cetera CMS 3 
 * 
 * AJAX-backend. Сохранение материала.
 *
 * @package CeteraCMS
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

$handler = $application->getConn()->fetchColumn("SELECT handler FROM types WHERE alias = ?", array($math), 0);

$r = fssql_query("SELECT handler FROM types WHERE alias = '$math'");
if ($handler) {
    if (substr($handler, -4) != '.php') $handler .= '.php';
    if ($handler && file_exists(PLUGIN_MATH_DIR.'/'.$handler)) 
        include(PLUGIN_MATH_DIR.'/'.$handler);
}

$od = ObjectDefinition::findByTable($_REQUEST['table']);
if (!$_POST['id'])
{
	$m = DynamicFieldsObject::fetch($_POST, $od);
}
else
{
	$m = DynamicFieldsObject::getByIdType($_POST['id'], $od);
	$m->setFields($_POST);
}

if (function_exists('on_before_save')) on_before_save();

$m->save(false);
$m->lock($user->id);

if (function_exists('on_after_save')) on_after_save();
if ((int)$_REQUEST['publish'] && function_exists('on_publish')) on_publish();
        
$res['success'] = TRUE; 
$res['id'] = $m->id;
if ((int)$_REQUEST['catalog_id'] >= 0) $res['alias'] = $m->alias;

echo json_encode($res); 