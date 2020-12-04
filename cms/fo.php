<?php
use Zend\Authentication\Result;

require_once('include/common.php');

if (stripos($_SERVER['HTTP_HOST'], 'xn--')!==false) {
   $idn = new \True\Punycode();
   $_SERVER['HTTP_HOST'] = $idn->decode($_SERVER['HTTP_HOST']);
}

$application = Cetera\Application::getInstance();

list($url) = explode('?',$_SERVER['REQUEST_URI']);

$_ext = '/'.LIBRARY_PATH.'/extjs';
if (substr($url,0,strlen($_ext)) == $_ext) die();

$_ext = substr($url,-3);

$_init = Cetera\Util::utime();

$application->getRouter()->addRoute(\Cetera\ImageTransform::PREFIX,
    \Zend\Router\Http\Regex::factory([
        'regex' => '/'.\Cetera\ImageTransform::PREFIX.'/(?<params>[a-zA-Z0-9_-]+)/(?<path>.+)',
        'defaults' => [
            'controller' => '\Cetera\ImageTransform',
            'action'     => 'transformFromURI',
        ],
        'spec' => '/'.\Cetera\ImageTransform::PREFIX.'/%params%/%path%',
    ]), 10
);

$application->getRouter()->addRoute('api_entities',
    \Zend\Router\Http\Segment::factory([
        'route' => '/api/entity[/:entity][/:action][/:id]',
        'constraints' => [
            'entity' => '[a-zA-Z][a-zA-Z0-9_-]+',
            'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
            'id'         => '[a-zA-Z][a-zA-Z0-9_-]+',
        ],
        'defaults' => [
            '__NAMESPACE__' => 'Cetera\Api',
            'controller'    => 'Cetera\Api\EntityController',
            'action'        => 'default',
        ],
    ]), 10
);

$application->getRouter()->addRoute('api_main',
    \Zend\Router\Http\Segment::factory([
        'route' => '/api[/:controller][/:action][/:id]',
        'constraints' => [
            'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
            'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
            'id'         => '[a-zA-Z][a-zA-Z0-9_-]+',
        ],
        'defaults' => [
            '__NAMESPACE__' => 'Cetera\Api',
            'controller'    => 'Cetera\Api\IndexController',
        ],
    ]), 0
);



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
        
            $result = $application->getAuth()->authenticate(new UserAuthAdapter(array(
                'login' => $_SERVER['PHP_AUTH_USER'],
                'pass'  => $_SERVER['PHP_AUTH_PW']
            ))); 
            
            if ($result->getCode() == Result::SUCCESS) {
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

/*
if ($application->getCatalog()->isLink()) {
	header('Location: '.$application->getCatalog()->prototype->getFullUrl(), true, 301);
	die();
}
*/

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
if (!$template) {
    $template = $application->getCatalog()->getDefaultTemplate();
}

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

$request  = $application->getRequest();
$response = $application->getResponse();
$router   = $application->getRouter();

$match = $router->match($application->getRequest());
if ( $match ) {
    
    $class = $match->getParam('controller');
    $method = $match->getParam('action');

    if (!class_exists($class)) {
        $class = $match->getParam('__NAMESPACE__').'\\'.ucfirst($class).'Controller';
    }
       
    $controller = new $class();
    
    if ($controller instanceof Zend\Mvc\Controller\AbstractController) {
                                
        $event = new Zend\Mvc\MvcEvent();
        $event->setRouter($router);
        $event->setRouteMatch($match);     
        
        $controller->setEvent($event);        
        $res = $controller->dispatch($request,$response);
        
        $view = new \Zend\View\View();
        $view->setRequest($request);
        $view->setResponse($response);
        
        $jsonRenderer = new Zend\View\Renderer\JsonRenderer();
        $jsonStrategy = new Zend\View\Strategy\JsonStrategy($jsonRenderer);        
        $jsonStrategy->attach($view->getEventManager(), 100);
        
        $view->render($res);
                
    }
    else {
        ob_start();
        $method = $match->getParam('action');
        $controller->$method($match->getParams());
        $response->setContent(ob_get_contents());
        ob_end_clean();         
    }
    
}
elseif (parse_url($template,  PHP_URL_HOST)) {
	
	header("HTTP/1.1 301 Moved Permanently");
	header('Location: '.$template);
    die();
  
}
elseif (is_callable($template)) {
		ob_start();
        list($class, $method) = explode('::', $template);
        if ($class) {
            $controller = new $class();
            $controller->$method();
        }
        else {
            $template();
        }
		$result = ob_get_contents();
		ob_end_clean();    
}
else {
            
    $path_parts = pathinfo($template);
    
    if ($template == '[visual_constructor]') {
        $application->getTwig()->addGlobal('visual_constructor', $application->getCatalog()->visual_constructor);
        $application->getTwig()->addGlobal('visual_constructor_section', $application->getCatalog()->visual_constructor_origin);
        $response->setContent($application->getTwig()->render('page_visual_constructor.twig'));
    }
    elseif ($path_parts['extension'] == 'php') {
        $template_file = $application->getTemplatePath($template);
        if (!file_exists($template_file))
            throw new Cetera\Exception\CMS('Шаблон не найден '.$template_file);
        ob_start();
        include($template_file);
        $response->setContent(ob_get_contents());
        ob_end_clean();
        
    }
    elseif ($path_parts['extension'] == 'widget') {
        $response->setContent($application->getWidget($path_parts['filename'])->getHtml());
    }
    elseif ($path_parts['extension'] == 'twig') {
        $response->setContent($application->getTwig()->render($template));
    }
}

$_time_template = Cetera\Util::utime()-$_start;

$_time_total = Cetera\Util::utime() - $_init;

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

$application->applyOutputHandler();

foreach ($response->getHeaders() as $header) {
    if ($header instanceof Zend\Http\Header\MultipleHeaderInterface) {
        header($header->toString(), false);
        continue;
    }
    header($header->toString());
}
header($response->renderStatusLine());
echo $response->getContent();