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
 * 
 *
 * @package FastsiteCMS
 * @access private 
 **/ 
class CatalogID extends Tag {
    public function __construct($id) {
        parent::__construct("catalog_{$id}");
    }
}