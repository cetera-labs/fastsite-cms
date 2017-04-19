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
    'errors'  => array()
);


$action = $_POST['action'];
$sel = $_POST['sel'];

if ($action == 'delete') {
    foreach($sel as $uid) {
        if ($uid == $user->id) continue;
        $u = User::getById($uid);
        if ($u) $u->delete();
    }
} 

elseif ($action == 'enable') {
    fssql_query('UPDATE users SET disabled=0 WHERE id<>0 and id IN ('.implode(',',$sel).')');
}

elseif ($action == 'disable') {
    fssql_query('UPDATE users SET disabled=1 WHERE id<>1 and id<>'.(int)$user->id.' and id IN ('.implode(',',$sel).')');
}

echo json_encode($res);
?>
