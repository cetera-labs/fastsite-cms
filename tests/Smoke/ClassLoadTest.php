<?php
namespace Cetera\Tests\Smoke;

use Cetera\Tests\Support\Cms;
use PHPUnit\Framework\TestCase;

/**
 * Загружает каждый класс ядра. Несовместимость с родителем или интерфейсом
 * (как у Widget::compile() с void в Twig 3) проявляется только при загрузке класса
 * и роняет процесс фатальной ошибкой, поэтому классы грузятся в дочернем процессе
 * tests/bin/load-classes.php, а после падения он перезапускается со следующего класса.
 */
final class ClassLoadTest extends TestCase
{
    public function testAllCoreClassesLoad(): void
    {
        $prefix = 'cms/include/classes/';
        $classes = [];
        foreach (Cms::phpFiles('cms/include/classes') as $file) {
            $classes[] = str_replace('/', '\\', substr($file, strlen($prefix), -4));
        }
        $this->assertNotEmpty($classes);

        $failures = [];
        while ($classes) {
            $cmd = PHP_BINARY . ' ' . escapeshellarg(TEST_SRC . '/tests/bin/load-classes.php') . ' '
                . implode(' ', array_map('escapeshellarg', $classes)) . ' 2>&1';
            exec($cmd, $output, $code);

            // Скрипт печатает «LOAD <класс>» перед загрузкой и «FAIL <класс>: <ошибка>», если класс не найден
            $last = null;
            $error = [];
            foreach ($output as $line) {
                if (str_starts_with($line, 'LOAD ')) {
                    $last = substr($line, 5);
                    $error = [];
                } elseif (str_starts_with($line, 'FAIL ')) {
                    $failures[] = substr($line, 5);
                } elseif (trim($line) !== '') {
                    $error[] = trim($line);
                }
            }

            if ($code === 0) {
                break;
            }
            if ($last === null) {
                $this->fail("load-classes.php упал до загрузки классов:\n" . implode("\n", $output));
            }
            $failures[] = $last . ': ' . implode(' ', $error);
            $classes = array_slice($classes, array_search($last, $classes, true) + 1);
            $output = [];
        }

        $this->assertSame([], $failures, "Классы не загружаются:\n" . implode("\n", $failures));
    }
}
