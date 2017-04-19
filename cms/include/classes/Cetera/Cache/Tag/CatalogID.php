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
class CatalogID extends Tag {
    public function __construct($id) {
        parent::__construct("catalog_{$id}");
    }
}