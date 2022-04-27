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
 * Слот для хранения полей материалов
 *
 * @package FastsiteCMS
 * @access private 
 **/ 
class MaterialField extends Slot {
    public function __construct($table, $id, $field) {
        parent::__construct("material_{$table}_{$id}_{$field}", 3600 * 24);
    }
	
    public function load()
    {
        //$raw = $this->_getBackend()->load($this->_id);
        //return unserialize($raw);
		// TODO
    }
    
    
    /**
     * Saves a data for this slot. 
     * 
     * @param mixed $data   Data to be saved.
     * @return void
     */
    public function save($data)
    {
        //$tags = array();
        //foreach ($this->_tags as $tag) {
         //   $tags[] = $tag->getNativeId();
        //}
        //$raw = serialize($data);
        //$this->_getBackend()->save($raw, $this->_id, $tags, $this->_lifetime);
		// TODO
    }	
}