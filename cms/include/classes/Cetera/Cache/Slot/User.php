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
 * Слот для хранения пользователей
 *
 * @package FastsiteCMS
 * @access private 
 **/ 
class User extends Slot {
	
    /**
     * @internal  
     */  		
    public function __construct($key) {
        parent::__construct("user_{$key}", 3600 * 24);
    }
}
