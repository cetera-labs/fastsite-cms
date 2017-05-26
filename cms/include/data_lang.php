<?php
namespace Cetera;
include('common.php');

$data = array();

$locales = \Zend_Locale::getLocaleList();
$l = $application->getLocale();
$translator = $application->getTranslator(); 

foreach($locales as $locale => $exists) if ($exists) {
    if ($translator->isAvailable($locale)) {
        
		$v = $l->getTranslation($locale, 'language', $locale);
		
        $data[] = array(
            'abbr' => $locale, 
            'state' => mb_strtoupper(mb_substr($v,0,1)).mb_substr($v,1)
        );
    }
}

echo json_encode(array(
    'rows' => $data
));