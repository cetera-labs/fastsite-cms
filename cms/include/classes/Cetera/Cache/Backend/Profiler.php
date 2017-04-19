<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$ 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera\Cache\Backend; 

/**
 * 
 *
 * @package CeteraCMS
 * @access private 
 **/ 
class Profiler implements \Zend_Cache_Backend_Interface 
{
    private $_backend = null;   
    public $successCalls = 0;
    public $failCalls = 0;
    
    public function __construct(\Zend_Cache_Backend_Interface $backend)
    {
        $this->_backend = $backend;
    }
    
    public function getBackend()
    {
        return $this->_backend;
    }
    
    public function setDirectives($directives)
    {
        return $this->_backend->setDirectives($directives);
    }
    
    
    public function load($id, $doNotTestCacheValidity = false)
    {
        $result = $this->_backend->load($id, $doNotTestCacheValidity);
        if ($result === FALSE) {
            $this->failCalls++;
        } else $this->successCalls++;
        return $result;        
    }
    

    public function multiLoad($ids, $doNotTestCacheValidity = false)
    {
        return $this->_backend->multiLoad($ids, $doNotTestCacheValidity);
    }
    
        
    public function test($id)
    {
        return $this->_backend->test($id);   
    }
    
    
    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        return $this->_backend->save($data, $id, $tags, $specificLifetime);    
    }
    
    
    public function remove($id)
    {
        return $this->_backend->remove($id);      
    }
    
    
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, $tags = array())
    {
        return $this->_backend->clean($mode, $tags);     
    }
}