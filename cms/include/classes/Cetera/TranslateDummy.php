<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 * @internal
 **/
namespace Cetera; 

/**
 * @internal
 * @ignore
 **/
class TranslateDummy {
	/**
	 * @internal
	 * @ignore
	 **/	
    public function _($text) {
        return $text;
    }
	
	public function addTranslation() {}
}