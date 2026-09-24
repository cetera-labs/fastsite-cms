<?php
/**
 * Cetera\Catalog создаётся во время выполнения через class_alias (cms/include/classes/Cetera/Catalog.php),
 * поэтому статический анализ его не видит. Заглушка описывает тот же псевдоним для PHPStan.
 */

namespace Cetera;

class Catalog extends Section
{
}
