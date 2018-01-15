<?php
namespace Cetera;

include('common_bo.php');

$t = Application::getInstance()->getTranslator();

if ($_GET['action'] == 'clear') {
	Util::clearAllCache();
}

if ($_GET['action'] == 'delete') {
	Util::delTree(WWWROOT.ImageTransform::PREFIX, false);
	Util::delTree(IMAGECACHE_DIR, false);
	Util::delTree(FILECACHE_DIR, false);
	Util::delTree(TWIG_CACHE_DIR, false);
}

if ($_GET['action'] == 'twig') {
	Util::delTree(TWIG_CACHE_DIR, false);
}

$imageTransform = Util::directorySize(WWWROOT.ImageTransform::PREFIX);

$data = [
	[
		'name' => $t->_('Всего'),
		'size' => Util::hbytes(Util::directorySize(CACHE_DIR) + $imageTransform),
	],
	[
		'name' => $t->_('Резайз картинок'),
		'size' => Util::hbytes(Util::directorySize(IMAGECACHE_DIR) + $imageTransform),
	],	
	[
		'name' => $t->_('Файловый кэш объектов'),
		'size' => Util::hbytes(Util::directorySize(FILECACHE_DIR)),
	],		
	[
		'name' => $t->_('Twig'),
		'size' => Util::hbytes(Util::directorySize(TWIG_CACHE_DIR)),
	],	
];

echo json_encode(array(
    'success' => true,
    'rows'    => $data,
));