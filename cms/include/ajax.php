<?php
namespace Cetera;

include_once('common_bo.php');

$res = array(
    'success' => false,
);

if ($_REQUEST['action'] == 'fo_edit_mode')
{
  
    if ($_REQUEST['mode'])
	{
		$application->getSession()->foEditMode = true;
	}
	else
	{
		unset($application->getSession()->foEditMode);
	}
   
}

$res['success'] = true;

echo json_encode($res); 