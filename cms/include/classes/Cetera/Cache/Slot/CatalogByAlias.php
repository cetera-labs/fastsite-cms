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
class CatalogByAlias extends Slot {
    public function __construct($id, $alias) {
        parent::__construct("catalog_{$id}_{$alias}", 3600 * 24);
    }
}