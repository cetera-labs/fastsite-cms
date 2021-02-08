<?php
namespace Cetera;

include_once('common.php');

$application = Application::getInstance();
$application->connectDb();
$application->initSession();
$application->initPlugins();
$application->setFrontOffice();

if (isset($_REQUEST['url'])) {
    $application->setRequestUri($_REQUEST['url']);
}

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

if (isset($_REQUEST['params']) && !is_array($_REQUEST['params'])) {
    $_REQUEST['params'] = json_decode($_REQUEST['params'], true);
}

$params = [];
if (is_array($_REQUEST['params'])) {
    foreach($_REQUEST['params'] as $key => $value) {
        if ((string)$value != (string)(int)$value) {
            $params[$key] = Util::dsCrypt($value, true);
        }
        else {
            $params[$key] = $value;
        }
    }
}

if (isset($_REQUEST['ajaxCall'])) $params['ajaxCall'] = 1;
$w = $application->getWidget($_REQUEST['widget'],$params,$_REQUEST['unique']);
$w->display();