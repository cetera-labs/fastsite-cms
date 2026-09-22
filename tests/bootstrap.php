<?php
/**
 * Тесты запускаются внутри Docker-окружения (dev/), где репозиторий подмонтирован
 * в vendor/cetera-labs/cetera-cms сайта /var/www/site.
 */

define('TEST_SITE', getenv('CMS_TEST_SITE') ?: '/var/www/site');
define('TEST_SRC', dirname(__DIR__));

require TEST_SITE . '/vendor/autoload.php';
require __DIR__ . '/Support/Cms.php';
