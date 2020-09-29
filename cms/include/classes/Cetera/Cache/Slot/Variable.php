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
 * Слот для хранения переменных
 *
 * @package FastsiteCMS
 * @access private 
 **/ 
class Variable extends Slot {
	
    /**
     * Установливает текущий раздел.
     *      
     * @param  int ID сервера раздел
     * @param string имя переменной
     */	
    public function __construct($server_id, $name) {
        parent::__construct("variable_{$server_id}_{$name}", 3600 * 24);
    }
	
}