<?php
namespace Cetera\Tests\Smoke;

use Cetera\Menu;
use Cetera\Tests\Support\Cms;
use PHPUnit\Framework\TestCase;

/**
 * Скрытый пункт меню не выводится на сайте, но остаётся в данных и виден в админке (getItems).
 */
final class MenuHiddenTest extends TestCase
{
    public function testHiddenItemsSkippedOnSiteButKept(): void
    {
        Cms::app();
        $m = Menu::create('test_hidden_menu', 'test');
        try {
            $m->_data = [
                ['url' => '/a/', 'name' => 'A'],
                ['url' => '/b/', 'name' => 'B', 'hidden' => 1, 'children' => [['url' => '/b1/', 'name' => 'B1']]],
                ['url' => '/c/', 'name' => 'C', 'children' => [
                    ['url' => '/c1/', 'name' => 'C1', 'hidden' => 1],
                    ['url' => '/c2/', 'name' => 'C2'],
                ]],
                ['id' => 999999, 'type' => 4, 'hidden' => 1], // битый пункт: удаляется как раньше
            ];
            $m->save();

            $m = Menu::getByAlias('test_hidden_menu');
            $this->assertSame(['/a/', '/c/'], array_map(fn($c) => $c->url, $m->getChildren()));

            $c = $m->getChildren()[1];
            $this->assertSame(['/c2/'], array_map(fn($x) => $x->url, $c->getChildren()));
            $this->assertSame([true, false], array_column($c->getItems(), 'hidden'));

            $m = Menu::getByAlias('test_hidden_menu');
            $this->assertSame([false, true, false], array_column($m->getItems(), 'hidden'));
            $m->getChildren(); // самоочистка не должна терять скрытые пункты
            $m = Menu::getByAlias('test_hidden_menu');
            $this->assertCount(3, $m->getItems());
        } finally {
            Menu::getByAlias('test_hidden_menu')->delete();
        }
    }
}
