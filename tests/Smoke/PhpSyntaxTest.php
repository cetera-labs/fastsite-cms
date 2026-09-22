<?php
namespace Cetera\Tests\Smoke;

use Cetera\Tests\Support\Cms;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * php -l по всем PHP-файлам: ловит синтаксические ошибки и ошибки компиляции файла
 * (например, return со значением в void-функции) на текущей версии PHP.
 */
final class PhpSyntaxTest extends TestCase
{
    public static function files(): iterable
    {
        foreach (Cms::phpFiles('cms') as $file) {
            yield $file => [$file];
        }
        foreach (Cms::phpFiles('tests') as $file) {
            yield $file => [$file];
        }
    }

    #[DataProvider('files')]
    public function testFileCompiles(string $file): void
    {
        exec(PHP_BINARY . ' -l ' . escapeshellarg(TEST_SRC . '/' . $file) . ' 2>&1', $output, $code);
        $this->assertSame(0, $code, implode("\n", $output));
    }
}
