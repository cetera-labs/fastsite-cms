<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera; 
 
/**
 * Utility class
 * 
 * @package FastsiteCMS
 */ 
class Util {
	
	use DbConnection;
	
    /**
     * Нельзя создать экземпляр класса. Только статические методы.
     */         
    private function __construct() {}
        
    public static function dsCrypt($input,$decrypt=false) {
        
        if ($input == (int)$input) {
            return $input;
        }
        
        if ($decrypt==true) {
            if ( substr($input, -3) != 'O-P' || substr($input, 0, 2) != 'SQ' ) {
                return $input;
            }
            $input = substr($input, 2, -3);
        }
        else {
            $input = (string)$input;
        }

        $o = $s1 = $s2 = array(); // Arrays for: Output, Square1, Square2
        // формируем базовый массив с набором символов
        $basea = array('?','(','@',';','$','#',"]","&",'*'); // base symbol set
        $basea = array_merge($basea, range('a','z'), range('A','Z'), range(0,9) );
        $basea = array_merge($basea, array('!',')','_','+','|','%','/','[','.',' ') );
        $dimension=9; // of squares
        for($i=0;$i<$dimension;$i++) { // create Squares
            for($j=0;$j<$dimension;$j++) {
                $s1[$i][$j] = $basea[$i*$dimension+$j];
                $s2[$i][$j] = str_rot13($basea[($dimension*$dimension-1) - ($i*$dimension+$j)]);
            }
        }
        unset($basea);
        $m = floor(strlen($input)/2)*2; // !strlen%2
        $symbl = $m==strlen($input) ? '':$input[strlen($input)-1]; // last symbol (unpaired)
        $al = array();
        // crypt/uncrypt pairs of symbols
        for ($ii=0; $ii<$m; $ii+=2) {
            $symb1 = $symbn1 = strval($input[$ii]);
            $symb2 = $symbn2 = strval($input[$ii+1]);
            $a1 = $a2 = array();
            for($i=0;$i<$dimension;$i++) { // search symbols in Squares
                for($j=0;$j<$dimension;$j++) {
                    if ($decrypt) {
                        if ($symb1===strval($s2[$i][$j]) ) $a1=array($i,$j);
                        if ($symb2===strval($s1[$i][$j]) ) $a2=array($i,$j);
                        if (!empty($symbl) && $symbl===strval($s2[$i][$j])) $al=array($i,$j);
                    }
                    else {
                        if ($symb1===strval($s1[$i][$j]) ) $a1=array($i,$j);
                        if ($symb2===strval($s2[$i][$j]) ) $a2=array($i,$j);
                        if (!empty($symbl) && $symbl===strval($s1[$i][$j])) $al=array($i,$j);
                    }
                }
            }
            if (sizeof($a1) && sizeof($a2)) {
                $symbn1 = $decrypt ? $s1[$a1[0]][$a2[1]] : $s2[$a1[0]][$a2[1]];
                $symbn2 = $decrypt ? $s2[$a2[0]][$a1[1]] : $s1[$a2[0]][$a1[1]];
            }
            $o[] = $symbn1.$symbn2;
        }
        if (!empty($symbl) && sizeof($al)) // last symbol
            $o[] = $decrypt ? $s1[$al[1]][$al[0]] : $s2[$al[1]][$al[0]];
        $res = implode('',$o);
        if ($decrypt==false) {
            $res = 'SQ'.$res.'O-P';
        }
        return $res;
    }   
        
