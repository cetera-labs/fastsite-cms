<?php
namespace Cetera;
include('common_bo.php');

if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);
$t = $application->getTranslator();

$data = json_decode(file_get_contents("php://input"), true);

$theme = Theme::find($data['theme']);
if (!$theme)  throw new Exception\CMS($t->_('Тема не найдена'));

$theme->saveContentInfo($data);

echo json_encode([
	'success' => true
]);