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
	use DbConnection;

    /**
     * Open Session - retrieve resources
     *
     * @param string $save_path
     * @param string $name
     */
    public function open($save_path, $name)
    {
        //print "open $save_path, $name";
		return true;
    }

    /**
     * Close Session - free resources
     *
     */
    public function close()
    {
		return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     */
    public function read($id)
    { 
		return $this->getDbConnection()->fetchColumn('SELECT value FROM session_data WHERE id=?',[$id],0);
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
            $this->getDbConnection()->executeQuery('DELETE FROM session_data WHERE id=?',[$id]);
            else $this->getDbConnection()->executeQuery('REPLACE INTO session_data SET timestamp=?, id=?, value=?',[time(),$id,$data]);
		return true;
    }

    /**
     * Destroy Session - remove data from resource for
     * given session id
     *
     * @param string $id
     */
    public function destroy($id)
    {
        $this->getDbConnection()->executeQuery('DELETE FROM session_data WHERE id=?',[$id]);
		return true;
    }

    /**
     * Garbage Collection - remove old session data older
     * than $maxlifetime (in seconds)
     *
     * @param int $maxlifetime
     */
    public function gc($maxlifetime)
    {
        $this->getDbConnection()->executeQuery('DELETE FROM session_data WHERE timestamp<?',[time()-$maxlifetime]);
		return true;
    }

}
