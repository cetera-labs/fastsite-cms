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
abstract class Dklab_Cache_Frontend_Slot
{
    /**
     * Tags attached to this slot.
     * 
     * @var array of Dklab_Cache_Tag
     */
    private $_tags;
    
    /**
     * Calculated ID associated to this slot.
     * 
     * @var string
     */
    private $_id = null;
    
    /**
     * Lifetime of this slot.
     */
    private $_lifetime;

    
    /**
     * Creates a new slot object.
     * 
     * @param string $id   ID of this slot.
     * @return Dklab_Cache_Slot
     */
    public function __construct($id, $lifetime)
    {
        $this->_id = getenv('SERVER_NAME').'_'.str_replace('/','_',$id);
        $this->_lifetime = $lifetime;
        $this->_tags = array();
    }
    
    
    /**
     * Loads a data of this slot. If nothing is found, returns false.
     * 
     * @return mixed   Complex data or false if no cache entry is found.
     */
    public function load()
    {
        $raw = $this->_getBackend()->load($this->_id);
        return unserialize($raw);
    }
    
    
    /**
     * Saves a data for this slot. 
     * 
     * @param mixed $data   Data to be saved.
     * @return void
     */
    public function save($data)
    {
        $tags = array();
        foreach ($this->_tags as $tag) {
            $tags[] = $tag->getNativeId();
        }
        $raw = serialize($data);
        $this->_getBackend()->save($raw, $this->_id, $tags, $this->_lifetime);
    }
    
    
    /**
     * Removes a data of specified slot.
     * 
     * @return void
     */
    public function remove()
    {
        $this->_getBackend()->remove($this->_id);
    }
    
    
    /**
     * Associates a tag with current slot.
     * 
     * @param Dklab_Cache_Tag $tag   Tag object to associate.
     * @return void
     */
    public function addTag(Dklab_Cache_Frontend_Tag $tag)
    {
        if ($tag->getBackend() !== $this->_getBackend()) {
            \Zend_Cache::throwException("Backends for tag " . get_class($tag) . " and slot " . get_class($this) . " must be same");
        }
        $this->_tags[] = $tag;
    }

    
    /**
     * Returns Thru-proxy object to call a method with transparent caching.
     * Usage:
     *   $slot = new SomeSlot(...);
     *   $slot->thru($person)->getSomethingHeavy();
     *   // calls $person->getSomethingHeavy() with intermediate caching
     * 
     * @param mixed $obj    Object or classname. May be null if you want to
     *                      thru-call a global function, not a method.
     * @return Dklab_Cache_Frontend_Slot_Thru   Thru-proxy object.
     */
    public function thru($obj)
    {
        return new Dklab_Cache_Frontend_Slot_Thru($this, $obj);
    }


    /**
     * Returns backend object responsible for this cache slot.
     * 
     * @return \Zend_Cache_Core
     */
    protected abstract function _getBackend();
}

/**
 * Thru-caching helper class.
 *  
 * @package Dklab_Cache
 * @access private
 */ 
class Dklab_Cache_Frontend_Slot_Thru
{
    private $_slot;
    private $_obj; 
    
    public function __construct(Dklab_Cache_Frontend_Slot $slot, $obj)
    {
        $this->_slot = $slot;
        $this->_obj = $obj;
    }
    
    public function __call($method, $args)
    {
        if (false === ($result = $this->_slot->load())) {
            if ($this->_obj) {
                $result = call_user_func_array(array($this->_obj, $method), $args);
            } else {
                $result = call_user_func_array($method, $args);
            }
            $this->_slot->save($result);
        }
        return $result;
    }
}
?>
