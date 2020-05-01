<?php
namespace Cetera;
include_once('common.php');

echo json_encode(array(
    'rows' => Application::getInstance()->getLocaleList()
));