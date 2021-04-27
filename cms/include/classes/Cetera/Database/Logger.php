<?php
namespace Cetera\Database;  

class Logger implements \Doctrine\DBAL\Logging\SQLLogger
{
    
    
    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        $a = \Cetera\Application::getInstance();
        $a->debug(DEBUG_SQL, $sql);
        $a->queryCount++;
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
    }
}