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
 * Базовый класс для всех будущих пользовательских классов-слотов.
 * Определяет, с каким backend-ом будет идти работа.
 *
 * @package CeteraCMS
 * @access private 
 **/ 
class Slot extends \Dklab_Cache_Frontend_Slot {
    /**
     * @internal  
     */  	
    protected function _getBackend() {
        // Код может быть любым, лишь бы он возвращал один и тот же backend.
        return \Cetera\Cache\Backend\Backend::getInstance();
    }
}