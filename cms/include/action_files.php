<?php
namespace Cetera;
/**
 * Cetera CMS 3 
 * 
 * AJAX-backend загрузка файлов 
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/

header('X-Frame-Options: SAMEORIGIN');
include_once('common_bo.php');

$res = array(
    'success' => false,
);

if ($_REQUEST['action'] == 'get_twig_template_file') {
	
    $c = Catalog::getById($_REQUEST['section_id']); 
	$application->setServer($c);	
	$dir = $application->getTemplateDir();
	$res['success'] = true;
	$res['filename'] = str_replace(DOCROOT,'/',$application->getTemplateDir().'/'.TWIG_TEMPLATES_PATH.'/'.$_REQUEST['name']);

}

if ($_REQUEST['action'] == 'file_info') {
	
	$file = ltrim($_REQUEST['file'],'/');
	$file = DOCROOT.$file;
	if (file_exists($file))
	{
		$res['exists'] = true;
	}
	else
	{
		$res['exists'] = false;
	}
	$res['success'] = true;

}

if ($_REQUEST['action'] == 'save_file') {
	
	$file = ltrim($_REQUEST['file'],'/');
	if (!check_file($file))
	{
		$res['success'] = false;
		$res['deny'] = true;
	}
	else
	{	
	
		$file = DOCROOT.$file;
		
		$p = pathinfo( $file );
		
		if (!file_exists($p['dirname']))
		{
			mkdir($p['dirname'], 0777, true);
		}
		
		$r = file_put_contents( $file, $_REQUEST['data'] );
		
		if ($r === FALSE) {
			$res['success'] = false;
			$res['message'] = $translator->_('Невозможно сохранить файл');
		} else {
			$res['success'] = true;
			$res['extension'] = $p['extension'];		
		}
	
	}
}

if ($_REQUEST['action'] == 'get_file') {

	$file = ltrim($_REQUEST['file'],'/');
	if (!check_file($file, true)) {
		$res['success'] = false;
		$res['deny'] = true;
	} else {

		$file = DOCROOT.$file;
		if (!file_exists($file)) {
			$res['success'] = false;
		} else {	
			$res['success'] = true;
			$res['readonly'] = !is_writable( $file );
      
			$p = pathinfo( $file );
			$res['data'] = file_get_contents( $file );
			
			$res['extension'] = $p['extension'];
		}
	
	}
	
}

function check_file($file, $readonly = false) {
	$p = pathinfo( $file );
	if ( $p['dirname'] == '' || $p['dirname'] == '.') return FALSE;
	if (0 === strpos( $p['dirname'], CMS_DIR) ) 
	{
		if (!$readonly || false === strpos( $p['dirname'], CMS_DIR.'/widgets/')) return FALSE;
	}
	if (0 === strpos( $p['dirname'], LIBRARY_PATH)) return FALSE;
	if (0 === strpos( $p['dirname'], PLUGIN_DIR)) 
	{	
		if (!$readonly || false === strpos( $p['dirname'], '/widgets/')) return FALSE;
	}
	if (0 === strpos( $p['dirname'], '.cache')) return FALSE;
	if (in_array($p['extension'], array('php','phtml','pl','prefs','htaccess'))) return FALSE;
	return TRUE;
}

if ($_REQUEST['action'] == 'delete_folder') {

    rmdir(DOCROOT.ltrim($_REQUEST['path'],'/'));
    $res['success'] = true;
}

if ($_REQUEST['action'] == 'create_folder') {

    $dir = DOCROOT.ltrim($_REQUEST['path'],'/').$_REQUEST['name'];
    mkdir($dir,0777);
    chmod($dir,0777);
    $res['success'] = true;
    
}

if ($_REQUEST['action'] == 'upload') {

    if ($_FILES['file']['error']) { 
        switch ($_FILES['file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $msg = $translator->_('Слишком большой размер файла');
                break;
            case UPLOAD_ERR_PARTIAL:
                $msg = $translator->_('Ошибка. Загружена лишь часть файла');
                break;   
            case UPLOAD_ERR_NO_FILE:
                $msg = $translator->_('Ошибка. Нет файла');
                break;   
            case UPLOAD_ERR_NO_TMP_DIR:
                $msg = $translator->_('Не найден временный каталог для сохранения загруженного файла');
                break;   
            case UPLOAD_ERR_CANT_WRITE:
                $msg = $translator->_('Ошибка при сохранении загруженного файла');
                break;   
            case UPLOAD_ERR_EXTENSION:
                $msg = $translator->_('Невозможно загрузить данный тип файла');
                break;          
        }
        $res['success'] = false;
        $res['message'] = $msg;
        
    } else {
    
        $path = trim($_REQUEST['path'],'/').'/';
           
		if (!file_exists(DOCROOT.$path)) mkdir(DOCROOT.$path,0777);
		   
        if (!is_writable(DOCROOT.$path)) {
            if ($_POST['showPath'] == 'true') $field = 'path'; else $field = 'file';
            throw new Exception\Form(sprintf($translator->_('Каталог "%s" недоступен для записи'), DOCROOT.$path), $field);
        }
        
        $s = $application->getSession();
        $s->last_upload_path = '/'.$path;
        
        check_upload_file_name($_FILES["file"]["name"]);
        
        $n = 2;
        $fname_orig = explode('.',$_FILES["file"]["name"]);
        
        while (file_exists(DOCROOT.$path.$_FILES["file"]["name"])) {
            $dummy = $fname_orig;
            $dummy[0] .= '_'.$n++;
            $_FILES["file"]["name"] = implode('.', $dummy);
        }
        
        move_uploaded_file($_FILES["file"]["tmp_name"], DOCROOT.$path.$_FILES["file"]["name"]);
        
        check_upload_file( DOCROOT.$path.$_FILES["file"]["name"] );
        
        $res['success'] = true;
        $res['file'] = $_FILES["file"]["name"];
    
    }
}

if ($_REQUEST['action'] == 'upload_path') {
    $res['success'] = true;
    $res['path'] = USER_UPLOAD_PATH.date('Ymd').'/';
}

if ($_REQUEST['action'] == 'delete') {
    if (!unlink(DOCROOT.ltrim(rtrim($_REQUEST['path'],'.htaccess'),'/'))) throw new Exception\CMS($translator->_('Не удалось удалить файл.'));
    $res['success'] = true;
}

echo json_encode($res); 
