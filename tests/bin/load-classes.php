<?php
/**
 * Загружает переданные классы по одному (см. tests/Smoke/ClassLoadTest.php).
 * Использование: php load-classes.php Cetera\Foo Cetera\Bar ...
 */

$site = getenv('CMS_TEST_SITE') ?: '/var/www/site';
$_SERVER['DOCUMENT_ROOT'] = $site . '/www';
$_SERVER['HTTP_HOST'] = 'localhost';
require_once $site . '/www/cms/include/common.php';

restore_error_handler();
restore_exception_handler();
ini_set('display_errors', 'stderr');
error_reporting(E_ALL);

foreach (array_slice($argv, 1) as $class) {
    echo "LOAD $class\n";
    try {
        if (!class_exists($class) && !interface_exists($class) && !trait_exists($class) && !enum_exists($class)) {
            echo "FAIL $class: класс не найден в файле\n";
        }
    } catch (\Throwable $e) {
        echo "FAIL $class: " . get_class($e) . ': ' . $e->getMessage() . "\n";
    }
}
