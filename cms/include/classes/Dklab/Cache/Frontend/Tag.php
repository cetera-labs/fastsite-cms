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
abstract class Dklab_Cache_Frontend_Tag
{
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
        $this->getBackend()->clean(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG, 
            array($this->getNativeId())
        );
    }
    

    /**
     * Returns backend object responsible for this cache tag.
     * This method has to be public, because we use it in Slot::addTag()
     * to check equality of tag and slot backends.
     * 
     * @return \Zend_Cache_Backend_Interface
     */
    public abstract function getBackend();


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
}
?>
