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


$td = $application->getTemplateDir();
if (file_exists($td.'/classes') && is_dir($td.'/classes')) {
    $loader = new \Composer\Autoload\ClassLoader();
    $loader->add('', $td.'/classes');
    $loader->register();
}
if (file_exists($td.'/'.BOOTSTRAP_SCRIPT)) {
    include_once($td.'/'.BOOTSTRAP_SCRIPT);
}

if (isset($_REQUEST['ajaxCall'])) $_REQUEST['params']['ajaxCall'] = 1;
$w = $application->getWidget($_REQUEST['widget'],$_REQUEST['params'],$_REQUEST['unique']);
$w->display();