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
 * 
 *
 * @package CeteraCMS
 * @access private 
 **/ 
class Material extends Tag {
    public function __construct($table, $id = 0) {
        parent::__construct("material_{$table}_{$id}");
    }
}