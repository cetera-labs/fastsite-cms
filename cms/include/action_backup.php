<?php
namespace Cetera;

include_once('common_bo.php');

$res = array(
    'success' => false,
);

if (!$user->allowAdmin()) throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

if ($_REQUEST['action'] == 'backup') {

	if ($_REQUEST['section'] > 0) {
		$section = \Cetera\Catalog::getById($_REQUEST['section']);
	}
	else {
		$section = \Cetera\Catalog::getRoot();
	}
	if (!file_exists(CACHE_DIR.'/backup')) {
		mkdir(CACHE_DIR.'/backup');
	}
	$file = 'backup_'.$section->id.'_'.time().'.xml';
	\Cetera\Backup\Content::backup( CACHE_DIR.'/backup/'.$file, $section );
	$res['file'] = $file;
}

if ($_REQUEST['action'] == 'download') {
	
	$file = $_REQUEST['file'];
	
	header('Content-Type: application/octet-stream');
	header("Content-Transfer-Encoding: Binary"); 
	header("Content-disposition: attachment; filename=\"" . basename($file) . "\""); 
	readfile(CACHE_DIR.'/backup/'.$file);
	die();
	
}

if ($_REQUEST['action'] == 'restore') {
	
	if ($_REQUEST['section'] > 0) {
		$section = \Cetera\Catalog::getById($_REQUEST['section']);
	}
	else {
		$section = \Cetera\Catalog::getRoot();
	}
	
	\Cetera\Backup\Content::restore( WWWROOT.$_REQUEST['file'], $section );
	
	$res['message'] = $translator->_('Импорт завершен успешно.');
	
}

$res['success'] = true;

echo json_encode($res); 
