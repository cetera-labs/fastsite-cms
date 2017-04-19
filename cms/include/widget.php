<?php
namespace Cetera;

include('common.php');

$application = Application::getInstance();
$application->connectDb();
$application->initSession();
$application->initPlugins();
$application->setFrontOffice();

if (isset($_REQUEST['locale']))
	$application->setLocale($_REQUEST['locale']);

if (isset($_REQUEST['ajaxCall'])) $_REQUEST['params']['ajaxCall'] = 1;
$w = $application->getWidget($_REQUEST['widget'],$_REQUEST['params'],$_REQUEST['unique']);
$w->display();