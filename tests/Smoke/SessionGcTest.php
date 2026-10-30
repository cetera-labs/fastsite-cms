<?php
namespace Cetera\Tests\Smoke;

use Cetera\SessionSaveHandler;
use Cetera\Tests\Support\Cms;
use PHPUnit\Framework\TestCase;

/**
 * gc() удаляет просроченные сессии и возвращает их количество (контракт SessionHandlerInterface::gc()).
 */
final class SessionGcTest extends TestCase
{
    public function testGcReturnsNumberOfDeletedSessions(): void
    {
        $conn = Cms::app()->getConn();
        $h = new SessionSaveHandler();
        $h->write('gc_test_old1', 'x');
        $h->write('gc_test_old2', 'x');
        $h->write('gc_test_new', 'x');
        try {
            $conn->executeStatement("UPDATE session_data SET timestamp=1 WHERE id LIKE 'gc_test_old%'");

            $before = (int)$conn->fetchOne('SELECT COUNT(*) FROM session_data WHERE timestamp<?', [time() - 3600]);
            $this->assertSame($before, $h->gc(3600));
            $this->assertGreaterThanOrEqual(2, $before);
            $this->assertSame(0, (int)$conn->fetchOne("SELECT COUNT(*) FROM session_data WHERE id LIKE 'gc_test_old%'"));
            $this->assertSame(1, (int)$conn->fetchOne("SELECT COUNT(*) FROM session_data WHERE id='gc_test_new'"));
        } finally {
            $conn->executeStatement("DELETE FROM session_data WHERE id LIKE 'gc_test%'");
        }
    }
}
