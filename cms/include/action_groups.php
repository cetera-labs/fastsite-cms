<?php
namespace Cetera;
/**
 * Fastsite CMS 3
 * 
 * Действия с группами  
 *
 * @package FastsiteCMS
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
        $application->getConn()->executeQuery('DELETE FROM users_groups_allow_cat WHERE group_id='.$id);
        $application->getConn()->executeQuery('DELETE FROM users_groups_deny_filesystem WHERE group_id='.$id);
        $application->getConn()->executeQuery('DELETE FROM users_groups_membership WHERE group_id='.$id);
        $application->getConn()->executeQuery('DELETE FROM users_groups WHERE id='.$id);
    }
    $res['success'] = true;
} 

if ($action == 'save') {
    $id = (int)$_POST['id'];
    
    if ($id >= 0) {
		
		$d = $application->getConn()->fetchAll('SELECT id FROM users_groups WHERE name=? and id<>?', array($_POST['name'], $id));
        if (count($d)) throw new Exception_Form($translator->_('Группа с таким именем уже существует.'), 'name');
        
        if ($id > 0) {
			$application->getConn()->update('users_groups',array(
				'name' => $_POST['name'],
				'describ' => $_POST['describ'],
			),array(
				'id' => $id
			));
        } else {
			$application->getConn()->insert('users_groups',array(
				'name' => $_POST['name'],
				'describ' => $_POST['describ'],
			));			
            $id = $application->getConn()->lastInsertId();
        }
    }
    
    if (isset($_POST['remove']) && is_array($_POST['remove']))
        foreach($_POST['remove'] as $uid) if ($uid) {
			$application->getConn()->delete('users_groups_membership',array(
				'user_id' => (int)$uid,
				'group_id' => $id,
			));
		}
  
    if (isset($_POST['add']) && is_array($_POST['add']))
        foreach($_POST['add'] as $uid) if ($uid && ($uid != 1 || $id != GROUP_ADMIN)) {
			$application->getConn()->insert('users_groups_membership',array(
				'user_id' => (int)$uid,
				'group_id' => $id,
			));	
		}
            
    $res['success'] = true;
}

echo json_encode($res);