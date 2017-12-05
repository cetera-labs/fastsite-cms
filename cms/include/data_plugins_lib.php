<?php
namespace Cetera;
include('common_bo.php');

if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

header('Content-Type: application/json');

try {

	$client = new \GuzzleHttp\Client();
    $res = $client->get(PLUGINS_INFO, [
		'verify' => false,
		'query'  => $_GET
	]);
    $data = $res->getBody();   

    $plugins = json_decode( $res->getBody(), true);

    if (is_array($plugins)) {
    
        $installed = Plugin::enum();
		
		$l = (string)$application->getLocale();
    
        foreach ($plugins as $id => $p) {
			
            $plugins[$id]['description'] = isset($plugins[$id]['description_'.$l])?$plugins[$id]['description_'.$l]:$plugins[$id]['description'];
            $plugins[$id]['title'] = isset($plugins[$id]['title_'.$l])?$plugins[$id]['title_'.$l]:$plugins[$id]['title'];	
            
            if (isset($installed[$p['id']])) {
                $plugins[$id]['installed'] = true;
                if (version_compare($p['version'], $installed[$p['id']]['version']) > 0)
                    $plugins[$id]['upgrade'] = true;
            } else {
                $plugins[$id]['installed'] = false;
            }
                
            $plugins[$id]['compatible'] = true;
            // требуется более свежая CMS
            if ($p['cms_version_min'] && version_compare($p['cms_version_min'], VERSION) > 0 ) {
				$plugins[$id]['compatible'] = false;
				$plugins[$id]['compatible_message'] = sprintf($translator->_('Не подходящая версия Cetera CMS. Требуется %s или выше'), $p['cms_version_min']);
			}
            // CMS слишком новая
            if ($p['cms_version_max'] && version_compare($p['cms_version_max'], VERSION) <= 0 ) {
				$plugins[$id]['compatible'] = false; 
				$plugins[$id]['compatible_message'] = sprintf($translator->_('Не подходящая версия Cetera CMS. Требуется не выше %s'), $p['cms_version_max']);				
			}
        }
        
    } else {
        throw new Exception('NO DATA');
    }

} catch (\Exception $e) {

    $plugins = array();
    
}

 usort ( $plugins , function($a, $b){
	 
	 if ($a['installed'] == $b['installed']) {
		 return strcasecmp ( $a['id'], $b['id'] );
	 }
	 else {
		 return ($a['installed'] < $b['installed'])?1:-1;
	 }
	 
 } );

print json_encode($plugins);