<?php
namespace Cetera;
include('common_bo.php');

if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

$plugins = Plugin::enum();
$client = new \GuzzleHttp\Client();

// проверка обновлений плагинов
if (sizeof($plugins)) {
    $query = '?plugins[]='.implode('&plugins[]=', array_keys($plugins));
    
    $data = array();
    
    try {
    
		$res = $client->get(PLUGINS_INFO.$query);
		$d = $res->getBody();  		
        $plugins_lib = json_decode( $res->getBody(), true);
        
    } catch (\Exception $e) {}    

	$l = (string)$application->getLocale();
    
    foreach ($plugins as $id => $p) {
            
        $data[] = array(
            'id'          => $p->name,
            'upgrade'     => false,
            'description' => isset($p['description_'.$l])?$p['description_'.$l]:$p['description'],
            'version'     => $p['version'],
            'disabled'    => !$p->isEnabled(),
            'title'       => isset($p['title_'.$l])?$p['title_'.$l]:$p['title'],
            'composer'    => $p->composer,
        );            
    
    }
    
}

echo json_encode(array(
    'success' => true,
    'rows'    => $data
));