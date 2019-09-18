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
    Event::trigger(EVENT_CORE_DIR_CREATE, ['message' => $nc->getBoUrl()]);
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
        $application->getConn()->executeQuery('TRUNCATE TABLE vars');
        if (isset($_POST['vars']))
            foreach ($_POST['vars'] as $var) {
				$application->getConn()->insert('vars', $var);
			}
    } else {
		$application->getConn()->delete('vars_servers', array('server_id' => $catalog->id));
        if (isset($_POST['vars']))
            foreach ($_POST['vars'] as $var) {
				$data = $application->getConn()->fetchAll('SELECT value FROM vars WHERE id=?',array((int)$var['id']));
				if (!count($data)) continue;
                if ($var['value'] != $data[0]['value']) {
					$data = $application->getConn()->insert('vars_servers', array(
						'var_id' => (int)$var['id'],
						'server_id' => $catalog->id,
						'value' => $var['value'],
					));
				}
            }      
    }

    $tpl = new Cache\Tag\Variable();
    $tpl->clean();
    
    Event::trigger(EVENT_CORE_DIR_EDIT, ['message' => $catalog->getBoUrl()]);
   
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
    Event::trigger(EVENT_CORE_DIR_DELETE, ['message' => $c->getBoUrl()]);
    $c->delete();
}

if ($_POST['action'] == 'hard_link_create') {
    $em = $application->getEntityManager();
    $structure_repo = $em->getRepository('\Cetera\Entity\Structure'); 
    
    $structure_repo->recoverParents();
    $em->flush();
    
    $createLink = function($parent, $source) use($em,$structure_repo,&$createLink) {
        $child = new \Cetera\Entity\Structure();
        $child->setParent($parent);
        $child->setSection($source->section);
        $em->persist($child);

        foreach($structure_repo->children($source, true) as $c) {
            $createLink($child, $c);
        }
    };
    
    $parent = $structure_repo->findOneById( $_POST['parent_structure_id'] );
    $source = $structure_repo->findOneById( $_POST['structure_id'] );   
    $createLink($parent, $source);
    
    $em->flush();
}

if ($_POST['action'] == 'cat_create') {
  
    if (!$user->allowCat(PERM_CAT_ADMIN, $_POST['parent']))
        throw new Exception\CMS(Exception\CMS::NO_RIGHTS);    
    
    $c = Catalog::getById($_POST['parent']); 
    $res['id'] = $c->createChild(array(
    	'name'		=> $_POST['name'],
    	'alias'		=> $_POST['alias'],
    	'typ'	  	=> $_POST['typ'],
    	'link'		=> $_POST['link'],
    	'server'    => $_POST['server'],
        'autoalias' => Catalog::AUTOALIAS_TRANSLIT
    ));
    
    $nc = Catalog::getById($res['id']);
    Event::trigger(EVENT_CORE_DIR_CREATE, [
		'message'=>$nc->getBoUrl()
	]);
	
	// https://pm.cetera.ru/browse/CCD-1166
	// Создает материал с тем же названием и alias=index в созданной папке одновременно. 
	if ($_POST['create_index']) {
		$m = Material::fetch([
			'idcat'   => $nc->id,
			'alias'   => 'index',
			'publish' => true,
			'autor'   => $user->id,
			'name'    => $_POST['name']
		], $_POST['typ']);
		$m->save();
	}
   
}

$res['success'] = true;

echo json_encode($res); 
