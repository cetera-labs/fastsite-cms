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
class Dklab_Cache_Backend_MemcachedTag extends \Zend_Cache_Backend_Memcached
{
    private $_handle;
    
    
    /**
     * Constructor.
     * 
     * @see \Zend_Cache_Backend_Memcached::__construct()
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
        $this->_handle = self::_getPrivateProp($this, "_memcache"); 
    }
    
    
    /**
     * Saves a data in memcached.
     * Supports tags.
     * 
     * @see \Zend_Cache_Backend_Memcached::save()
     */
    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        $result = parent::save($data, $id, array(), $specificLifetime);
        if ($tags) {
            if (!method_exists($this->_handle, 'tag_add')) {
                \Zend_Cache::throwException('Method tag_add() is not supported by the PHP memcached extension!');
            }
            foreach ($tags as $tag) {
                $this->_handle->tag_add($tag, $id);
            }
            return true;
        }
        return $result;
    }
    
    
    /**
     * Cleaning operation with tag support.
     * 
     * @see \Zend_Cache_Backend_Memcached::clean()
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, $tags = array()) 
    {
    	if ($mode == \Zend_Cache::CLEANING_MODE_MATCHING_TAG) {
            if ($tags) {
                if (!method_exists($this->_handle, 'tag_delete')) {
                    \Zend_Cache::throwException('Method tag_delete() is not supported by the PHP memcached extension!');
                }
                foreach ($tags as $tag) {
                    $this->_handle->tag_delete($tag);
                }
            }
        } else {
            return parent::clean($mode, $tags); 
        }
    }


    /**
     * Returns native handle.
     * 
     * @return Memcache   Native PHP memcache handle.
     */
    protected function _getHandle()
    {
        return $this->_handle;
    }
    
    
    /**
     * Reads a private or protected property from the object.
     * Unfortunately we have to use this hack, because \Zend_Cache_Backend_Memcached
     * does not declare $_memcache handle as protected.
     * 
     * In PHP private properties are named with \x00 in the name.
     * 
     * @param object $obj   Object to read a property from.
     * @param string $name  Name of a protected or private property.
     * @return mixed        Property value or exception if property is not found.
     */
    private static function _getPrivateProp($obj, $name)
    {
        $arraized = (array)$obj;
        foreach ($arraized as $k => $v) {
            if (substr($k, -strlen($name)) === $name) {
                return $v;
            }
        }
        throw new Exception\CMS(
            "Cannot find $name property in \Zend_Cache_Backend_Memcached; properties are: " 
            . array_map('addslashes', array_keys($arraized))
        );
    }
}
?>
