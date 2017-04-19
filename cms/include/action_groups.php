<?php
namespace Cetera;
/**
 * Cetera CMS 3
 * 
 * Действия с группами  
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
$id = (int)$_POST['id'];

if ($action == 'delete') {
    $id = (int)$_POST['id'];
    if ($id > 0) {
        fssql_query('DELETE FROM users_groups_allow_cat WHERE group_id='.$id);
        fssql_query('DELETE FROM users_groups_deny_filesystem WHERE group_id='.$id);
        fssql_query('DELETE FROM users_groups_membership WHERE group_id='.$id);
        fssql_query('DELETE FROM users_groups WHERE id='.$id);
    }
    $res['success'] = true;
} 

if ($action == 'save') {
    $id = (int)$_POST['id'];
    
    if ($id >= 0) {
        $r = fssql_query('SELECT id FROM users_groups WHERE name="'.mysql_escape_string($_POST['name']).'" and id<>'.$id);
        if (mysql_num_rows($r)) throw new Exception_Form($translator->_('Группа с таким именем уже существует.'), 'name');
        
        if ($id > 0) {
            fssql_query('UPDATE users_groups SET name="'.mysql_escape_string($_POST['name']).'", describ="'.mysql_escape_string($_POST['describ']).'" WHERE id='.$id);
        } else {
            fssql_query('INSERT INTO users_groups SET name="'.mysql_escape_string($_POST['name']).'", describ="'.mysql_escape_string($_POST['describ']).'"');
            $id = mysql_insert_id();
        }
    }
    
    if (isset($_POST['remove']) && is_array($_POST['remove']))
        foreach($_POST['remove'] as $uid) if ($uid)
            fssql_query('DELETE FROM users_groups_membership WHERE user_id='.(int)$uid.' and group_id='.$id);
  
    if (isset($_POST['add']) && is_array($_POST['add']))
        foreach($_POST['add'] as $uid) if ($uid && ($uid != 1 || $id != GROUP_ADMIN))
            fssql_query('INSERT INTO users_groups_membership SET user_id='.(int)$uid.', group_id='.$id);
            
    $res['success'] = true;
}

echo json_encode($res);
?>
