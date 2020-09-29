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
 * Слот для внутренних нужд CMS
 *
 * @package FastsiteCMS
 * @access private 
 **/ 
class Cms extends Slot {
    public function __construct($key) {
        parent::__construct("cms_{$key}", 3600 * 24);
    }
}