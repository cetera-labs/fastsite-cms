<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$ 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera\Cache\Tag; 

/**
 * Базовый класс для всех будущих пользовательских классов-тэгов.
 * Определяет, с каким backend-ом будет идти работа.
 *
 * @package FastsiteCMS
 * @access private 
 **/ 
abstract class Tag {
    
    /**
     * Calculated ID associated to this slot.
     * 
     * @var string
     */
    private $_id = null;


    /**
     * Creates a new Tag object.
     *
     * @return Dklab_Cache_Tag
     */
    public function __construct($id)
    {
        $this->_id = $id;
    }
    
    
    /**
     * Clears all keys associated to this tags.
     * 
     * @return void
     */
    public function clean()
    {
        $this->getBackend()->clean([$this->getNativeId()]);
    }
    
    /**
     * Returns generated ID of this tag.
     * This method must be public, because it is used in Slot.
     * 
     * @return string    Tag name.
     */
    public function getNativeId()
    {
        return $this->_id;
    }
    
	/**
	 * @internal
	 */
    public function getBackend() {
        return \Cetera\Cache\Backend\Backend::getInstance();
    }
}