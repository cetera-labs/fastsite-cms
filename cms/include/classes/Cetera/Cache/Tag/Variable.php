<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$ 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera\Cache\Tag; 

/**
 * Тег для переменных
 *
 * @package CeteraCMS
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