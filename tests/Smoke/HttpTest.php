<?php
namespace Cetera\Tests\Smoke;

use Cetera\Tests\Support\Cms;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Запросы к работающему сайту через nginx: фронт-офис, вход в админку
 * и чтение данных для экранов админки (data_*.php) с теми параметрами, что шлёт UI.
 */
final class HttpTest extends TestCase
{
    private static ?CookieJar $session = null;

    private static function client(): Client
    {
        return new Client(['base_uri' => Cms::url(), 'http_errors' => false, 'allow_redirects' => false, 'timeout' => 30]);
    }

    /** Сессия администратора, созданного dev/bin/install.sh */
    private static function session(): CookieJar
    {
        if (!self::$session) {
            $jar = new CookieJar();
            $response = self::client()->post('/cms/include/action_login.php', [
                'cookies' => $jar,
                'form_params' => [
                    'login'  => getenv('DEV_ADMIN_LOGIN') ?: 'admin',
                    'pass'   => getenv('DEV_ADMIN_PASSWORD') ?: 'admin',
                    'locale' => 'ru',
                ],
            ]);
            $data = json_decode((string)$response->getBody(), true);
            if (empty($data['success'])) {
                throw new \RuntimeException('Не удалось войти в админку: ' . $response->getBody());
            }
            self::$session = $jar;
        }
        return self::$session;
    }

    private static function assertNoPhpErrors(string $body): void
    {
        self::assertDoesNotMatchRegularExpression('/(Fatal error|Warning|Notice|Deprecated|Stack trace):/i', $body);
    }

    public function testFrontPage(): void
    {
        $response = self::client()->get('/');
        $body = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode(), $body);
        $this->assertStringContainsString('<html', $body);
        self::assertNoPhpErrors($body);
    }

    public function testBackOfficeLoginPage(): void
    {
        $response = self::client()->get('/cms/index.php');
        $body = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode(), $body);
        $this->assertStringContainsString('/cms/js/app.js', $body);
        self::assertNoPhpErrors($body);
    }

    public function testWrongPasswordIsRejected(): void
    {
        $response = self::client()->post('/cms/include/action_login.php', [
            'form_params' => ['login' => 'admin', 'pass' => 'wrong-' . uniqid(), 'locale' => 'ru'],
        ]);
        $data = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($data, (string)$response->getBody());
        $this->assertEmpty($data['success']);
    }

    public function testBackOfficeStartPage(): void
    {
        $response = self::client()->get('/cms/include/welcome_new.php', ['cookies' => self::session()]);
        $body = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode(), $body);
        self::assertNoPhpErrors($body);
    }

    public static function dataEndpoints(): iterable
    {
        $urls = [
            'data_ui.php',
            'data_main_navigation.php',
            'data_tree.php?node=root',
            'data_catalog.php',
            'data_materials.php',
            'data_types.php',
            'data_users.php',
            'data_groups.php',
            'data_events.php',
            'data_eventlog.php',
            'data_mail_templates.php',
            'data_menus.php',
            'data_widgets.php',
            'data_plugins.php',
            'data_themes.php',
            'data_lang.php',
            'data_cache.php',
        ];
        foreach ($urls as $url) {
            yield $url => [$url];
        }
    }

    #[DataProvider('dataEndpoints')]
    public function testBackOfficeDataEndpoint(string $url): void
    {
        $response = self::client()->get('/cms/include/' . $url, ['cookies' => self::session()]);
        $body = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode(), strip_tags($body));
        self::assertNoPhpErrors($body);
        $this->assertNotNull(json_decode($body), 'Ответ — не JSON: ' . mb_substr($body, 0, 300));
    }

    public function testDataEndpointRequiresLogin(): void
    {
        $response = self::client()->get('/cms/include/data_users.php');
        $this->assertStringNotContainsString('"login"', (string)$response->getBody());
    }
}
