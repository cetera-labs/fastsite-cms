<?php
require('include/common.php');

if (stripos($_SERVER['HTTP_HOST'], 'xn--')!==false) {
   $idn = new \True\Punycode();
   $_SERVER['HTTP_HOST'] = $idn->decode($_SERVER['HTTP_HOST']);
}

$application = Cetera\Application::getInstance();

set_exception_handler('fo_exception_handler');

list($url) = explode('?',$_SERVER['REQUEST_URI']);

$_ext = '/'.LIBRARY_PATH.'/extjs';
if (substr($url,0,strlen($_ext)) == $_ext) die();

$_ext = substr($url,-3);

$_init = Cetera\Util::utime();

$application->route(\Cetera\ImageTransform::PREFIX,  array('\Cetera\ImageTransform', 'transformFromURI') );

$application->route('/'.PREVIEW_PREFIX,  function() {
	
	\Cetera\Application::getInstance()->setPreviewMode(true);
	$_SERVER['REQUEST_URI'] = str_replace('/'.PREVIEW_PREFIX,'',$_SERVER['REQUEST_URI']);
	
} );

$application->route('/robots.txt',  function() {
	
	header('HTTP/1.0 200 OK');
	header('Content-type: text/plain');
	echo \Cetera\Application::getInstance()->getServer()->getRobots();
	return true;
	
} );

$application->setFrontOffice();
$application->connectDb();
$application->initSession();
$application->initPlugins();
$application->ping();

if ($application->getVar('fo_close')) {
	$proceed = 0;
    $message = $application->getVar('fo_close_msg');
	if (!$message) $message = 'Site closed';
	
  	if ($application->getVar('fo_allow_users')||$application->getVar('fo_allow_pw')) {

		if ($application->getVar('fo_allow_pw') && ($_SERVER['PHP_AUTH_USER'] == $application->getVar('fo_allow_user'))&&($_SERVER['PHP_AUTH_PW'] == $application->getVar('fo_allow_pw'))) {
           $proceed = 1;
        } elseif ($application->getVar('fo_allow_users')) {
        
            $result = \Zend_Auth::getInstance()->authenticate(new UserAuthAdapter(array(
                'login' => $_SERVER['PHP_AUTH_USER'],
                'pass'  => $_SERVER['PHP_AUTH_PW']
            ))); 
            
            if ($result->getCode() == \Zend_Auth_Result::SUCCESS) {
                if (!$application->getVar('fo_allow_users_bo')) {
                    $proceed = 1;
                } else {
                    $i = $result->getIdentity();
                    $user = User::getById($i['user_id']);
                    if ($user->allowBackOffice()) $proceed = 1;
                }
            }
		}
  	}
	
	if (!$proceed) {
   		header('WWW-Authenticate: Basic realm="'.APP_NAME.'"');
        throw new \Cetera\Exception\HTTP(401, $message);
    }
}
$_time_init = Cetera\Util::utime() - $_init;

$_start = Cetera\Util::utime();

$pieces = explode('/',$application->getUnparsedUrl());

if (!$application->getServer()) throw new \Cetera\Exception\HTTP(404, 'Server is not found');

if ($application->getCatalog()->isLink()) {
	header('Location: '.$application->getCatalog()->prototype->getFullUrl(), true, 301);
	die();
}

$t = $application->getServer()->getTheme();
if ($t) $t->addTranslation($application->getTranslator());

// ------------------------------------------------------------------
// -			           Executing template                       -
// ------------------------------------------------------------------
$_time_uri = Cetera\Util::utime()-$_start;

$td = $application->getTemplateDir();

$template = false;
if (sizeof($pieces)) $template = $pieces[0];
if ($template)
{
    $template = $template.'.php';
    if (!file_exists($td.'/'.$template)) 
        $template = false;
    
} 
if (!$template) $template = $application->getCatalog()->getDefaultTemplate();

