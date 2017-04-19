<?php
namespace Cetera;
/**
 * Cetera CMS 3
 * 
 * Действия с пользователями
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
include('common_bo.php');

if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

$res = array(
    'success' => false,
    'message' => ''
);

$application->setVar( $_REQUEST['name'], $_REQUEST['value'] );   
$res['success'] = true;

echo json_encode($res);