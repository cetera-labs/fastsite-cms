<?php   
namespace Cetera;  
include(__DIR__.'/common.php');
$p = parse_url($_SERVER['REQUEST_URI']);

if (preg_match('|(/'.PLUGIN_DIR.'/[^/]*/)(.*)|',$p['path'],$m)) {
    define('PLUGIN_PATH', $m[1]);
    require WWWROOT.PLUGIN_PATH.$m[2];    
}
elseif (preg_match('|(/'.THEME_DIR.'/[^/]*/)(.*)|',$p['path'],$m)) {
    define('PLUGIN_PATH', $m[1]);
    require WWWROOT.PLUGIN_PATH.$m[2];  	
}