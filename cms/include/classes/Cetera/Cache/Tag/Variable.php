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
 * Тег для переменных
 *
 * @package FastsiteCMS
 * @access private 
 **/ 
class Variable extends Tag {
	/**
	 * @internal
	 */	
    public function __construct() {
        parent::__construct("variable");
    }
}