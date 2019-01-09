<?php
/**
 * Cetera CMS 3 
 * 
 * Определение констант  
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/

/** Версия */
define('VERSION', '3.58.18');

/** Название продукта */
define('APP_NAME', 'Cetera CMS');

/** Cайт производителя */
define('APP_WWW', 'www.cetera.ru');

define('PHP_VER', '7.0.0');
define('MYSQL_VER', '5.0.3');
define('LIBRARY_VERSION_REQ', 10);

define("DISTRIB_HOST", 'https://cms.cetera.ru/');
define("DISTRIB_INFO", DISTRIB_HOST.'info.json'); 
define("PLUGINS_INFO", DISTRIB_HOST.'plugins/plugins.php'); 
define("THEMES_INFO",  DISTRIB_HOST.'themes/themes.php'); 
define("PING_URL",     DISTRIB_HOST.'net/ping.php'); 
define("DISTRIB_FILE", 'cms.zip'); 
define("LIBRARY_FILE", 'library.zip'); 

define("TRANSLATIONS", 'lang'); 

define("UPGRADE_FILE",   'upgrade.zip');
define("UPGRADE_SCRIPT", 'upgrade.php');
define("INSTALL_SCRIPT", 'install.php');

if (isset($_SERVER['HTTP_USER_AGENT'])) {
	$agt = strtolower($_SERVER['HTTP_USER_AGENT']);
	define('IS_IE', strpos($agt, 'msie') !== false && !(strpos($agt, 'opera') !== false));
}

// ----------- файлы и каталоги -----------------

/** Каталог установки CMS */
if (!defined('CMS_DIR'))
    //define('CMS_DIR', substr($_SERVER['SCRIPT_NAME'], 1, strpos($_SERVER['SCRIPT_NAME'],'/',1) - 1));
    define('CMS_DIR', 'cms' );
	
/** Абсолютный путь к корню CMS */
//define('CMSROOT', $_SERVER['DOCUMENT_ROOT'].'/'.CMS_DIR.'/');
define('CMSROOT', str_replace('include','',__DIR__) );

/** Абсолютный путь DOCUMENT ROOT */
//define('DOCROOT', $_SERVER['DOCUMENT_ROOT'].'/');
if (!defined('DOCROOT')) {
	if (isset($_SERVER['DOCUMENT_ROOT'])) {
		$dr = $_SERVER['DOCUMENT_ROOT'].'/';
	}
	else {
		$dr = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'.CMS_DIR.'/', 1 )).'/';
	}	
	define('DOCROOT', $dr );
}
define('WWWROOT', DOCROOT );

define('PREFS_FILE',       DOCROOT.'.prefs');
define('PREFS_FILE_LOCAL', DOCROOT.'.prefs.local');

define ('DB_SCHEMA', CMSROOT.'.dbschema/core.xml');
define ('DB_DATA', CMSROOT.'.dbschema/core.sql');
define ('DEFAULT_THEME', CMSROOT.'.dbschema/.templates.zip');
define ('TEMPLATES_DIR', DOCROOT.'.templates');
define ('TWIG_TEMPLATES_PATH', 'design');

define ('BOOTSTRAP_SCRIPT', 'bootstrap.php');

/** Шаблон по умолчанию */
define ('DEFAULT_TEMPLATE', 'default.php');

define('PLUGIN_DIR',      'plugins');
define('PLUGIN_CONFIG',   'config.php');
define('PLUGIN_INFO',     'info.json');
define('PLUGIN_CLASSES',  'classes');
define('PLUGIN_DB_SCHEMA','schema.xml');
define('PLUGIN_INSTALL',  'install.php');

define('THEME_DIR',    'themes');
define('THEME_DEFAULT_DIR', 'default');
define('THEME_INFO',   'info.json');
define('THEME_CONTENT_INFO', 'content_info.json');
define('THEME_DB_SCHEMA', 'schema.xml');
define('THEME_DB_DATA','data.sql');
define('THEME_INSTALL',  'install.php');

define('PLUGIN_MATH_DIR', DOCROOT.PLUGIN_DIR);

define('CACHE_DIR', DOCROOT.'.cache');
define('FILECACHE_DIR', CACHE_DIR.'/filecache');
define('IMAGECACHE_DIR', CACHE_DIR.'/images');
define('TWIG_CACHE_DIR', CACHE_DIR.'/twig');
define('USER_UPLOADS_DIR', DOCROOT.'uploads');
define('USER_UPLOAD_PATH', '/uploads/');

