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

    /**
     * Индекс, которого нет в схеме, попадает в расхождения, а не роняет проверку.
     */
    public function testExtraIndexIsReported(): void
    {
        $conn = Cms::app()->getConn();
        $conn->executeStatement('CREATE INDEX test_extra_key ON server_aliases (name(10))');
        try {
            $diff = (new Schema())->compare_schemas();
        } finally {
            $conn->executeStatement('DROP INDEX test_extra_key ON server_aliases');
        }

        $extra = array_values(array_filter($diff, fn($e) => $e['error'] == Schema::EXTRA_KEY));
        $this->assertCount(1, $extra, json_encode($diff, JSON_UNESCAPED_UNICODE));
        $this->assertSame('server_aliases', $extra[0]['table']);
        $this->assertSame('INDEX `test_extra_key` (`name`(10))', $extra[0]['field']);
    }
}
