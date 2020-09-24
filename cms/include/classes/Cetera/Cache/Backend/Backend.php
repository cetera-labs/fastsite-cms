<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$ 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera\Cache\Backend; 

/**
 * Backend для кэширования объектов системы
 *
 * @package FastsiteCMS
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
                $o = array(
                    'servers' => array(
                        array('localhost', 11211)
                    )
                );
				if ( $app->getVar('memcache_server') ) {
					$o['servers'] = $app->getVar('memcache_server');
				}
                $backend = new TagEmuWrapper(new \Zend\Cache\Storage\Adapter\Memcache($o));
			}
            elseif ($app->getVar('cache_memcached')!='off'&& $app->getVar('cache_memcached')!='disable' && $app->getVar('cache_memcached')!='0' && self::isMemcachedAvailable()) {
                $o = array(
                    'servers' => array(
                        array('localhost', 11211)
                    )
                );
				if ( $app->getVar('memcached_server') ) {
					$o['servers'] = $app->getVar('memcached_server');
				}				
                $backend =  new TagEmuWrapper(new \Zend\Cache\Storage\Adapter\Memcached($o));
            } 
			elseif ($app->getVar('cache_file') && self::isFilecacheAvailable()) {
                $backend = new \Zend\Cache\Storage\Adapter\Filesystem([
                    'cache_dir'=>FILECACHE_DIR,
                ]);
            } 
			else {
                 $backend = new \Zend\Cache\Storage\Adapter\BlackHole();
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
