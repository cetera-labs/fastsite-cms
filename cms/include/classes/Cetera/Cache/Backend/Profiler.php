<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$ 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera\Cache\Backend; 

/**
 * 
 *
 * @package FastsiteCMS
 * @access private 
 **/ 
class Profiler
{
    private $_backend = null;   
    public $successCalls = 0;
    public $failCalls = 0;
    
    public function __construct($backend)
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
    
    
    public function load($id)
    {
        $result = $this->_backend->getItem($id);
        if ($result === NULL) {
            $this->failCalls++;
        } else $this->successCalls++;
        return $result;        
    }
    

    public function multiLoad($ids)
    {
        return $this->_backend->multiLoad($ids);
    }
    
        
    public function test($id)
    {
        return $this->_backend->test($id);   
    }
    
    
    public function save($data, $id, $tags = array())
    {
        $this->_backend->setTags($id, $tags);        
        return $this->_backend->setItem($id, $data);
    }
    
    
    public function remove($id)
    {
        return $this->_backend->removeItem($id);      
    }
    
    
    public function clean($tags = array())
    {
        return $this->_backend->clearByTags($tags);   
    }
}