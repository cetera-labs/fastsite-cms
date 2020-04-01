<?php
/** Каталог установки CMS */
if (!defined('CMS_DIR')) {
    define('CMS_DIR', 'cms' );
}
	
if (!defined('DOCROOT')) {
	if (isset($_SERVER['DOCUMENT_ROOT'])) {
        $dr = rtrim($_SERVER['DOCUMENT_ROOT'],'/').'/';
	}
	else {
		$dr = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'.CMS_DIR.'/', 1 )).'/';
	}	
	define('DOCROOT', $dr );
}

if (file_exists(DOCROOT.'../vendor/cetera-labs/cetera-cms')) {
	define('COMPOSER_INSTALL', true);
	define('VENDOR_PATH', DOCROOT.'../vendor');
    define('CMSROOT', DOCROOT.'../vendor/cetera-labs/cetera-cms/cms/' );
    define('LIBRARY_PATH', 'library');
}
else {
    define('LIBRARY_PATH', 'library');
	define('COMPOSER_INSTALL', false);
	define('VENDOR_PATH', DOCROOT.'/'.LIBRARY_PATH.'/vendor');
	
	if (file_exists(DOCROOT.LIBRARY_PATH.'/library.php')) {
		include_once(DOCROOT.LIBRARY_PATH.'/library.php');
	} else {
		define('LIBRARY_VERSION', 1);
	}	
    define('CMSROOT', str_replace('include','',__DIR__) );
}