if (substr_count(__DIR__, 'vendor'.DIRECTORY_SEPARATOR.'cetera-labs'.DIRECTORY_SEPARATOR.'cetera-cms') > 0) {
	define('COMPOSER_INSTALL', true);
	define('LIBRARY_PATH', 'vendor/cetera-labs/library');
	define('VENDOR_PATH', DOCROOT.'/vendor');
}
else {
	define('COMPOSER_INSTALL', false);
	define('LIBRARY_PATH', 'library');
	define('VENDOR_PATH', DOCROOT.'/'.LIBRARY_PATH.'/vendor');
	
	if (file_exists(DOCROOT.LIBRARY_PATH.'/library.php')) {
		include_once(DOCROOT.LIBRARY_PATH.'/library.php');
	} else {
		define('LIBRARY_VERSION', 1);
	}	
}

// ----------- // файлы и каталоги -----------------

define('PREVIEW_PREFIX', '_preview_');

define('REMEMBER_ME_SECONDS', 3600*24*365);
define('AUTH_INACTIVITY_SECONDS', 3600*24);
define('SESSION_NAMESPACE', 'CeteraCMS');

// материалы
define('MATH_PUBLISHED',   1);
define('MATH_SEND',        2);
define('MATH_ADDED',       4);
define('MATH_DELETED',     8);
define('MATH_SHOW_FUTURE', 16);
define('PUBLISHED', 'type&'.MATH_PUBLISHED.'='.MATH_PUBLISHED.' and (dat<=NOW() or dat IS NULL or type&'.MATH_SHOW_FUTURE.'='.MATH_SHOW_FUTURE.')');

define('SITE_USER_ENABLED',3);

define('USER_DELETED', 4);

define('MENU_SITE', 100);
define('MENU_SERVICE', 200);
define('MENU_PLUGINS', 300);
define('MENU_INTERACTIVES', MENU_SITE);

define('ADMIN_ID', 1); // ID администратора

// Группы
define('GROUP_ALL', 	      -1);  // Все пользователи
define('GROUP_BACKOFFICE',  -2);  // Пользователи БО
define('GROUP_ADMIN',       -3);  // Администраторы
define('GROUP_EXTERNAL',    -4);  // Пользователи внешние
define('GROUP_ANONYMOUS',   -5);  // Анонимы
define('GROUP_LOCAL',       -6);  // Зарегистрированные

define('USER_OPENID',        -4);  // Пользователи OpenID
define('USER_ANONYMOUS',     -5);  // Анонимы
define('USER_FACEBOOK',      -6);  // Пользователи FB
define('USER_TWITTER',       -7);  // Пользователи twitter
define('USER_VK',            -8);  // Пользователи vk
define('USER_GOOGLE',        -9);  // Пользователи ggl
define('USER_ODNOKLASSNIKI', -10);
define('USER_LJ',            -11);  

// Разделы
define('CATALOG_VIRTUAL_HIDDEN', -1); // виртуальный каталог, где хранятся материалы из поля "Набор материалов"
define('CATALOG_VIRTUAL_USERS', -2); // виртуальный каталог, где хранятся пользователи

// -------- Permissions --------------

/** Работа со своими материалами */
define('PERM_CAT_OWN_MAT',   	5);

/** Работа с материалами других авторов */
define('PERM_CAT_ALL_MAT',    	6);

/** Изменение свойств раздела */
define('PERM_CAT_ADMIN',    	7);

/** Возможность видеть раздел */
define('PERM_CAT_VIEW',      	8);

/** Публикация материалов */
define('PERM_CAT_MAT_PUB', 	    9);

// -------- // Permissions --------------

// ---------- Field types ------------------
define('FIELD_TEXT', 		1);
define('FIELD_HTML', 		2); // deprecated
define('FIELD_LONGTEXT',	2);
define('FIELD_INTEGER', 	3);
define('FIELD_FILE', 		4);
define('FIELD_DATETIME',	5);
define('FIELD_LINK', 		6);
define('FIELD_LINKSET', 	7);
define('FIELD_MATSET', 		8);
define('FIELD_BOOLEAN', 	9);
define('FIELD_ENUM', 		10);
define('FIELD_FORM', 	  	11);
define('FIELD_MATERIAL',	13);
define('FIELD_HUGETEXT',	14);
define('FIELD_DOUBLE',   	15);
define('FIELD_LINKSET2',    16);

//define('HLINK_PLAIN', 		1);
//define('HLINK_STRUCTURE',	2);

