<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$ 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera\Cache\Backend; 

/**
 * Backend для кэширования объектов системы
 *
 * @package CeteraCMS
 * @access private 
 **/ 
class Backend {
	
    /**
     * @internal  
     */  	
    private static $_instance = null;
    
	/**
	 * В зависимости от настроек системы используется либо Memcache, либо файловый кэш, либо вообще ничего
	 **/ 	
    public static function getInstance()
    {
        if (null === self::$_instance) {
        
            $app = \Cetera\Application::getInstance();
        
            if ($app->getVar('cache_memcache')!='off' && $app->getVar('cache_memcache')!='disable' && $app->getVar('cache_memcache')!='0' && self::isMemcacheAvailable()) {
				$o = [];
				if ( $app->getVar('memcache_server') ) {
					$o['servers'] = $app->getVar('memcache_server');
				}
                $backend = new \Dklab_Cache_Backend_TagEmuWrapper(new \Zend_Cache_Backend_Memcached($o));
			}
            elseif ($app->getVar('cache_memcached')!='off'&& $app->getVar('cache_memcached')!='disable' && $app->getVar('cache_memcached')!='0' && self::isMemcachedAvailable()) {
				$o = [];
				if ( $app->getVar('memcached_server') ) {
					$o['servers'] = $app->getVar('memcached_server');
				}				
                $backend = new \Dklab_Cache_Backend_TagEmuWrapper(new \Zend_Cache_Backend_Libmemcached($o));				
            } 
			elseif ($app->getVar('cache_file') && self::isFilecacheAvailable()) {
                $backend = new \Zend_Cache_Backend_File(array('cache_dir'=>FILECACHE_DIR, 'hashed_directory_level'=>1));
            } 
			else {
                $backend = new BackendNull();
            }
        
            self::$_instance = new Profiler($backend);
        }
        return self::$_instance;
    } 
    
    /**
     * @internal  
     */  	
    protected function __construct() { }
    
	/**
	 * Проверяет доступность memcache
	 **/ 	
    private static function isMemcacheAvailable() {		
        if (!extension_loaded('memcache')) return FALSE;
        return true;
    }
	
	/**
	 * Проверяет доступность memcached
	 **/ 	
    private static function isMemcachedAvailable() {		
        if (!extension_loaded('memcached')) return FALSE;
        return true;
    }	
    
	/**
	 * Проверяет доступность файлового хранилища
	 **/ 	
    private static function isFilecacheAvailable() {
        if (is_writable(FILECACHE_DIR)) return TRUE;
        return FALSE;
    }
}
