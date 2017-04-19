<?php
include('../include/common_bo.php');

$data = array();

$r = $application->getDbConnection()->executeQuery( 'SELECT * FROM widgets WHERE widgetName=?', array("Container") );
  
while($f = $r->fetch())
{

    $f['params'] = unserialize($f['params']);
    $f['widgetDisabled'] = (bool)$f['widgetDisabled'];
    $data[] = $f;
    
}

echo json_encode(array(
    'success' => true,
    'data'    => $data
));