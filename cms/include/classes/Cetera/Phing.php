<?php
namespace Cetera; 

class Phing
{
    private static $plugins = [];
    
    public static function plugins() {
            $vendorPath = dirname( dirname( dirname( dirname( dirname( dirname(__DIR__) ) ) ) ) );
            if (file_exists($vendorPath . DIRECTORY_SEPARATOR . 'cetera-labs' . DIRECTORY_SEPARATOR . 'cetera-cms-plugins.php')) {
                $composer_plugins = include( $vendorPath . DIRECTORY_SEPARATOR . 'cetera-labs' . DIRECTORY_SEPARATOR . 'cetera-cms-plugins.php' );
                foreach($composer_plugins as $k => $p) {
                    self::$plugins[ $p['name'] ] = $vendorPath . DIRECTORY_SEPARATOR . $k;
                    $res[] = $p['name'];
                }
            }
            
            return implode(',',$res);
    }
    
    public static function pluginPath($plugin) {
        return self::$plugins[ $plugin ];
    }

}