<?php
namespace Cetera;

$step = (isset($_REQUEST['step']))?(int)$_REQUEST['step']:0;

include('common.php');

if ($application->getVar('setup_done')) die();

if ($step >= 4) {
	$application->connectDb();
	$application->initSession();
}
$translator = $application->getTranslator();

if (isset($_REQUEST['locale'])) {
	$application->setLocale($_REQUEST['locale'], $step >= 4);
}

$res = array();

if ($step == 9) {
	set_time_limit(999999);

    $res['success'] = true;    
    $res['error'] = false;
	
	if ($_POST['create'])
	{
	
		$theme = 'corp';
		if ($_POST['theme']) $theme = $_POST['theme'];
		
		$str = '<div class="scroll" style="width: 475px;"><table class="setup" cellspacing="0" cellpadding="0" width="100%" border="0">';
			  
			try {
			
				Theme::install($theme, function($text, $start) use (&$str) { 
					if ($start) 
						$str .= '<tr><td class="left">'.$text.'</td><td>'; 
						else $str .= status(0).'</td></tr>';
				}, $translator, null, true);  
				
				$f = fopen(PREFS_FILE,'a');
				fwrite($f,"setup_theme=1\n");
				fclose($f);		
		
			} catch (\Exception $e) {
			
				$res['error'] = true;
				$res['success'] = false;
				$str .= status(1, $e->getMessage()).'</td></tr>';
				
			} 
			 
		$str .= '</table></div>';
		$res['text'] = $str;
    
	}	
    
    if (!$res['error']) finish_setup(); 
        
}

if ($step == 6) {

    try {

        $res['success'] = true; 
    
        if ($_POST['password'] != $_POST['password2']) {
            $res['success'] = false;
            $res['errors']['password'] = $translator->_('Пароли не совпадают');
            $res['errors']['password2'] = $translator->_('Пароли не совпадают');
        } else {
            fssql_query('DELETE FROM users WHERE id=1');
            fssql_query('DELETE FROM users WHERE login="'.mysql_escape_string($_POST['login']).'"');
            fssql_query('INSERT INTO users SET id=1, login="'.mysql_escape_string($_POST['login']).'", password="'.md5($_POST['password']).'", email="'.mysql_escape_string($_POST['email']).'", date_reg=NOW(), disabled=0');
        }
    
    } catch (Exception $e) {
    
        $res['success'] = false;
        $res['message'] = $e->getMessage();     
    
    }

}

elseif ($step == 5) {
	$res['error'] = false;
		
	$str = '<div class="scroll" style="width: 475px;"><table class="setup" cellspacing="0" cellpadding="0" width="100%" border="0">';

    $str .= '<tr><td class="left">'.$translator->_('Установка кодировки БД').'</td><td>';
    try {
        $application->getConn()->executeQuery( 'ALTER DATABASE `'.$application->getVar('dbname').'` DEFAULT CHARSET utf8' );
	      $str .= status(0);
    } catch (\Exception $e) {
        $res['error'] = true;
        $str .= status(1, $e->getMessage());
    }
    $str .= '</td></tr>';
    
    if (!$res['error']) {
    
        $schema = new Schema();
        foreach($schema->schemas as $key => $schm) {
            if ($schm['schema']) {
                $str .= '<tr><td class="left">'.$translator->_('Структура').' <b>'.$schm['name'].'</b></td><td>';
                try {
                    $schema->readSchema($key);
                    $str .= status(0);
                } catch (Exception $e) {
                    $res['error'] = TRUE;
                    $str .= status(1, $e->getMessage());
                }
                $str .= '</td></tr>';
            }
            
            if ($schm['sql']) {
                $str .= '<tr><td class="left">'.$translator->_('Данные').' <b>'.$schm['name'].'</b></td><td>';
                try {
                    $schema->readDump($key);
                    $str .= status(0);
                } catch (Exception $e) {
                    $res['error'] = TRUE;
                    $str .= status(1, $e->getMessage());
                }
                $str .= '</td></tr>';
            }
        }   
    }
    
    $str .= '</table></div>';
    $res['text'] = $str;
} 

elseif ($step == 3) {

    $res['success'] = true;
    mysql_connect($_POST['dbhost'],$_POST['dbuser'],$_POST['dbpass']);
    if(!mysql_select_db($_POST['dbname'])) {
        $res['message'] = $translator->_('Ошибка при выборе базы данных').'<br />'.mysql_error();
        fssql_query('CREATE DATABASE `'.$_POST['dbname'].'`');
        if (!mysql_select_db($_POST['dbname'])) throw new Exception\CMS(mysql_error());
    }
    
    $my_ver = Util::getMysqlVersion();	

    if (version_compare(MYSQL_VER, $my_ver) > 0)
        throw new Exception\CMS(sprintf($translator->_('Требуется версия MySQL не ниже %s<br />Обнаружена версия &mdash; %s'),MYSQL_VER,$my_ver));
        
    $f = fopen(PREFS_FILE,'w');
	fwrite($f,"dbhost=".$_POST['dbhost']."\n");
	fwrite($f,"dbname=".$_POST['dbname']."\n");
	fwrite($f,"dbuser=".$_POST['dbuser']."\n");
	fwrite($f,"dbpass=".$_POST['dbpass']."\n");
	fclose($f);
	
}

