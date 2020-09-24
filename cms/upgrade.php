<?php
/**
 * Fastsite CMS 3
 * 
 * Действия с пользователями
 *
 * @package FastsiteCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
$action = $_REQUEST['action'];

set_time_limit(10000);

define('CMS_DIR', 'cms');

include_once(CMS_DIR.'/include/common_bo.php'); 
if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

$t = $application->getTranslator();

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING);

$upgrade_chain = array(
    'upgrade'      => array('action' => 'download',    'text' => $t->_('Загрузка обновления')),   
    'download'     => array('action' => 'extract',     'text' => $t->_('Установка')),
    'extract'      => array('action' => 'check_lib',   'text' => $t->_('Проверка доп. библиотек')),
    'download_lib' => array('action' => 'extract_lib', 'text' => $t->_('Установка библиотек')),
    'check_lib'    => array('action' => 'check_db',    'text' => $t->_('Проверка БД')),
    'extract_lib'  => array('action' => 'check_db',    'text' => $t->_('Проверка БД')),
);

$res = array(
    'success' => false,
    'message' => ''
);

if (isset($upgrade_chain[$action]))
    $res['next'] = $upgrade_chain[$action];


if ($action == 'upgrade') {

    $info = json_decode( getFile(DISTRIB_INFO), true);
    if ($_REQUEST['beta']) $info = $info['beta'];
    if (isset($info['require']['php']) && version_compare($info['require']['php'], phpversion()) > 0) {
    
        $res['message'] = '<span style="color:red;">'.sprintf($t->_('Требуется PHP %s или выше. Обновление невозможно.',$info['require']['php']).'</span><br>';
    
    } else {

        $res['success'] = true;
        $res['message'] = '<span style="color:green;">OK</span><br>'; 
    
    }  
    
} elseif ($action == 'download') {

    try {
    
        $info = json_decode( getFile(DISTRIB_INFO), true);
        if ($_REQUEST['beta']) $info = $info['beta'];
        
        if (!$info['file']) throw new Exception();
        $d = getFile(DISTRIB_HOST.$info['file']);
        if (!$d) throw new Exception();
        file_put_contents(WWWROOT.DISTRIB_FILE, $d);    
        
        $res['message'] = '<span style="color:green;">OK (v'.$info['version'].')</span><br>';          
        $res['success'] = true;     
    
    } catch (Exception $e) {  
         $res['message'] = '<span style="color:red;">'.$t->_('Не удалось получить файл').'</span><br>';
         if ($e->getMessage()) $res['message'] .= '<span style="color:red; font-size: 70%">'.$e->getMessage().'</span><br>';  
    }

} elseif ($action == 'extract') {

    try {
    
        $zip = new ZipArchive;      
        if($zip->open(WWWROOT.DISTRIB_FILE)===TRUE) { 
        
              $backup = str_replace(' ','_',WWWROOT.'cms_backup_'.VERSION);
              $backup_o = $backup;
              $i = 2;
              while (file_exists($backup)) $backup = $backup_o.'_('.$i++.')';
        
              try {        
                  rename(WWWROOT.CMS_DIR, $backup);
              } catch (Exception $e) {} 
			  
              if(!$zip->extractTo('.')){   
                  throw new Exception($t->_('Не удалось распаковать архив'));   
              }	
	      else {
        		  try {
                      unlink(WWWROOT.DISTRIB_FILE);	
                  } catch (Exception $e) {}		
        	  }
        	  $zip->close(); 
        } 
		else throw new Exception($t->_('Не удалось открыть архив'));  
        
        $res['message'] = '<span style="color:green;">OK</span><br>';          
        $res['success'] = true;              
    
    } 
	catch (Exception $e) {
    
        $res['message'] = '<span style="color:red;">'.$t->_('Ошибка обновления').'</span><br>';
        if ($e->getMessage()) $res['message'] .= '<span style="color:red; font-size: 80%">'.$e->getMessage().'</span><br>';   
        
    }

} elseif ($action == 'check_db') {

    $res['message'] = '';
    
    $schema = new Cetera\Schema(); 
    $msg = ''; 
    $module = '';
    $table = '';
    $tables = array();    
    $result = $schema->compare_schemas(TRUE, TRUE);
    if (sizeof($result)) foreach ($result as $error) {
    		
    		if (!isset($tables[$error['table']])) {
    		    $s = $schema->parseSchema($error['module']);
    			  $tables = array_merge($tables, $s['tables']);
    		}
    		
    		$query = $schema->get_fix_query($tables, $error);
    		
    		if ($query) $application->getConn()->executeQuery($query);
    		
    		$module = $error['module'];
    		$table = $error['table'];    
    }
    
    $res['message'] = '<span style="color:green;">OK</span><br>';              
    $res['success'] = true;      

} 
elseif ($action == 'check_lib' && defined('LIBRARY_VERSION_REQ')) {

    if (LIBRARY_VERSION < LIBRARY_VERSION_REQ) {
        if ($action != $_REQUEST['action']) {
            $res['message'] = '<span style="color:#666;">'.$t->_('Отмена. Необходимо обновление доп. библиотек.').'</span><br>'; 
        } else {
            $res['message'] = '<span style="color:green;">'.$t->_('Нуждается в обновлении').'</span><br>';
        } 
        $res['next'] = array('action' => 'download_lib', 'text' => $t->_('Загрузка доп. библиотек'));
    } else {
        $res['message'] = '<span style="color:green;">OK</span><br>'; 
    }
    $res['success'] = true;  

} 
elseif ($action == 'download_lib') {

    try {
    
        $d = getFile(DISTRIB_HOST.LIBRARY_FILE);
        if (!$d) throw new Exception();
        file_put_contents(WWWROOT.LIBRARY_FILE, $d);    
        
        $res['message'] = '<span style="color:green;">OK</span><br>';          
        $res['success'] = true;     
    
    } catch (Exception $e) {  
         $res['message'] = '<span style="color:red;">'.$t->_('Не удалось получить файл').'</span><br>';
         if ($e->getMessage()) $res['message'] .= '<span style="color:red; font-size: 70%">'.$e->getMessage().'</span><br>';  
    }

} 
elseif ($action == 'extract_lib') {

    try {
    
        $zip = new ZipArchive;
        if($zip->open(WWWROOT.LIBRARY_FILE)===TRUE) { 
        
              /*
              $backup = str_replace(' ','_',WWWROOT.'library_backup_'.LIBRARY_VERSION);
              $backup_o = $backup;
              $i = 2;
              while (file_exists($backup)) $backup = $backup_o.'_('.$i++.')';
        
              try {
                  rename(WWWROOT.LIBRARY_PATH, $backup);
              } catch (Exception $e) {} 
              */
              delTree(WWWROOT.LIBRARY_PATH);

              if(!$zip->extractTo('.')) {
                  throw new Exception($t->_('Не удалось распаковать архив'));   
              }	else {
                  try {
                      unlink(WWWROOT.LIBRARY_FILE);	
                  } catch (Exception $e) {}		
              }
              $zip->close(); 
        } else throw new Exception($t->_('Не удалось открыть архив'));  
        
        $res['message'] = '<span style="color:green;">OK</span><br>';          
        $res['success'] = true;              
    
    } catch (Exception $e) {
    
        $res['message'] = '<span style="color:red;">'.$t->_('Ошибка обновления').'</span><br>';
        if ($e->getMessage()) $res['message'] .= '<span style="color:red; font-size: 80%">'.$e->getMessage().'</span><br>';
        
    }

} 
else {
    
    $res['message'] = '<span style="color:#666;">'.$t->_('Не требуется.').'</span><br>'; 
    $res['success'] = true;  
    
}


echo json_encode($res);
die();

function getFile( $file ) {
	
	if (class_exists('\GuzzleHttp\Client')) {
		$client = new \GuzzleHttp\Client();
		$res = $client->get( $file );
		return $res->getBody(); 
	} else {
		return file_get_contents( $file );
	}
	
}

function delTree($dir) {
       $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
          (is_dir("$dir/$file") && !is_link("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
} 