<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera; 

/**
 *  
 * @package CeteraCMS
 * @access private
 */ 
class SessionSaveHandler implements \Zend_Session_SaveHandler_Interface
{

    /**
     * Open Session - retrieve resources
     *
     * @param string $save_path
     * @param string $name
     */
    public function open($save_path, $name)
    {
        //print "open $save_path, $name";
    }

    /**
     * Close Session - free resources
     *
     */
    public function close()
    {
    }

    /**
     * Read session data
     *
     * @param string $id
     */
    public function read($id)
    { 
        $r = fssql_query('SELECT value FROM session_data WHERE id="'.mysql_real_escape_string($id).'"');
        if (mysql_num_rows($r)) return mysql_result($r,0);
    }

    /**
     * Write Session - commit data to resource
     *
     * @param string $id
     * @param mixed $data
     */
    public function write($id, $data)
    {
        if (!$data) 
            fssql_query('DELETE FROM session_data WHERE id="'.mysql_real_escape_string($id).'"');
            else fssql_query('REPLACE INTO session_data SET timestamp='.time().', id="'.mysql_real_escape_string($id).'", value="'.mysql_real_escape_string($data).'"');
    }

    /**
     * Destroy Session - remove data from resource for
     * given session id
     *
     * @param string $id
     */
    public function destroy($id)
    {
        fssql_query('DELETE FROM session_data WHERE id="'.mysql_real_escape_string($id).'"');
    }

    /**
     * Garbage Collection - remove old session data older
     * than $maxlifetime (in seconds)
     *
     * @param int $maxlifetime
     */
    public function gc($maxlifetime)
    {
        fssql_query('DELETE FROM session_data WHERE timestamp<'.(time()-$maxlifetime));
    }

}
