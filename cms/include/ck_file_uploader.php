<?php
namespace Cetera;
header('X-Frame-Options: SAMEORIGIN');
include_once('common_bo.php');

if ($_FILES['upload']['error']) { 
    switch ($_FILES['upload']['error']) {
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
    die($msg);
}

$path = trim($_REQUEST['path'],'/').'/';
  
if (!file_exists(DOCROOT.$path)) mkdir(DOCROOT.$path,0777);
   
if (!is_writable(DOCROOT.$path)) {
    die(sprintf($translator->_('Каталог "%s" недоступен для записи'),DOCROOT.$path));
}


check_upload_file_name($_FILES["file"]["name"]);

while(file_exists(DOCROOT.$path.$_FILES["upload"]["name"]))
    $_FILES["upload"]["name"] = 'cp_'.$_FILES["upload"]["name"];
    
move_uploaded_file($_FILES["upload"]["tmp_name"], DOCROOT.$path.$_FILES["upload"]["name"]);

check_upload_file( DOCROOT.$path.$_FILES["upload"]["name"] );

?>
<script>
window.parent.CKEDITOR.tools.callFunction(<?=$_GET['CKEditorFuncNum']?>, '/<?=$path.$_FILES["upload"]["name"]?>');
</script>