<?php
namespace Cetera\Tests\Smoke;

use Cetera\Tests\Support\Cms;
use Cetera\Widget\Widget;
use PHPUnit\Framework\TestCase;

/**
 * Ошибки виджетов на сайте: пользователям back-office — блок callout alert,
 * посетителям — только HTML-комментарий без вывода на страницу.
 */
final class WidgetErrorTest extends TestCase
{
    public function testVisitorGetsHtmlComment(): void
    {
        Cms::app();

        $html = Widget::errorHtml(new \Exception('Unable to find template "x.twig" -->'));

        $this->assertStringStartsWith('<!-- ', $html);
        $this->assertStringContainsString('Unable to find template', $html);
        // сообщение не может закрыть комментарий раньше времени
        $this->assertSame(1, substr_count($html, '-->'));
    }

    public function testUnknownWidgetInTwigDoesNotBreakPage(): void
    {
        $twig = Cms::app()->getTwig();
        $template = $twig->createTemplate("before{% widget 'NoSuchWidget' %}after");

        $html = $template->render([]);

        $this->assertStringStartsWith('before', $html);
        $this->assertStringEndsWith('after', $html);
        $this->assertStringContainsString('<!-- ', $html);
    }
}
