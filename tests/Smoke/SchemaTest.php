<?php
namespace Cetera\Tests\Smoke;

use Cetera\Schema;
use Cetera\Tests\Support\Cms;
use PHPUnit\Framework\TestCase;

/**
 * После установки CMS БД должна совпадать с XML-схемами (cms/.dbschema/core.xml и др.) —
 * и таблицы, и описания типов материалов. То же, что проверка на панели «Repair» в админке.
 */
final class SchemaTest extends TestCase
{
    public function testDatabaseMatchesSchema(): void
    {
        Cms::app();
        $diff = (new Schema())->compare_schemas();
        $this->assertSame([], $diff, 'Расхождения БД со схемой: ' . json_encode($diff, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
