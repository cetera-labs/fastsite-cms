<?php
namespace Cetera;
include('common_bo.php');

if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

$t = $application->getTranslator();

$theme = Theme::find($_REQUEST['theme']);
if (!$theme)  throw new Exception\CMS($t->_('Тема не найдена'));

$conn = $application->getDbConnection();

if (sizeof($_POST)) {

    $rows = json_decode($_POST['rows']);
    if (is_object($rows)) $rows = array($rows);
    
    foreach ($rows as $r) {
    
        $s = Server::getById($r->id);
        if ($r->active)
            $s->setTheme($theme);
            else $s->setTheme();
            
        $theme->setConfig($r->config, $s);       
    
    }
	
    echo json_encode(array(
        'success' => true
    ));		

} else {

    $servers = Server::enum();
    if (sizeof($servers)) {
    
        
        $data = array();
        
        foreach ($servers as $s) {
        
            $data[] = array(
                'id'       => $s->id,
                'name'     => $s->name,
                'active'   => $s->theme->name == $theme->name,
                'config'   => $theme->loadConfig($s)->config
            );  
        }  
        
    }
    
    echo json_encode(array(
        'success' => true,
        'rows'    => $data
    ));

}
