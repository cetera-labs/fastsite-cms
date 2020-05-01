<?php
require_once('include/common.php');
$application->connectDb();
$application->initSession();

$user = $application->getUser();

if ($user) $user->logout();
if (isset($_REQUEST['redirect']))
{
    header('Location: '.$_REQUEST['redirect']);
}
else
{
	header('Location: '.$_SERVER['HTTP_REFERER']);
}