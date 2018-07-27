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
 
include('common_bo.php');

$data = array();

$s = $application->getSession();
$s->explorer_folder = $_REQUEST['path'];

if (!isset($s->explorer_history) || !is_array($s->explorer_history))
    $s->explorer_history = array();
    
if (!in_array($_REQUEST['path'], $s->explorer_history))
    array_unshift($s->explorer_history, $_REQUEST['path']);
$s->explorer_history = array_slice($s->explorer_history, 0, 6);

$path = rtrim(str_replace('|','/',$_REQUEST['path']),'/');
$path = str_replace(DOCROOT, '', $path);

$iterator = new \DirectoryIterator(DOCROOT.$path);
if ($_REQUEST['extension']) {
    $ext = explode(',',$_REQUEST['extension']);
} else $ext = false;
foreach ($iterator as $fileinfo) {
    if (!$fileinfo->isFile()) continue;
    
    $path_parts = pathinfo($fileinfo->getPathname());
    if ($ext) if (!in_array(strtolower($path_parts['extension']),$ext)) continue;
    
    $name = htmlentities( $fileinfo->getFilename(), ENT_QUOTES, 'utf-8', FALSE);
    if (!$name) continue;
    
    $info = array(
        'name'     => $name,
        'size'     => $fileinfo->getSize(),
        'lastmod'  => $fileinfo->getMTime()
    );
    
    $path_parts['extension'] = strtolower($path_parts['extension']);
	if ($path_parts['extension'] == 'svg') {
		$info['type'] = 99;
	}
    elseif ($path_parts['extension'] == 'jpg' || $path_parts['extension'] == 'gif' || $path_parts['extension'] == 'png') {
        $size = getimagesize($fileinfo->getPathname());
        if ($size) {
            $info['width'] = $size[0];
            $info['height'] = $size[1];
            $info['type'] = $size[2];
        }
    }
    
    try {
        json_encode($info);
    } catch (\Exception $e) {
        continue;
    }
    
    $data[] = $info;
}

function cmp($a, $b)
{
    $_a = strtolower($a['name']);
    $_b = strtolower($b['name']);
    if ($_a == $_b) return 0;
    return ($_a < $_b) ? -1 : 1;
}

usort($data, "Cetera\\cmp");

echo json_encode(array(
    'success' => true,
    'rows'    => $data
));
