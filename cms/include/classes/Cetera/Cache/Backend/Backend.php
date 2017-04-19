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
        
            if ($app->getVar('cache_memcache') && self::isMemcachedAvailable()) {
                $backend = new \Dklab_Cache_Backend_TagEmuWrapper(new \Zend_Cache_Backend_Memcached());
            } elseif ($app->getVar('cache_file') && self::isFilecacheAvailable()) {
                $backend = new \Zend_Cache_Backend_File(array('cache_dir'=>FILECACHE_DIR, 'hashed_directory_level'=>1));
            } else {
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
    private static function isMemcachedAvailable() {
        if (!extension_loaded('memcache')) return FALSE;
        $memcache = new \Memcache;
        try {
            $res = $memcache->connect('localhost');
        } catch (Exception $e) {
            return FALSE;
        }
        if (!$res) return FALSE;
        return $memcache;
    }
    
	/**
	 * Проверяет доступность файлового хранилища
	 **/ 	
    private static function isFilecacheAvailable() {
        if (is_writable(FILECACHE_DIR)) return TRUE;
        return FALSE;
    }
}
