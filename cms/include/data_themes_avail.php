<?php
namespace Cetera;
include_once('common.php');
header('Content-Type: application/json');

$data = [];

try {

	$client = new \GuzzleHttp\Client();
    $res = $client->get(THEMES_INFO, ['verify'=>false]);  		
    $themes = json_decode( $res->getBody(), true);
    if (is_array($themes)) {
        
        foreach ($themes as $id => $p) {
                           
            // требуется более свежая CMS
            if ($p['cms_version_min'] && version_compare($p['cms_version_min'], VERSION) > 0 ) continue;
            // CMS слишком новая
            if ($p['cms_version_max'] && version_compare($p['cms_version_max'], VERSION) <= 0 ) continue;
			
			if (isset($_REQUEST['theme']) && $_REQUEST['theme'] != $id) continue;
			
			foreach($p['content'] as $c) {
				
				$c['full_id'] = $c['theme'].'|'.$c['id'];
 				$data[] = $c;
				
			}
			
        }
        
    } else {
        throw new Exception('NO DATA');
    }

} catch (\Exception $e) { }

print json_encode($data);