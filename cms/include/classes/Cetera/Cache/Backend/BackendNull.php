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
 * Заглушка при отключенном кэшировании
 *
 * @package CeteraCMS
 * @access private 
 **/ 
class BackendNull implements \Zend_Cache_Backend_Interface
{
    /**
     * @internal  
     */  		
    public function setDirectives($directives) {}
    /**
     * @internal  
     */  		
    public function load($id, $doNotTestCacheValidity = false) { return FALSE; }
	/**
     * @internal  
     */  		
    public function test($id) {}
	/**
     * @internal  
     */
    public function save($data, $id, $tags = array(), $specificLifetime = false) {}
	/**
     * @internal  
     */
    public function remove($id) {}
	/**
     * @internal  
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, $tags = array()) {}
	/**
     * @internal  
     */
    public function isAutomaticCleaningAvailable() {}
}