<?php
/**
 * Cetera CMS 3 
 * 
 * Common module  
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/ 
 
if (!function_exists('curl_reset'))
{
	function curl_reset(&$ch){
	  $ch = curl_init();
	}
} 

if ( !function_exists(  'is_iterable' ) )
{
    function is_iterable( $obj ) {
        return is_array( $obj ) || ( is_object( $obj ) && ( $obj instanceof \Traversable ) );
    }
}

error_reporting (E_ALL ^ E_NOTICE);

require_once(__DIR__.'/constants.php');

mb_internal_encoding("UTF-8");

ini_set('include_path', '.'.PATH_SEPARATOR.CMSROOT.PATH_SEPARATOR.CMSROOT.'include/classes'.PATH_SEPARATOR.DOCROOT.LIBRARY_PATH);

if (!file_exists(DOCROOT.LIBRARY_PATH)) {
	
	echo 'Download and unpack <a href="http://cetera.ru/cetera_cms/library.zip">http://cetera.ru/cetera_cms/library.zip</a> at your webserver home folder ';
	echo '<a href="/cms/library_install.php">or click to automatic install</a>';
	die();
	
}

if (COMPOSER_INSTALL) {
	
	include VENDOR_PATH.'/autoload.php';
	
}
else {

	include DOCROOT.LIBRARY_PATH . '/vendor/composer/autoload_real.php';
	if (class_exists('\ComposerAutoloaderInit5f42614c889c2666ee9d5273042f3a3b')) {
		\ComposerAutoloaderInit5f42614c889c2666ee9d5273042f3a3b::getLoader();
	}
	else {
		include DOCROOT.LIBRARY_PATH . '/vendor/autoload.php';
	}

	$loader = new \Composer\Autoload\ClassLoader();
	$loader->add('Cetera', __DIR__.'/classes');
	$loader->add('Dklab', __DIR__.'/classes');
	$loader->register();

}

$application = \Cetera\Application::getInstance();

if ($application->getVar('class_compability')) {
    // Backward compability
    class_alias('Cetera\Catalog', 'Catalog');
    class_alias('Cetera\Material', 'Material');
    class_alias('Cetera\Application', 'Application');
	class_alias('Cetera\UserAuthAdapter', 'UserAuthAdapter');
	class_alias('Cetera\User', 'User');
    class_alias('Cetera\ExternalUserAuthAdapter', 'ExternalUserAuthAdapter');
    class_alias('Cetera\Cache\Tag\Material', 'Cache_Tag_Material');
    class_alias('Cetera\Cache\Slot\User', 'Cache_Slot_User');
}

if (get_magic_quotes_gpc()) {
  if (is_array($_GET)) $_GET = Util::stripslashes($_GET);
  if (is_array($_POST)) $_POST = Util::stripslashes($_POST);
  if (is_array($_COOKIE)) $_COOKIE = Util::stripslashes($_COOKIE);
}

function check_upload_file_name(&$name)
{
     if ($name == '.htaccess' || substr($name,-4) == '.php') $name .= '_not_allowed';
	 
	 // Замена кириллицы	
	 $name = translit($name, FALSE);
}  

function check_upload_file($name) {
    $a = \Cetera\Application::getInstance();
    $info = getimagesize($name);
    if ($info && ($a->getVar('file_upload_max_width') || $a->getVar('file_upload_max_height')) )
    {
        require_once(__DIR__.'/image.php');
        $res = image($name, (int)$a->getVar('file_upload_max_width'), (int)$a->getVar('file_upload_max_height'), 100, 1, 1, 0);
        if ($res['file'] != $name)
        {
            unlink($name);
            copy($res['file'], $name);
        }
    }
} 

/* DEPRECATED
* Делает mysql_query() c контролем ошибок
* 
* @param string $query SQL query
* @return resource mysql result ID
*/
function fssql_query($query) {	
	$r = mysql_query($query);   
	if (mysql_error()) throw new \Exception($query);
	return $r;
}  

