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
 * Слот для хранения разделов-серверов
 *
 * @package FastsiteCMS
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