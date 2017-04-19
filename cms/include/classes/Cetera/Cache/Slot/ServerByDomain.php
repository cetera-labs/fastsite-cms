<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$ 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera\Cache\Slot; 

/**
 * Слот для хранения разделов-серверов
 *
 * @package CeteraCMS
 * @access private 
 **/ 
class ServerByDomain extends Slot {
	
    /**
     * @internal  
     */  		
    public function __construct($domain) {
        parent::__construct("server_{$domain}", 3600 * 24);
    }
}