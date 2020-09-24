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
 * Тег для пользователей
 *
 * @package FastsiteCMS
 * @access private 
 **/ 
class User extends Tag {
	/**
	 * @internal
	 */	
    public function __construct($id) {
        parent::__construct("user_{$id}");
    }
}