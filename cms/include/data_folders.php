<?php
namespace Cetera;
/**
 * Cetera CMS 3
 * 
 * Дерево каталогов   
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
include_once('common_bo.php');

$path = rtrim(str_replace('|','/',$_REQUEST['node']),'/');

if (!$path) {
    $walk = $application->getSession()->explorer_folder;
    if (!$walk) $walk = str_replace('/','|',$_GET['defaultExpand']);
    $walk = explode('|', trim($walk,'|'));
} 
else {
	$walk =[];
}

echo json_encode(read_dir($path, $walk));

function read_dir($path, $walk) {
    global $user;
    
    $nodes = array();
    
    $expand = array_shift($walk);

    $iterator = new \DirectoryIterator(DOCROOT.$path);
    foreach ($iterator as $fileinfo) {
        if ($fileinfo->isDot() || !$fileinfo->isDir()) continue;
        if ( $path == '' && in_array( $fileinfo->getFilename(), [CMS_DIR,LIBRARY_PATH,'.cache','imagetransform'] ) ) continue;
        $localpath = $path.'/'.$fileinfo->getFilename().'/';
        if (!$user->allowFilesystem($localpath)) continue;
        
        $children = false;
        if ($expand == $fileinfo->getFilename()) 
            $children = read_dir($path.'/'.$fileinfo->getFilename(), $walk);
        
		$w = $fileinfo->isWritable();// && (substr( $localpath, 0, strlen(USER_UPLOAD_PATH) ) == USER_UPLOAD_PATH);
		
        $nodes[] = array(
            'text'     => $fileinfo->getFilename(),
            'id'       => str_replace('/','|',$localpath),
            'iconCls'  => $w?'tree-folder-visible':'tree-folder-locked',
            'qtip'     => '',
            'leaf'     => FALSE,
            'disabled' => FALSE,
            'expanded' => is_array($children),
            'readOnly' => !$w,
            'children' => $children
        );
    }
    
    usort($nodes, "Cetera\\cmp");
    return $nodes;
}

function cmp($a, $b)
{
    $_a = strtolower($a['text']);
    $_b = strtolower($b['text']);
    if ($_a == $_b) return 0;
    return ($_a < $_b) ? -1 : 1;
}
