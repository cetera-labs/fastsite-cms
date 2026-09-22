<?php
namespace Cetera\Tests\Support;

/**
 * Доступ к CMS, установленной в Docker-окружении.
 */
final class Cms
{
    /** Поднимает Cetera\Application так же, как это делают скрипты из www/cms, и подключается к БД */
    public static function app(): \Cetera\Application
    {
        static $app = null;
        if ($app) {
            return $app;
        }

        $_SERVER['DOCUMENT_ROOT'] = TEST_SITE . '/www';
        $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
        require_once TEST_SITE . '/www/cms/include/common.php';

        // common.php ставит свои обработчики ошибок и исключений, а ошибки в тестах должен ловить PHPUnit
        restore_error_handler();
        restore_exception_handler();

        $app = \Cetera\Application::getInstance();
        $app->connectDb();
        return $app;
    }

    /** Базовый адрес сайта внутри docker-сети */
    public static function url(): string
    {
        return rtrim(getenv('CMS_TEST_URL') ?: 'http://nginx', '/');
    }

    /**
     * PHP-файлы репозитория
     *
     * @return string[] пути относительно корня репозитория
     */
    public static function phpFiles(string $dir): array
    {
        $files = [];
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(TEST_SRC . '/' . $dir, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if ($file->getExtension() === 'php') {
                $files[] = substr($file->getPathname(), strlen(TEST_SRC) + 1);
            }
        }
        sort($files);
        return $files;
    }
}
