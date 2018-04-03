<?php
include('common_bo.php');

$rows = $application->getBo()->getRegisteredEvents();

echo json_encode(array(
    'success' => true,
    'rows'    => $rows,
));