    /*
    * Вычисляет размер каталога
    * 
    * @param string $path путь
    * @return int
    * 
    */	
	public static function directorySize($path){
		$bytestotal = 0;
		$path = realpath($path);
		if($path!==false && $path!='' && file_exists($path)){
			foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $object){
				$bytestotal += $object->getSize();
			}
		}
		return $bytestotal;
	}

	/**
	* Форматирует число байт в кб, мб и т.д.
	*
	* @param int       bytes   is the size
	* @param bool      base10  enable base 10 representation, otherwise
	*                  default base 2  is used
	* @param int       round   number of fractional digits
	* @param array     labels  strings associated to each 2^10 or
	*                  10^3(base10==true) multiple of base units
	*/
	public static function hbytes($bytes, $base10=false, $round=2, $labels=array('', ' Kb', ' Mb', ' Gb')) {

	   if ((! is_array($labels)) ||
		   (count($labels) <= 0))
		   return null;
	   
	   $step = $base10 ? 3 : 10 ;
	   $base = $base10 ? 10 : 2;
	   
	   $log = (int)(log10($bytes)/log10($base));
	   
	   krsort($labels);
	   
	   foreach ($labels as $p=>$lab) {
		   $pow = $p * $step;
		   if ($log < $pow) continue;
		   $text = round($bytes/pow($base,$pow),$round) . $lab;
		   break;
	   }

	   return $text;
	}	
	
    /**
    * Send a GET requst using cURL
    * @param string $url to request
    * @param array $get values to send
    * @param array $options for cURL
    * @return string
    */
    public static function curlGet($url)
    {   
        $defaults = array(
            CURLOPT_URL => $url,
            //CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => TRUE,
            //CURLOPT_TIMEOUT => 4
        );
       
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        if( ! $result = curl_exec($ch))
        {
            trigger_error(curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }
	
	public static function clearAllCache($period = 1209600) {
		self::clearCache(WWWROOT.ImageTransform::PREFIX, $period);
		self::clearCache(IMAGECACHE_DIR, $period);
		self::clearCache(FILECACHE_DIR, $period);
		self::clearCache(TWIG_CACHE_DIR, $period);		
	}

	public static function clearCache($path, $period = 1209600) {
		$path = realpath($path);
		if($path!==false && $path!='' && file_exists($path)){
			foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $object){
				$stat = stat ( $object->getPathname() );
				
				if (time() - $stat['atime'] >= $period) {
					unlink($object->getPathname());
				}			
			}
		}	
	}	
    	
    public static function delTree($dir, $self = true) {
		if (!file_exists($dir)) return;
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
          (is_dir("$dir/$file") && !is_link("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
        }
        if ($self) return rmdir($dir);
		return;
    }  

    // copies files and non-empty directories
    function rcopy($src, $dst) {
      if (file_exists($dst)) rrmdir($dst);
      if (is_dir($src)) {
        mkdir($dst);
        $files = scandir($src);
        foreach ($files as $file)
        if ($file != "." && $file != "..") self::rcopy("$src/$file", "$dst/$file");
      }
      else if (file_exists($src)) copy($src, $dst);
    }    
    
    /*
    * Если аргумент строка - применяет к ней PHP-функцию addslashes()
    * Если аргумент массив - применяет к каждому элементу массива Util::addslashes()
    * 
    * @param mixed $value
    * @return mixed
    * 
    */
    public static function addslashes($value)
    {
      if (is_array($value)) {
        while (list($_nm,$_vl)=each($value)) {
    	  $value[$_nm] = self::addslashes($_vl);
    	}
    	$result = $value;
      } else {
        $result = addslashes($value);
      }
      return $result;
    }
    
    /*
    * Если аргумент строка - применяет к ней PHP-функцию stripslashes()
    * если аргумент массив - применяет к каждому элементу массива Util::stripslashes()
    * 
    * @param mixed $value
    * @return mixed
    * 
    */
    public static function stripslashes($value)
    {
      if (is_array($value)) {
        while (list($_nm,$_vl)=each($value)) {
    	  $value[$_nm] = self::stripslashes($_vl);
    	}
    	$result = $value;
      } else {
        $result = stripslashes($value);
      }
      return $result;
    }
    
    /*
    * Создает дубликат записи в таблице
    * 
    * @param string $table имя таблицы
    * @param string $id_row имя primary столбца (должен быть auto_increment)
    * @param integer $id ID записи, которая копируется
    * @param array $replace ассоциативный массив названий и значений столбцов, которые нужно заменить, а не копировать
    * @return int ID новой записи
    * @throws Exception\CMS    
    **/ 
    public static function copyRecord($table, $id_row, $id, $replace) {
		$conn = self::getDbConnection();
    	$row = $conn->fetchAssoc("SELECT * FROM $table WHERE $id_row='$id'");
    	if (!$row) throw new Exception\CMS('Source record id not found');
    	foreach ($replace as $name => $value)
    		if (isset($row[$name])) $row[$name] = $value;
    	unset($row[$id_row]);
		$conn->insert($table, $row);
    	return $conn->lastInsertId();
    }
           
    /*
    * Возвращает версию MySQL
    * 
    * @param string $ver версия в формате x.y.z
    */
    public static function getMysqlVersion() {
		
		$conn = self::getDbConnection();
		
        $data = $conn->fetchArray('SELECT VERSION()');
        if ($data[0]) {
            $my_ver = $data[0];
        } else {
			$data = $conn->fetchArray('SHOW VARIABLES LIKE \'version\'');
            if ($data[0]) {
				$my_ver = $data[0];
			}
        }
    
        if (!isset($my_ver)) $my_ver = '3.21.0';
    	return preg_replace('/[a-z\-]+/i', '', strtolower($my_ver));
    }
   
    /*
    * Возвращает $_GET[$name] или false, если $_GET[$name] не установлен
    * 
    * @param string $name имя параметра
    * @param bool $int возвращать как целое число  
    * @return mixed    
    */ 
    public static function get($name, $int = FALSE) {
    	if ($int) {
    		return (isset($_GET[$name]))?(int)$_GET[$name]:0;    
    	} else {
    		return (isset($_GET[$name]))?$_GET[$name]:FALSE;
    	}
    }
    
    /*
    * Возвращает $_POST[$name] или false, если $_POST[$name] не установлен
    * 
    * @param string $name имя параметра
    * @param bool $int возвращать как целое число  
    * @return mixed    
    */ 
    public static function post($name, $int = FALSE) {
    	if ($int) {
    		return (isset($_POST[$name]))?(int)$_POST[$name]:0;    
    	} else {
    		return (isset($_POST[$name]))?$_POST[$name]:FALSE;
    	}
    }
    
    public static function fatalError($msg) {
        echo '<div id="progress"><table width="100%" height="100%" class="x-panel-mc">'.
             '<tr><td align="center"><div class="panel"><h2>Внимание!</h2><b>'.$msg.'</b></div></td></tr></table></div>';
    }
    
    public static function commonHead() {
        echo '<link rel="stylesheet" type="text/css" href="/'.LIBRARY_PATH.'/extjs4/resources/css/ext-all.css">'. 
             '<script type="text/javascript" src="/'.LIBRARY_PATH.'/extjs4/ext-all.js"></script>'.
             '<script type="text/javascript" src="/'.LIBRARY_PATH.'/extjs4/compatibility/ext3-core-compat.js"></script>'.
             '<script type="text/javascript" src="/'.LIBRARY_PATH.'/extjs4/compatibility/ext3-compat.js"></script>'.
             '<link rel="stylesheet" type="text/css" href="/'.CMS_DIR.'/css/main.css">';
    }
    
    public static function utime()
    {
    	$time = explode( " ", microtime());
    	$usec = (double)$time[0];
    	$sec = (double)$time[1];
    	return $sec + $usec;
    }
    
    /*
    * Возвращает расширенное описание ошибки
    * 
    * @param Exception $e исключение
    * @return string    
    */
    public static function extErrorMessage($e)
    {
        if ($e instanceof Exception\CMS) {
            return $e->getExtMessage();
        } else {
            return 'In file <b>'.$e->getFile().'</b> on line: '.$e->getLine()."<br /><br /><b>Stack trace:</b><br />".nl2br($e->getTraceAsString());
        }
    }               

}
