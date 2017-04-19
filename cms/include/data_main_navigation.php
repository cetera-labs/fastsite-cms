<?php
namespace Cetera;
 
include('common_bo.php');

header('Content-type: application/json');

$menu = array();

$menu = array();

foreach ($application->getBo()->getModules() as $id => $component) {

	if (!$component['ext6_compat']) continue;

	$component['id'] = $id;
	$component['text'] = $component['name'];
	unset($component['name']);
	
	if (!isset($component['iconCls'])) $component['iconCls'] = 'tab-'.$component['id'];
	
	if (!isset($component['leaf'])) $component['leaf'] = true;
	
	$menu[] = $component;               

}

echo json_encode(array_values($menu));