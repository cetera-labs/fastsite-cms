<?php
/**
 * Cetera CMS 3 
 *
 * @package  Dklab_Cache
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author 
 * @access private 
 **/

/**
 * @package Dklab_Cache
 * @access private
 */ 
class Dklab_Cache_Backend_NamespaceWrapper implements \Zend_Cache_Backend_Interface 
{
    private $_backend = null;
    private $_namespace = null;
    
    
    public function __construct(\Zend_Cache_Backend_Interface $backend, $namespace)
    {
        $this->_backend = $backend;
        $this->_namespace = $namespace;
    }
    
    
    public function setDirectives($directives)
    {
        return $this->_backend->setDirectives($directives);
    }
    
    
    public function load($id, $doNotTestCacheValidity = false)
    {
        return $this->_backend->load($this->_mangleId($id), $doNotTestCacheValidity);
    }
    
    
    public function multiLoad($ids, $doNotTestCacheValidity = false)
    {
        if (!is_array($ids)) {
            \Zend_Cache::throwException('multiLoad() expects parameter 1 to be array, ' . gettype($ids) . ' given');
        }
        if (method_exists($this->_backend, 'multiLoad')) {
            return $this->_backend->multiLoad($this->_mangleIds($ids), $doNotTestCacheValidity);
        }
        // No multiLoad() method avalilable, so we have to emulate it to keep
        // the interface consistent.
        $result = array();
        foreach ($ids as $i => $id) {
            $result[$id] = $this->load($id, $doNotTestCacheValidity);
        }
        return $result;
    }
    
    
    public function test($id)
    {
        return $this->_backend->test($this->_mangleId($id));
    }
    
    
    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        $tags = array_map(array($this, '_mangleId'), $tags);
        return $this->_backend->save($data, $this->_mangleId($id), $tags, $specificLifetime);
    }
    
    
    public function remove($id)
    {
        return $this->_backend->remove($this->_mangleId($id));
    }
    
    
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, $tags = array())
    {
        $tags = array_map(array($this, '_mangleId'), $tags);
        return $this->_backend->clean($mode, $tags);
    }

    
    private function _mangleId($id)
    {
        return $this->_namespace . "_" . $id;
    }
    
    
    private function _mangleIds($ids)
    {
        foreach ($ids as $i => $id) {
            $ids[$i] = $this->_mangleId($id);
        }
        return $ids;
    }    
}
?>
