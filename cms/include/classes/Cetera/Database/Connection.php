<?php
namespace Cetera\Database;  

/**
 * A wrapper around a Doctrine\DBAL\Driver\Connection that adds features like
 * REPLACE and INSERT IGNORE
 */
class Connection extends \Doctrine\DBAL\Connection {
	
    /**
     * Inserts a table row with specified data.
	 * REPLACE works exactly like INSERT, except that if an old row in the table has the same value as a new row for a PRIMARY KEY or a UNIQUE index, the old row is deleted before the new row is inserted. 
     *
     * Table expression and columns are not escaped and are not safe for user-input.
     *
     * @param string $tableExpression The expression of the table to insert data into, quoted or unquoted.
     * @param array  $data      An associative array containing column-value pairs.
     * @param array  $types     Types of the inserted data.
     *
     * @return integer The number of affected rows.
     */
    public function replace($tableExpression, array $data, array $types = array())
    {
        $this->connect();

        if (empty($data)) {
            return $this->executeUpdate('REPLACE INTO ' . $tableExpression . ' ()' . ' VALUES ()');
        }

        return $this->executeUpdate(
            'REPLACE INTO ' . $tableExpression . ' (' . implode(', ', array_keys($data)) . ')' .
            ' VALUES (' . implode(', ', array_fill(0, count($data), '?')) . ')',
            array_values($data),
            is_string(key($types)) ? $this->extractTypeValues($data, $types) : $types
        );
    }	
	
    /**
     * Inserts a table row with specified data.
	 * Errors that occur while executing the INSERT statement are ignored. For example, without IGNORE, a row that duplicates an existing UNIQUE index or PRIMARY KEY value in the table causes a duplicate-key error and the statement is aborted. With IGNORE, the row is discarded and no error occurs. Ignored errors generate warnings instead
     *
     * Table expression and columns are not escaped and are not safe for user-input.
     *
     * @param string $tableExpression The expression of the table to insert data into, quoted or unquoted.
     * @param array  $data      An associative array containing column-value pairs.
     * @param array  $types     Types of the inserted data.
     *
     * @return integer The number of affected rows.
     */
    public function insertIgnore($tableExpression, array $data, array $types = array())
    {
        $this->connect();

        if (empty($data)) {
            return $this->executeUpdate('INSERT INTO ' . $tableExpression . ' ()' . ' VALUES ()');
        }

        return $this->executeUpdate(
            'INSERT INTO ' . $tableExpression . ' (' . implode(', ', array_keys($data)) . ')' .
            ' VALUES (' . implode(', ', array_fill(0, count($data), '?')) . ')',
            array_values($data),
            is_string(key($types)) ? $this->extractTypeValues($data, $types) : $types
        );
    }	
	
}