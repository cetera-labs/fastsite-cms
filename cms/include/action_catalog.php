<?php
namespace Cetera;
/**
 * Cetera CMS 3 
 * 
 * AJAX-backend действия с разделами 
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/

include_once('common_bo.php');

$res = array(
    'success' => false,
);

if ($_REQUEST['action'] == 'get_path_info') {
    $id = (int)$_REQUEST['id'];
    try {
        $c = Catalog::getById($id);
        $res['treePath'] = $c->getTreePath();
        $res['displayPath'] = $c->getPath()->implode();
    } catch (\Exception $e) {
        $res['treePath'] = '';
        $res['displayPath'] = '';    
    }
}

if ($_POST['action'] == 'cat_copy') {
    $id = (int)$_REQUEST['id'];

    if (!$user->allowCat(PERM_CAT_ADMIN, $id))
        throw new Exception\CMS(Exception\CMS::NO_RIGHTS);
    
    $c = Catalog::getById($id);
    set_time_limit(1000);
    $new = $c->copy($_POST['dest'], $_POST['subs'], $_POST['math']);
    $nc = Catalog::getById($new);
    $res['path'] = $nc->getTreePath();
    $application->eventLog(EVENT_DIR_CREATE, $nc->getBoUrl());
}

if ($_POST['action'] == 'cat_prefs') {

    $id = (int)$_REQUEST['id'];

    if (!$user->allowAdmin()) 
        throw new Exception\CMS(Exception\CMS::NO_RIGHTS);
        
    $catalog = Catalog::getById($id); 
    
    $catalog->update($_POST);    
    
    $res['success'] = true;   
}

if ($_POST['action'] == 'cat_save') {

    $id = (int)$_REQUEST['id'];

    if (!$user->allowCat(PERM_CAT_ADMIN, $id)) 
        throw new Exception\CMS(Exception\CMS::NO_RIGHTS);
        
    $catalog = Catalog::getById($id);
        
    if (!$user->allowAdmin()) unset($_POST['permissions']); 
    
    if (isset($_POST['parentid']) && ((int)$_POST['parentid'] != $catalog->parent->id)) 
        $catalog->move((int)$_POST['parentid']);
        
    if (!isset($_POST['hidden'])) $_POST['hidden'] = 0;
    if (!isset($_POST['autoalias'])) $_POST['autoalias'] = 0;
    
    
	
	if ($catalog->isServer() && $user->allowAdmin())
	{
		$catalog->setRobots($_POST['_robots_txt']);
		unset($_POST['_robots_txt']);
	}
	
	$catalog->update($_POST);
    
    if ($catalog->isRoot()) {
        fssql_query('TRUNCATE TABLE vars');
        if (isset($_POST['vars']))
            foreach ($_POST['vars'] as $var) 
                fssql_query('INSERT INTO vars SET '.($var['id']?'id='.(int)$var['id'].', ':'').'name="'.mysql_escape_string($var['name']).'", value="'.mysql_escape_string($var['value']).'", describ="'.mysql_escape_string($var['describ']).'"');
    } else {
        fssql_query('DELETE FROM vars_servers WHERE server_id='.$catalog->id);
        if (isset($_POST['vars']))
            foreach ($_POST['vars'] as $var) {
                $r = fssql_query('SELECT value FROM vars WHERE id='.(int)$var['id']);
                if (!mysql_num_rows($r)) continue;
                if ($var['value'] != mysql_result($r,0))
                    fssql_query('INSERT INTO vars_servers SET var_id='.(int)$var['id'].', server_id='.$catalog->id.', value="'.mysql_escape_string($var['value']).'"');
            }      
    }

    $tpl = new Cache\Tag\Variable();
    $tpl->clean();
    
    $application->eventLog(EVENT_DIR_EDIT, $catalog->getBoUrl());
   
    $res['success'] = true;
    $res['path'] = $catalog->getTreePath();

}

if ($_POST['action']=='up' || $_POST['action']=='down') {

	if ($_POST['id']) {
		if (!$user->allowCat(PERM_CAT_ADMIN, $_POST['id']))
			throw new Exception\CMS(Exception\CMS::NO_RIGHTS);
			
		$c = Catalog::getById($_POST['id']);
		$c->shift($_POST['action'] == 'up');
	}

}

if ($_POST['action'] == 'cat_delete') {

    if (!$user->allowCat(PERM_CAT_ADMIN, $_POST['id'])) 
        throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

    $c = Catalog::getById($_POST['id']);
    $application->eventLog(EVENT_DIR_DELETE, $c->getBoUrl());
    $c->delete();
}

if ($_POST['action'] == 'cat_create') {
  
    if (!$user->allowCat(PERM_CAT_ADMIN, $_POST['parent']))
        throw new Exception\CMS(Exception\CMS::NO_RIGHTS);    
    
    $c = Catalog::getById($_POST['parent']); 
    $res['id'] = $c->createChild(array(
    	'name'		  => $_POST['name'],
    	'alias'		  => $_POST['tablename'],
    	'typ'	  	  => $_POST['typ'],
    	'link'		  => $_POST['link'],
    	'server'    => $_POST['server'],
      'autoalias' => Catalog::AUTOALIAS_TRANSLIT
    ));
    
    $nc = Catalog::getById($res['id']);
    $application->eventLog(EVENT_DIR_CREATE, $nc->getBoUrl());
   
}

$res['success'] = true;

echo json_encode($res); 
