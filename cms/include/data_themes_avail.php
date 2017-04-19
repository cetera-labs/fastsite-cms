<?php
namespace Cetera;
include('common.php');
header('Content-Type: application/json');

try {

	$client = new \GuzzleHttp\Client();
    $res = $client->get(THEMES_INFO);  		
    $themes = json_decode( $res->getBody(), true);

    if (is_array($themes)) {
        
        foreach ($themes as $id => $p) {
                           
            $themes[$id]['compatible'] = true;
            // требуется более свежая CMS
            if ($p['cms_version_min'] && version_compare($p['cms_version_min'], VERSION) > 0 ) $themes[$id]['compatible'] = false;
            // CMS слишком новая
            if ($p['cms_version_max'] && version_compare($p['cms_version_max'], VERSION) <= 0 ) $themes[$id]['compatible'] = false;    
        }
        
    } else {
        throw new Exception('NO DATA');
    }

} catch (\Exception $e) {

    $themes = array();
    
}

print json_encode($themes);