<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$ 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera\Cache\Slot; 

/**
 * Базовый класс для всех будущих пользовательских классов-слотов.
 * Определяет, с каким backend-ом будет идти работа.
 *
 * @package FastsiteCMS
 * @access private 
 **/ 
class Slot {
    /**
     * Tags attached to this slot.
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
     * @param Cache\Tag $tag   Tag object to associate.
     * @return void
     */
    public function addTag($tag)
    {
        if ($tag->getBackend() !== $this->_getBackend()) {
            \Zend_Cache::throwException("Backends for tag " . get_class($tag) . " and slot " . get_class($this) . " must be same");
        }
        $this->_tags[] = $tag;
    }
    
    /**
     * @internal  
     */  	
    protected function _getBackend() {
        return \Cetera\Cache\Backend\Backend::getInstance();
    }
}