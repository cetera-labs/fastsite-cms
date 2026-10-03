<?php
namespace Cetera\Tests\Smoke;

use Cetera\ImageTransform;
use Cetera\Tests\Support\Cms;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Картинки webp и avif: загрузка через админку, уменьшение до максимального размера
 * при загрузке (file_upload_max_width) и ресайз через ImageTransform.
 */
final class ImageFormatsTest extends TestCase
{
    private const DIR = 'uploads/phpunit-images/';

    private static ?CookieJar $session = null;

    public static function formats(): array
    {
        return [
            'webp' => ['webp', IMAGETYPE_WEBP],
            'avif' => ['avif', IMAGETYPE_AVIF],
        ];
    }

    public static function tearDownAfterClass(): void
    {
        foreach (glob(TEST_SITE . '/www/' . self::DIR . '*') ?: [] as $file) {
            unlink($file);
        }
        @rmdir(TEST_SITE . '/www/' . self::DIR);
    }

    /** Картинка 300x200 в нужном формате */
    private static function makeImage(string $ext): string
    {
        $dir = TEST_SITE . '/www/' . self::DIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
            // тесты работают от root, а загрузку через nginx сохраняет php-fpm от www-data
            chmod($dir, 0777);
        }
        $file = $dir . uniqid('src_') . '.' . $ext;
        $img = imagecreatetruecolor(300, 200);
        imagefill($img, 0, 0, imagecolorallocate($img, 200, 50, 50));
        ('image' . $ext)($img, $file);
        return $file;
    }

    private static function session(): CookieJar
    {
        if (!self::$session) {
            $jar = new CookieJar();
            self::client()->post('/cms/include/action_login.php', [
                'cookies' => $jar,
                'form_params' => [
                    'login'  => getenv('DEV_ADMIN_LOGIN') ?: 'admin',
                    'pass'   => getenv('DEV_ADMIN_PASSWORD') ?: 'admin',
                    'locale' => 'ru',
                ],
            ]);
            self::$session = $jar;
        }
        return self::$session;
    }

    private static function client(): Client
    {
        return new Client(['base_uri' => Cms::url(), 'http_errors' => false, 'allow_redirects' => false, 'timeout' => 30]);
    }

    #[DataProvider('formats')]
    public function testGdSuffix(string $ext, int $type): void
    {
        $this->assertSame($ext, ImageTransform::gdSuffix($type));
    }

    public function testGdSuffixForUnsupportedType(): void
    {
        $this->assertNull(ImageTransform::gdSuffix(IMAGETYPE_TIFF_II));
    }

    #[DataProvider('formats')]
    public function testUploadThroughBackOffice(string $ext): void
    {
        $src = self::makeImage($ext);
        $name = 'upload_' . uniqid() . '.' . $ext;

        $response = self::client()->post('/cms/include/action_files.php', [
            'cookies'   => self::session(),
            'query'     => ['action' => 'upload', 'path' => '/' . self::DIR],
            'multipart' => [['name' => 'file', 'contents' => fopen($src, 'r'), 'filename' => $name]],
        ]);
        $body = (string)$response->getBody();
        $data = json_decode($body, true);

        $this->assertIsArray($data, $body);
        $this->assertTrue($data['success'], $body);
        $this->assertSame($name, $data['file']);
        $this->assertFileExists(TEST_SITE . '/www/' . self::DIR . $name);
    }

    /** При заданном максимальном размере загруженная картинка уменьшается в том же формате */
    #[DataProvider('formats')]
    public function testUploadedImageIsDownsized(string $ext, int $type): void
    {
        $app = Cms::app();
        $file = self::makeImage($ext);

        $app->setVar('file_upload_max_width', 100, false);
        try {
            check_upload_file($file);
        } finally {
            $app->setVar('file_upload_max_width', null, false);
        }

        $info = getimagesize($file);
        $this->assertSame(100, $info[0]);
        $this->assertSame($type, $info[2]);
    }

    #[DataProvider('formats')]
    public function testImageTransform(string $ext, int $type): void
    {
        Cms::app();
        $src = self::makeImage($ext);
        $dst = substr($src, 0, -strlen($ext) - 1) . '_small.' . $ext;

        ImageTransform::image($src, $dst, 60, 40);

        $info = getimagesize($dst);
        $this->assertSame([60, 40], [$info[0], $info[1]]);
        $this->assertSame($type, $info[2]);
    }
}