elseif ($step == 2) {

    $res['error'] = false;
	  $res['warning'] = false;
	
    $str = '<div align="center" class="scroll" style="width: 475px;"><h1>'.$translator->_('Проверка окружения').'</h1><table class="setup" cellspacing="0" cellpadding="0" width="100%" border="0">';
    $str .= '<tr><td>&nbsp;</td><td>'.$translator->_('требуется').'</td><td>'.$translator->_('обнаружено').'</td><td>&nbsp;</td></tr>';
    
    $version = version_compare(PHP_VER, phpversion()) > 0;
    if ($version) $res['error'] = true;
    $str .= '<tr><td class="left">PHP</td><td><b>'.PHP_VER.'</b></td><td><b>'.phpversion().'</b></td><td>'.status($version).'</td></tr>';
    
    $mbfo = ini_get ('mbstring.func_overload');
    if ($mbfo > 0) $res['error'] = true;
    $str .= '<tr><td class="left">mbstring.func_overload</td><td><b>0</b></td><td><b>'.$mbfo.'</b></td><td>'.status($mbfo > 0).'</td></tr>';   
   
    $str .= '<tr class="hr"><td colspan="4">&nbsp;</td></tr>';
    $str .= '<tr><th colspan="4">'.$translator->_('Файловая система').':</th></tr>'; 
    ob_start();
    
    $mode = 2;
    print '<tr><td colspan="3" class="left">';
    if (copy(CMSROOT.'.htaccess_distr',DOCROOT.'.htaccess')) {
        print 'Файл <b>.htaccess</b></td><td>'.status(0).'</td></tr>'; 
    } else {
        print 'Не удалось создать <b>.htaccess</b></td><td>'.status(0).'</td></tr>'; 
        print '</td><td>'.status(2).'</td></tr>';    
    }
    
    if (check_file(PREFS_FILE, 1)) $res['error'] = true;
    if (check_dir(TEMPLATES_DIR, 1, FALSE)) $res['error'] = true;
    if (check_dir(CACHE_DIR, 2)) $res['warning'] = true;
    if (check_dir(IMAGECACHE_DIR, 2)) $res['warning'] = true;
    if (check_dir(FILECACHE_DIR, 2)) $res['warning'] = true;
    check_dir(USER_UPLOADS_DIR, 2);
    check_dir(DOCROOT.PLUGIN_DIR, 2);
    check_dir(DOCROOT.THEME_DIR, 2);
    $str .= ob_get_contents();
    ob_end_clean(); 
    
    $str .= '</table><hr size="1">';
    if ($res['error']) {
       $str .= $translator->_('В процессе проверки конфигурации сервера были обнаружены критические ошибки.<br /><b>Установка невозможна.</b>');
    } else {
       if ($res['warning']) $str .= $translator->_('В процессе проверки конфигурации сервера были обнаружены некоторые проблемы. Рекомендуется устранить их перед продолжением установки.');
       else $str .= $translator->_('Проверка закончена');
    }
    $str .= '</div>';
    $res['text'] = $str;
}

echo json_encode($res);
exit;

function finish_setup() {
    $f = fopen(PREFS_FILE,'a');
    fwrite($f,"setup_done=1\n");
    fclose($f);  
}

function check_file($file, $mode) {
    global $translator;
    
	$error = 0;
	$fl = '<b>'.str_replace(DOCROOT,'',$file).'</b>';
	print '<tr><td colspan="3" class="left">';
    if (@file_exists($file)) {
      if (!is_writable($file)) {
        printf($translator->_('Файл %s недоступен для записи'), $fl);
		    $error = 1;
      } 
    } else {
      try {
        $f = fopen($file,'w');
        fclose($f);
      } catch (Exception $e) {
        printf($translator->_('Невозможно создать файл %s'), $fl);
        $error = 1;
      }
    }
	if (!$error) {
		print $translator->_('Файл').' '.$fl;
		$mode = 0;
	}
	print '</td><td>'.status($mode).'</td></tr>';
	return $error;
}

function check_dir($dir, $mode, $write = TRUE) {
    global $translator;
    
	$error = 0;
	$fl = '<b>'.str_replace(DOCROOT,'',$dir).'</b>';
	print '<tr><td colspan="3" class="left">';
    if ((file_exists($dir))&&(is_dir($dir))) {
        if ($write && !is_writable($dir)) {
	  	    printf($translator->_('Каталог %s недоступен для записи'), $fl);
		    $error = 1;
	    }
    } else {
		$dr = str_replace(DOCROOT,'',$dir);
        $chains = explode('/', trim($dr,'/'));
        $d = DOCROOT;
        $old = umask(0);
        foreach($chains as $chain) {
            if ($d != DOCROOT) $d .= '/';
            $d .= $chain;
            if (!is_dir($d)) {
                try {
                    mkdir ($d, 0777);
                } catch (Exception $e) {
            	  	printf($translator->_('Невозможно создать каталог %s'), $fl);
            		$error = 1;
            		break;
                }
            }
        }
        umask($old);
    }
	if (!$error) {
		print $translator->_('Каталог').' '.$fl;
		$mode = 0;
	}
	print '</td><td>'.status($mode).'</td></tr>';
	return $error;
}

function status($v, $title = '') {
    global $translator;
    
	if ((int)$v == 2) return '<b class="warning">&nbsp;'.$translator->_('Внимание!').'&nbsp;</b>';
	if ($v) return '<b class="error" title="'.htmlspecialchars($title).'">&nbsp;'.$translator->_('Ошибка!').'&nbsp;</b>';
	else return '<b class="ok">&nbsp;ОK&nbsp;</b>';
}
?>
