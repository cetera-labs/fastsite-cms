<?php
/**
 * Cetera CMS 3 
 * 
 * Соединение с БД, аутентификация пользователя  
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/ 
 
ob_start();
include_once('common.php');

$application->connectDb();
$application->initSession();
$application->initBo();

$translator = $application->getTranslator();  
include_once('field_editors.php');

$application->initPlugins();
$application->ping();

$user = $application->getUser();
if (!$user || !$user->allowBackOffice())
{
    if ($_SERVER['SCRIPT_NAME'] != '/'.CMS_DIR.'/login.php' && (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest'))
	{
        header('Location: /'.CMS_DIR.'/login.php');
        die();
    }
	else
	{
        throw new \Cetera\Exception\Auth();
    }
}
