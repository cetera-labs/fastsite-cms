<?php   
namespace Cetera;  
include(__DIR__.'/common.php');
$p = parse_url($_SERVER['REQUEST_URI']);

if (preg_match('|(/'.PLUGIN_DIR.'/([^/]*)/)(.*)|',$p['path'],$m)) {
    define('PLUGIN_PATH', $m[1]);
    
    if (file_exists(WWWROOT.PLUGIN_PATH.$m[3])) {
        require WWWROOT.PLUGIN_PATH.$m[3];    
    }
    else {
        $plugins = \Cetera\Plugin::enum();
        if (isset($plugins[ $m[2] ])) {
            if ( file_exists($plugins[ $m[2] ]->path . DIRECTORY_SEPARATOR . $m[3]) ) {
                require($plugins[ $m[2] ]->path . DIRECTORY_SEPARATOR . $m[3]);
            }
        }
    }
}
elseif (preg_match('|(/'.THEME_DIR.'/[^/]*/)(.*)|',$p['path'],$m)) {
    define('PLUGIN_PATH', $m[1]);
    require WWWROOT.PLUGIN_PATH.$m[2];  	
}