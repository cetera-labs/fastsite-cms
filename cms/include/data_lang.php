<?php
namespace Cetera;
include('common.php');

$data = array();

$locales = \Zend_Locale::getLocaleList();
$l = $application->getLocale();
$translator = $application->getTranslator(); 

foreach($locales as $locale => $exists) if ($exists) {
    if ($translator->isAvailable($locale)) {
        
        $data[] = array(
            'abbr' => $locale, 
            'state' => $l->getTranslation($locale, 'language', $locale)
        );
    }
}

echo json_encode(array(
    'rows' => $data
));