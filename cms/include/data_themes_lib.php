<?php
namespace Cetera;
include('common_bo.php');

if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

header('Content-Type: application/json');

try {

	$client = new \GuzzleHttp\Client();
    $res = $client->get(THEMES_INFO, ['verify'=>false]);  		
    $themes = json_decode( $res->getBody(), true);

    if (is_array($themes)) {
    
        $installed = Theme::enum();
    
        foreach ($themes as $id => $p) {
            
            if (isset($installed[$p['id']])) {
                $themes[$id]['installed'] = true;
                if (version_compare($p['version'], $installed[$p['id']]['version']) > 0)
                    $themes[$id]['upgrade'] = true;
            } else {
                $themes[$id]['installed'] = false;
            }
                
            $themes[$id]['compatible'] = true;
            // требуется более свежая CMS
            if ($p['cms_version_min'] && version_compare($p['cms_version_min'], VERSION) > 0 ) {
				$themes[$id]['compatible'] = false;
				$themes[$id]['compatible_message'] = sprintf($translator->_('Не подходящая версия Fastsite CMS. Требуется %s или выше'), $p['cms_version_min']);
			}
            // CMS слишком новая
            if ($p['cms_version_max'] && version_compare($p['cms_version_max'], VERSION) <= 0 ) {
				$themes[$id]['compatible'] = false;    
				$themes[$id]['compatible_message'] = sprintf($translator->_('Не подходящая версия Fastsite CMS. Требуется не выше %s'), $p['cms_version_max']);				
			}
        }
        
    } else {
        throw new Exception('NO DATA');
    }

} catch (\Exception $e) {

    $themes = array();
    
}

print json_encode($themes);