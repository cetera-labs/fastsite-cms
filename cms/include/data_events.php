<?php
include('common_bo.php');

$rows = [];

foreach($application->getLoggableEvents() as $eid) {
	$rows[] = $application->getBo()->getEventById($eid);
}

echo json_encode(array(
    'success' => true,
    'rows'    => $rows,
));