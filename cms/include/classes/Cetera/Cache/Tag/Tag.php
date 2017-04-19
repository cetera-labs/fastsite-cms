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
 * Базовый класс для всех будущих пользовательских классов-тэгов.
 * Определяет, с каким backend-ом будет идти работа.
 *
 * @package CeteraCMS
 * @access private 
 **/ 
abstract class Tag extends \Dklab_Cache_Frontend_Tag {
	/**
	 * @internal
	 */
    public function getBackend() {
        // Использовать \Zend_Registry совсем не обязательно; код может быть любым, 
        // лишь бы он всегда возвращал один и тот же backend.
        return \Cetera\Cache\Backend\Backend::getInstance();
    }
}