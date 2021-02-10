<?php
namespace Cetera\Database;  

class Logger implements \Doctrine\DBAL\Logging\SQLLogger
{
    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        \Cetera\Application::getInstance()->debug(DEBUG_SQL, $sql);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
    }
}