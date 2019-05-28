<?php
namespace Cetera;
include('common.php');

echo json_encode(array(
    'rows' => Application::getInstance()->getLocaleList()
));