<?php
namespace Cetera;

include_once('common.php');

$application = Application::getInstance();
$application->connectDb();
$application->initSession();
$application->initPlugins();
$application->setFrontOffice();

$t = $application->getServer()->getTheme();
if ($t) $t->addTranslation($application->getTranslator());

$td = $application->getTemplateDir();
if (file_exists($td.'/classes') && is_dir($td.'/classes')) {
    $loader = new \Composer\Autoload\ClassLoader();
    $loader->add('', $td.'/classes');
    $loader->register();
}
if (file_exists($td.'/'.BOOTSTRAP_SCRIPT)) {
    include_once($td.'/'.BOOTSTRAP_SCRIPT);
}

if (isset($_REQUEST['locale'])) {
	$application->setLocale($_REQUEST['locale']);
}

if ($_REQUEST['params'] && !is_array($_REQUEST['params'])) {
    $_REQUEST['params'] = json_decode($_REQUEST['params'], true);
}

if (isset($_REQUEST['ajaxCall'])) $_REQUEST['params']['ajaxCall'] = 1;
$w = $application->getWidget($_REQUEST['widget'],$_REQUEST['params'],$_REQUEST['unique']);
$w->display();