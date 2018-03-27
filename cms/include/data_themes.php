<?php
namespace Cetera;
include('common_bo.php');

if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'update') {
	
	$data = json_decode(file_get_contents("php://input"), true);
	
	$theme = Theme::find($data['id']);
	if (!$theme) throw new \Exception('Theme "'.$data['id'].'" is not found');
	$theme->update($data);
	echo json_encode(array('success' => true));	
	die();
	
}

$themes = Theme::enum();
$client = new \GuzzleHttp\Client();

// проверка обновлений плагинов
if (sizeof($themes)) {

    $query = '?themes[]='.implode('&themes[]=', array_keys($themes));
    
    $data = array();
    
    try {
		$res = $client->get(THEMES_INFO.$query, ['verify'=>false]);
        $themes_lib = json_decode( $res->getBody(), true);
        
    } catch (\Exception $e) {}        
    
    foreach ($themes as $id => $p) {
		    
        if (isset($themes_lib[$id])) {
        		
            // есть более новая версия
            if (version_compare($themes_lib[$id]['version'], $p['version']) > 0) {
            
                $p['upgrade'] = $themes_lib[$id]['version'];
                
                // обновление невозможно, требуется более свежая CMS
                if ($themes_lib[$id]['cms_version_min'] && version_compare($themes_lib[$id]['cms_version_min'], VERSION) > 0 ) $p['upgrade'] = false;
                // обновление невозможно, CMS слишком новая
                if ($themes_lib[$id]['cms_version_max'] && version_compare($themes_lib[$id]['cms_version_max'], VERSION) <= 0 ) $p['upgrade'] = false;                      
            
            }
        
        }
        
        $data[] = array(
            'id'          => $p->name,
			'name'        => $p->name,
            'upgrade'     => $p['upgrade'],
            'description' => $p['description'],
            'version'     => $p['version'],
            'title'       => $p['title'],
			'author'      => $p['author'],
			'content'     => $p->getContentInfo(),
			'url'         => trim($p->getUrl(),'/'),
			'disableUpgrade' => $p->isDisableUpgrade(),
			'developerMode'  => $p->isDeveloperMode(),
			'repository'  => isset($themes_lib[$id])?$themes_lib[$id]['version']:false,
        );    
         
    
    }
    
}

echo json_encode(array(
    'success' => true,
    'rows'    => $data
));
