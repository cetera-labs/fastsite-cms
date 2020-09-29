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
 * Слот для хранения разделов
 *
 * @package FastsiteCMS
 * @access private 
 **/ 
class CatalogByAlias extends Slot {
    public function __construct($id, $alias) {
        parent::__construct("catalog_{$id}_{$alias}", 3600 * 24);
    }
}