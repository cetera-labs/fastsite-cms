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
 * Слот для хранения разделов
 *
 * @package CeteraCMS
 * @access private 
 **/ 
class CatalogById extends Slot {
    public function __construct($id) {
        parent::__construct("catalog_{$id}", 3600 * 24);
    }
}