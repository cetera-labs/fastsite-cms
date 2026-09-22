<?php
namespace Cetera\Tests\Smoke;

use Cetera\Tests\Support\Cms;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Компилирует Twig-шаблоны ядра в окружении CMS (с её тегами, фильтрами и функциями),
 * не выполняя их: ловит синтаксические ошибки и неизвестные теги/фильтры/функции.
 */
final class TwigTemplatesTest extends TestCase
{
    public static function templates(): iterable
    {
        $base = TEST_SRC . '/cms/twig_templates/';
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS));
        $names = [];
        foreach ($it as $file) {
            if ($file->getExtension() !== 'twig') {
                continue;
            }
            $path = substr($file->getPathname(), strlen($base));
            // pages/ — корень загрузчика, widgets/ — пространство имён @widget (см. Application::getTwig())
            $names[] = str_starts_with($path, 'widgets/')
                ? '@widget/' . substr($path, strlen('widgets/'))
                : substr($path, strlen('pages/'));
        }
        sort($names);
        foreach ($names as $name) {
            yield $name => [$name];
        }
    }

    #[DataProvider('templates')]
    public function testTemplateCompiles(string $name): void
    {
        $twig = Cms::app()->getTwig();
        $source = $twig->getLoader()->getSourceContext($name);
        $this->assertNotEmpty($twig->compileSource($source));
    }
}