define('PSEUDO_FIELD_FILESET', 1001);
define('PSEUDO_FIELD_LINK_USER', 1003);
define('PSEUDO_FIELD_LINKSET_USER', 1005);
define('PSEUDO_FIELD_TAGS', 1006);

define('PSEUDO_FIELD_LINK_CATALOG', 1008);
define('PSEUDO_FIELD_LINKSET_CATALOG', 1007);
define('PSEUDO_FIELD_CATOLOGS', 1007);

define('EDITOR_DEFAULT', 			    0);
define('EDITOR_USER', 				   -1);
define('EDITOR_TEXT_DEFAULT', 		1);
define('EDITOR_INTEGER_DEFAULT', 	3);
define('EDITOR_FILE_DEFAULT', 		4);
define('EDITOR_DATETIME_DEFAULT', 5);
define('EDITOR_LINK_DEFAULT', 		6);
define('EDITOR_LINKSET_DEFAULT', 	7);
define('EDITOR_MATSET_DEFAULT', 	8);
define('EDITOR_BOOLEAN_DEFAULT', 	9);
define('EDITOR_ENUM_DEFAULT', 		10);
define('EDITOR_LINK_FORM', 			  11);
define('EDITOR_TEXT_ALIAS', 		  14);
define('EDITOR_TEXT_AREA', 		 	  16);
define('EDITOR_HIDDEN', 			    17);
define('EDITOR_BOOLEAN_RADIO', 		18);
define('EDITOR_MATSET_FILES',		  19);
define('EDITOR_LINKSET_CHECKBOX', 20);
define('EDITOR_MATSET_IMAGES',		21);
define('EDITOR_LINK_USER',    	  22);
define('EDITOR_LINKSET_USER', 		23);
define('EDITOR_MATERIAL_DEFAULT',	24);
define('EDITOR_DOUBLE_DEFAULT',   25);
define('EDITOR_TAGS_DEFAULT',     26);
define('EDITOR_TEXT_PASSWORD', 		27);
define('EDITOR_TEXT_EMAIL', 		  28);
define('EDITOR_DHTML_CKEDITOR',		29);
define('EDITOR_TEXT_CKEDITOR',		29);
define('EDITOR_LINKSET_CATALOG', 	31);
define('EDITOR_FILE_IMAGE', 	    32);
define('EDITOR_CKEDITOR_SMALL',		33);
define('EDITOR_TEXT_CKEDITOR_SMALL',33);
define('EDITOR_MATSET_RICH', 	    34);
define('EDITOR_LINK_CATALOG',    	35);
define('EDITOR_LINKSET2_DEFAULT', 	36);
define('EDITOR_LINKSET_EDITABLE', 	37);
// ---------- // Field types ---------------

define('DEBUG_SQL'      , 8);
define('DEBUG_ERROR_PHP', 3);
define('DEBUG_CACHE'    , 10);
define('DEBUG_GEO'      , 11);
define('DEBUG_COMMON'   , 5);
define('DEBUG_BANNER'   , 12);

define('PASSWORD_NOT_CHANGED', '***not_changed***');
//------------

define('PAGE_HEIGHT', 580);
define('LABEL_WIDTH', 180);
define('FIELD_WIDTH', 619);

define('EVENT_CORE_MATERIAL_COPY', 'CORE_MATERIAL_COPY');
define('EVENT_CORE_MATERIAL_BEFORE_SAVE', 'CORE_MATERIAL_BEFORE_SAVE');
define('EVENT_CORE_MATERIAL_AFTER_SAVE', 'CORE_MATERIAL_AFTER_SAVE');
define('EVENT_CORE_USER_REGISTER', 'CORE_USER_REGISTER');
define('EVENT_CORE_USER_RECOVER', 'CORE_USER_RECOVER');
define('EVENT_CORE_BO_LOGIN_OK', 1);
define('EVENT_CORE_BO_LOGIN_FAIL', 2);
define('EVENT_CORE_LOG_CLEAR', 3);
define('EVENT_CORE_DIR_CREATE', 4);
define('EVENT_CORE_DIR_EDIT', 5);
define('EVENT_CORE_DIR_DELETE', 6);
define('EVENT_CORE_MATH_CREATE', 7);
define('EVENT_CORE_MATH_EDIT', 8);
define('EVENT_CORE_MATH_DELETE', 9);
define('EVENT_CORE_MATH_PUB', 10);
define('EVENT_CORE_MATH_UNPUB', 11);
define('EVENT_CORE_USER_PROP', 12);