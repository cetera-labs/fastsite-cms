<?php
/**
 * Константы ядра для статического анализа (CCTM-1604).
 *
 * На сайте их задаёт cms/include/path_detect.php по реальному расположению файлов,
 * а затем cms/include/constants.php достраивает остальные. Анализатору настоящие пути
 * не нужны, поэтому базовые константы объявляются заглушками, а дальше подключается
 * тот же constants.php — так список констант не расходится с кодом.
 */

define('DOCROOT', __DIR__ . '/site/');
define('CMSROOT', dirname(__DIR__) . '/cms/');
define('CMS_DIR', 'cms/');
define('LIBRARY_PATH', 'library');
define('VENDOR_PATH', dirname(__DIR__) . '/vendor/');
define('COMPOSER_INSTALL', true);

require_once CMSROOT . 'include/constants.php';
