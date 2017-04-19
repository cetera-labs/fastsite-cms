<?php
namespace Cetera;
/**
 * Cetera CMS 3
 * 
 * Список файлов   
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/

include_once('common_bo.php');

$nodes = array();

if ($_REQUEST['node'] == 'root')
{

	foreach($application->getRegisteredWidgets() as $w)
	{
		if (!is_subclass_of($w['class'],'Cetera\Widget\Templateable')) continue;
		
		$nodes[] = array(
			'text'     => $w['name'].' <span class="tree-alias">'.$w['describ'].'</span>',
			'id'       => $w['class'],
			//'iconCls'  => 'tree-folder-visible',
			'qtip'     => $w['describ'],
			'leaf'     => false,
			'expanded' => false,
			'children' => getTemplates($w['class'])
		);	
	}

}
else
{
	
	$nodes = getTemplates($_REQUEST['node']);
	
}

header('Content-type: application/json');
echo json_encode($nodes);

function getTemplates($class)
{	
	$children = array();
	$templates = $class::getTemplates();
	foreach ($templates as $t)
	{
		$text = $t['name'];
		if ($t['theme']) $text .= ' ['.$t['theme'].']';
		$children[] = array(
			'id'       => $class.'\\'.$t['name'].'['.$t['theme'].']',
			'text'     => $text,
			'path'     => $t['path'],
			'writable' => $t['writable'],
			'theme'    => $t['theme'],
			'name'     => $t['name'],
			'folder'   => $t['folder'],
			'iconCls'  => 'f-fa fa-file-code-o',
			'leaf'     => true,
		);
	}
	return $children;
}