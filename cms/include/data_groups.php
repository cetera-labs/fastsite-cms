<?php
namespace Cetera;
/************************************************************************************************

Список материалов

*************************************************************************************************/

include('common_bo.php');

if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

$data = $application->getGroups();

if (!isset($_REQUEST['all'])) unset($data[GROUP_ALL]);

$r =  $application->getConn()->fetchAll('SELECT *, 1 as user_defined FROM users_groups ORDER BY name');
foreach ($r as $f) $data[$f['id']] = $f;

$d = array();

if (isset($_REQUEST['member']) && $_REQUEST['member']) {
	
    $u = User::getById($_REQUEST['member']);
    if ($u) 
        foreach ($u->getGroups() as $gid) 
            if (isset($data[$gid])) 
                $d[] = $data[$gid];
	
} elseif (isset($_REQUEST['avail']) && $_REQUEST['avail']) {
	
    $u = User::getById($_REQUEST['avail']);
    if ($u) foreach ($u->getGroups() as $gid) 
        if (isset($data[$gid])) unset($data[$gid]);
    $d = array_values($data);
	
} else {
	
    $d = array_values($data);
	
}


echo json_encode(array(
    'success' => true,
    'rows'    => $d
));