/*
* cached_fopen_url 
* 
* Xbnftn данные из HTTP url
* 
* @param string URL
* @param string в каком режиме открывать файл с полученными данными
* @param integer через какое время содержимое файла считать устаревшим и перечитывать данные
* @param string путь, где хранится r'i
* @param intege время ожидания сокета
* @return file resoruce или FALSE в случае неудачи 
**/ 
function cached_fopen_url($url, $file_mode, $timeout_seconds = 90, $cache_path = ".cache", $fsocket_timeout = 10, $debug = false, $cache_filename = false) 
{ 
          //$handle = fopen($cache_path.'/test.xml', $file_mode); 
          //return $handle; 
          
   try {     
       clearstatcache(); 
       if (!$cache_filename) $cache_filename=$cache_path . "/" . urlencode($url) .".cached";
           else $cache_filename = $cache_path . "/" . $cache_filename; 
                                
       if ( ( @file_exists($cache_filename ) and ( ( @filemtime($cache_filename) + $timeout_seconds) > ( time() ) ) ) ) { 
          // ok, file is already cached and young enouth 

       } else { 
                      
          $urlParts = parse_url($url); 
          $host = $urlParts['host']; 
          $port = (isset($urlParts['port'])) ? $urlParts['port'] : 80; 
           
          if( !$fp = @fsockopen( $host, $port, $errno, $errstr, $fsocket_timeout )) { 
             // Server not responding 
          } else { 
             $path = $urlParts['path'];
    	     if (!$path) $path = '/';
    	     if ($urlParts['query']) $path .= '?'.$urlParts['query'];
             if( !fputs( $fp, "GET ".$path." HTTP/1.0\r\nHost:$host\r\n\r\n" )) die( "unable to send get request" ); 
             $data = null; 
             stream_set_timeout($fp, $fsocket_timeout);    
             $status = socket_get_status($fp); 
             while( !feof($fp) && !$status['timed_out'])          
             { 
                $data .= fgets ($fp,8192); 
                $status = socket_get_status($fp); 
             } 
             fclose ($fp); 
             // strip headers 
             $sData = split("\r\n\r\n", $data, 2); 
             $data = $sData[1]; 
              
             // save to cache file 
             $f2 = fopen($cache_filename,"w+"); 
    		 if ($f2) {
             	fwrite($f2,$data); 
             	fclose($f2); 	     
    		 }
          } 
       } 
        
       // ok, point to (fresh) cached file 
       if ( @file_exists($cache_filename )) { 
          $handle = fopen($cache_filename, $file_mode); 
          return $handle; 
       } 
   } catch (\Exception $e) {   
   } 
   return false; 
} 

function translit($string, $nonWordChars = TRUE) {

    $letters = array(
    '№'=>'no','А'=>'a','Б'=>'b','В'=>'v','Г'=>'g','Д'=>'d','Е'=>'e','Ё'=>'e','Ж'=>'zh','З'=>'z','И'=>'i','Й'=>'j','К'=>'k',
    'Л'=>'l','М'=>'m','Н'=>'n','О'=>'o','П'=>'p','Р'=>'r','С'=>'s','Т'=>'t','У'=>'u','Ф'=>'f','Х'=>'kh','Ц'=>'ts',
    'Ч'=>'ch','Ш'=>'sh','Щ'=>'sh','Ъ'=>'','Ы'=>'y','Ь'=>'','Э'=>'e','Ю'=>'yu','Я'=>'ya',
    
    'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'j','к'=>'k',
    'л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'kh','ц'=>'ts',
    'ч'=>'ch','ш'=>'sh','щ'=>'sh','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
	
    'Α'=>'a','α'=>'a','Ά'=>'a','ά'=>'a','Β'=>'b','β'=>'b','Γ'=>'g','γ'=>'g','Δ'=>'d','δ'=>'d','Ε'=>'e','ε'=>'e','Έ'=>'e',
    'έ'=>'e','Ζ'=>'z','ζ'=>'z','Η'=>'h','η'=>'h','Ή'=>'h','ή'=>'h','Θ'=>'th','θ'=>'th','Ι'=>'i','ι'=>'i','Ί'=>'i',
    'ί'=>'i','Ϊ'=>'i','ϊ'=>'i','ΐ'=>'i','Κ'=>'k','κ'=>'k','Λ'=>'l','λ'=>'l','Μ'=>'m','μ'=>'m','Ν'=>'n','ν'=>'n',
    'Ξ'=>'x','ξ'=>'x','Ο'=>'o','ο'=>'o','Ό'=>'o','ό'=>'o','Π'=>'p','π'=>'p','Ρ'=>'r','ρ'=>'r','Σ'=>'s','σ'=>'s','ς'=>'s',
    'Τ'=>'t','τ'=>'t','Υ'=>'u','υ'=>'u','Ύ'=>'u','ύ'=>'u','Ϋ'=>'u','ϋ'=>'u','ΰ'=>'u','Φ'=>'f','φ'=>'f','Χ'=>'ch','χ'=>'ch',
    'Ψ'=>'ps','ψ'=>'ps','Ω'=>'w','ω'=>'w','Ώ'=>'w','ώ'=>'w',	
                                                            
    );

	$string = strtr($string, $letters);
	if ($nonWordChars) 
	{
		$string = preg_replace("/\W/", " ", $string);
	}
	$string = trim($string);
	$string = str_replace("  ", " ", $string);
	$string = str_replace("  ", " ", $string);
	$string = str_replace("  ", " ", $string);
	$string = str_replace(" ", "-", $string);
	$string = str_replace("___", "_", $string);
	$string = str_replace("__", "_", $string);
	return substr($string, 0, 255);
}