<?php
include('common_bo.php');

if (sizeof($_POST)) {
	
    $rows = json_decode($_POST['rows'], true);
	if (!isset($rows[0])) {
		$rows = [$rows];
	}	
	
	foreach ($rows as $r) {
		if ($r['log']) {
			$application->addLoggableEvent($r['id']);
		}
		else {
			$application->removeLoggableEvent($r['id']);
		}
	}
	
}
else {

	$loggable = $application->getLoggableEvents();
	$rows = $application->getBo()->getRegisteredEvents();
	foreach ($rows as $id => $r) {
		$rows[$id]['log'] = in_array($r['id'],$loggable);
	}

	echo json_encode(array(
		'success' => true,
		'rows'    => $rows,
	));
	
}