<?php
include('common_bo.php');

echo json_encode(array(
    'success' => true,
    'rows'    => \Cetera\Mail\Event::enum()
));