$application->debug(DEBUG_COMMON, 'Main template: '.$template);

if (file_exists($td.'/classes') && is_dir($td.'/classes')) {
    $loader = new \Composer\Autoload\ClassLoader();
    $loader->add('', $td.'/classes');
    $loader->register();
}

$p = substr_count(PHP_OS, 'WIN')?';':':';
ini_set('include_path', $td.$p.ini_get('include_path'));

$result = NULL;  
$_start = Cetera\Util::utime();

if (file_exists($td.'/'.BOOTSTRAP_SCRIPT))
    include_once($td.'/'.BOOTSTRAP_SCRIPT);

if (parse_url($template,  PHP_URL_HOST)) {
	
	header("HTTP/1.1 301 Moved Permanently");
	header('Location: '.$template);
	
}
else {
	$path_parts = pathinfo($template);

	if ($path_parts['extension'] == 'php')
	{
		$template_file = $application->getTemplatePath($template);
		if (!file_exists($template_file))
			throw new Cetera\Exception\CMS('Шаблон не найден '.$template_file);
		ob_start();
		include($template_file);
		$result = ob_get_contents();
		ob_end_clean();
		
	}
	elseif ($path_parts['extension'] == 'widget')
	{

		$result = $application->getWidget($path_parts['filename'])->getHtml();
		
	}
	elseif ($path_parts['extension'] == 'twig')
	{
		$result = $application->getTwig()->render($template);
	}
}

$_time_template = Cetera\Util::utime()-$_start;

$_time_total = Cetera\Util::utime() - $_init;

$application->debug(DEBUG_COMMON, 'SQL queries: '.$GLOBALS['_queries']);
$application->debug(DEBUG_COMMON, '== BENCHMARK ==');
$application->debug(DEBUG_COMMON, 'Init: '.sprintf("%.3f", $_time_init));
$application->debug(DEBUG_COMMON, 'Parse URL: '.sprintf("%.3f", $_time_uri));
$application->debug(DEBUG_COMMON, 'Templates: '.sprintf("%.3f", $_time_template));
$application->debug(DEBUG_COMMON, 'Total: '.sprintf("%.3f", $_time_total));
$application->debug(DEBUG_COMMON, '== CACHE ==');
$be = Cetera\Cache\Backend\Backend::getInstance();
$application->debug(DEBUG_COMMON, 'Backend: '.get_class($be->getBackend()));
$application->debug(DEBUG_COMMON, 'Total hits: '.($be->successCalls+$be->failCalls));
$application->debug(DEBUG_COMMON, 'Success hits: '.$be->successCalls.' ('.($be->successCalls*100/($be->successCalls+$be->failCalls)).'%)');

$application->applyOutputHandler($result);
echo $result;

function fo_exception_handler($exception) {

    if ($exception instanceof \Cetera\Exception\CMS) {
        $ext_message = $exception->getExtMessage();
    } else {
        $ext_message = 'In file <b>'.$exception->getFile().'</b> on line: '.$exception->getLine()."<br /><br /><b>Stack trace:</b><br />".nl2br($exception->getTraceAsString());
    }
   
    // Ошибка в Back office
    if (ob_get_level()) ob_clean();
    
    header('Content-type: text/html; charset=UTF-8');
    
    if ($exception instanceof \Cetera\Exception\HTTP) {
        header("HTTP/1.0 ".$exception->getStatus());
    } else {
        header("HTTP/1.0 500");
    }
    
    ?>
    <html>
        <head>
            <link rel="stylesheet" type="text/css" href="/<?=CMS_DIR?>/css/error.css">
        </head>
        <body>
            <table width="100%" height="100%"><tr><td align="center">
                <div class="panel">
                    <div class="error"><?=$exception->getMessage()?></div>
                    <?if ($ext_message) {?><div class="extend"><?=$ext_message?></div><?}?>
                </div>
            </td></tr></table>
        </body>
    </html>
    <?

}
?>
