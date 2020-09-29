<?php
namespace Cetera;
/**
 * Fastsite CMS 3
 * 
 * Действия с пользователями
 *
 * @package FastsiteCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
include('common_bo.php');

if (!$noauth && !$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

$t = $application->getTranslator();

$res = array(
    'success' => false,
    'message' => ''
);

try {

    if (isset($_REQUEST['action']) && $_REQUEST['action'] != 'upgrade') {
    
        if (!file_exists('../../'.UPGRADE_SCRIPT))
            load_upgrade_script();   
    
        include('../../'.UPGRADE_SCRIPT);
        die();
    
    } else {
    
        load_upgrade_script();   
        
        $res['next']    = array(
            'action' => 'upgrade',
            'url'    => '/'.UPGRADE_SCRIPT,
            'text'   => $t->_('Проверка совместимости'),
        );
        
        $res['message'] = '<span style="color:green;">OK</span><br>';          
        $res['success'] = true;     
    
    } 
    
} catch (Exception $e) {
  
     $res['message'] = '<span style="color:red;">'.$t->_('Ошибка').'</span><br>';
     if ($e->getMessage()) $res['message'] .= '<span style="color:red; font-size: 70%">'.$e->getMessage().'</span><br>';  
     
}

echo json_encode($res);

function load_upgrade_script() {
	
	$client = new \GuzzleHttp\Client();
	$res = $client->get( DISTRIB_HOST.UPGRADE_FILE );
	$d = $res->getBody();  	
	
    if (!$d) throw new Exception();
    file_put_contents(WWWROOT.UPGRADE_FILE, $d);    
    
    $zip = new \ZipArchive;
    if($zip->open(WWWROOT.UPGRADE_FILE)===TRUE) { 
    
    			if(!$zip->extractTo(WWWROOT))
              throw new Exception( $t->_('Не удалось распаковать архив').' '.WWWROOT.UPGRADE_FILE);   
              
    			$zip->close(); 
          unlink(WWWROOT.UPGRADE_FILE); 
          
    } else throw new Exception($t->_('Не удалось открыть архив').' '.WWWROOT.UPGRADE_FILE);   
}
