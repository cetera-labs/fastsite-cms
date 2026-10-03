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

// CMS ставится только через composer: DOCROOT/../vendor/cetera-labs/cetera-cms
if (!file_exists(DOCROOT.'../vendor/cetera-labs/cetera-cms')) {
    header('HTTP/1.1 500 Internal Server Error');
    die('Fastsite CMS is not installed: run composer install in the site root (../vendor/cetera-labs/cetera-cms not found)');
}

define('VENDOR_PATH', DOCROOT.'../vendor');
define('CMSROOT', DOCROOT.'../vendor/cetera-labs/cetera-cms/cms/' );

/** @deprecated Всегда true: установка без composer (каталог library/) не поддерживается. Будет удалена в 4.0 */
define('COMPOSER_INSTALL', true);

/** Каталог library/ в корне сайта, оставшийся от установки без composer: закрыт для файлового менеджера */
define('LIBRARY_PATH', 'library');