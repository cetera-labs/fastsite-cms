<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera; 
 
/**
 * Utility class
 * 
 * @package CeteraCMS
 */ 
class Util {
	
	use DbConnection;
	
    /**
     * Нельзя создать экземпляр класса. Только статические методы.
     */         
    private function __construct() {}
    
	
	
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
    	
    public static function delTree($dir) {
       $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
          (is_dir("$dir/$file") && !is_link("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
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
