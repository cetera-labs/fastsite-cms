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
 * Слот для хранения полей материалов
 *
 * @package CeteraCMS
 * @access private 
 **/ 
class MaterialField extends Slot {
    public function __construct($table, $id, $field) {
        parent::__construct("material_{$table}_{$id}_{$field}", 3600 * 24);
    }
}