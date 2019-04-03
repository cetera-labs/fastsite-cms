<?php
/** Каталог установки CMS */
if (!defined('CMS_DIR')) {
    define('CMS_DIR', 'cms' );
}
	
/** Абсолютный путь DOCUMENT ROOT */
if (!defined('DOCROOT')) {
	$dr = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'.CMS_DIR.'/', 1 )).'/';
	define('DOCROOT', $dr );
}

if (file_exists(__DIR__.'/../../../vendor/cetera-labs/cetera-cms')) {
	define('COMPOSER_INSTALL', true);
	define('VENDOR_PATH', __DIR__.'/../../../vendor');
    define('CMSROOT', __DIR__.'/../../../vendor/cetera-labs/cetera-cms/cms/' );
}
else {
	define('COMPOSER_INSTALL', false);
	define('VENDOR_PATH', DOCROOT.'/'.LIBRARY_PATH.'/vendor');
	
	if (file_exists(DOCROOT.LIBRARY_PATH.'/library.php')) {
		include_once(DOCROOT.LIBRARY_PATH.'/library.php');
	} else {
		define('LIBRARY_VERSION', 1);
	}	
    define('CMSROOT', str_replace('include','',__DIR__) );
}