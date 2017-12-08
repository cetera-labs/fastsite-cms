<?php
namespace Cetera;
/**
 * Cetera CMS 3
 * 
 * Действия с журналом
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
    'errors'  => array()
);


$action = $_POST['action'];

if ($action == 'clean') {
    $application->getConn()->executeQuery('TRUNCATE TABLE event_log');
    Event::trigger(EVENT_CORE_LOG_CLEAR);
} 

echo json_encode($res);