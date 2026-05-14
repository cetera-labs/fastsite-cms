<?php
namespace Cetera; 

class Phing
{
    private static $plugins = [];
    
    public static function plugins() {
            $vendorPath = dirname( dirname( dirname( dirname( dirname( dirname(__DIR__) ) ) ) ) );
            $res = [];

            $file = $vendorPath . DIRECTORY_SEPARATOR . 'cetera-labs' . DIRECTORY_SEPARATOR . 'cetera-cms-plugins.php';

            if (file_exists($file)) {

                $composer_plugins = include($file);

                if (is_array($composer_plugins)) {
                    foreach ($composer_plugins as $k => $p) {

                        if (!is_array($p)) {
                            continue;
                        }

                        if (empty($p['name'])) {
                            continue;
                        }

                        self::$plugins[$p['name']] = $vendorPath . DIRECTORY_SEPARATOR . $k;
                        $res[] = $p['name'];
                    }
                }
            }
            
            return $res ? implode(',', $res) : '';
    }
    
    public static function pluginPath($plugin) {
        return self::$plugins[ $plugin ] ?? '';
    }

}
