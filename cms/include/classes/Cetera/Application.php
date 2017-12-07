<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera; 
 
/**
 * Объект Application (приложение) является главным в иерархии объектов CeteraCMS и 
 * представляет само приложение CeteraCMS. Cвойства и методы предназначены для 
 * доступа к параметрам сайта.
 *
 * @package CeteraCMS
 **/ 
class Application {
	
	use DbConnection;

    /**
     * Экземпляр переводчика
     * @internal     
     * @var \Zend_Translate
     */
    protected $_translator = null;
  
    /**
     * Локаль приложения
     * @internal     
     * @var \Zend_Locale
     */
    protected $_locale;
    
    /**
     * Сессия приложения
     * @internal     
     * @var \Zend_Session_Namespace
     */
    protected $_session;
       
    /**
     * Конфигурация приложения
     * @internal     
     * @var array
     */
    protected $_config = null;
    
    /**
     * Авторизованный пользователь
     * @internal     
     * @var User
     */
    protected $_user = null;
    
    /**
     * Установлено соединение с БД
     * @internal     
     * @var bool
     */
    private $_connected = false;
    
    /**
     * Модули
     * @internal     
     * @var array
     */
    private $_plugins_loaded = false;
    
	/**
	 * Текущий сервер
	 * @internal    	   
	 * @var Server
	 */ 
	private $_server = false;
	
	/**
	 * Текущий раздел
	 * @internal    	   
	 * @var Catalog
	 */ 
	private $_catalog = false;
	
	/**
	 * Путь до текущего раздела 
	 * @internal   	   
	 * @var array
	 */ 
	private $_urlPath = array();
	
	/**
	 * Нераспарсенная часть url 
	 * @internal   	   
	 * @var string
	 */ 
	private $_unparsedUrl = null;
	
	/**
	 * Работает Front office? 
	 * @internal   	   
	 * @var bool
	 */ 
	private $_fo = false;
  
	protected $twig = null;
	protected $params = array();  
  
	/** 
	 * @internal  
	 * @var BackOffice
	 */ 
	private $_bo = null;  
	
	/**
	 * Режим preview	
	 * @internal      
	 * @var bool
	 */ 
	private $_previewMode = false;
		
  /*
   * @internal
   */   
	private $_groups = array();
  
  /*
   * @internal
   */  
	private $_user_groups = array();
	
	/**
	 * Обработчики результата FO	   
	 * @var array
	 */ 
	private $_result_handler = array();
  
  /*
   * @internal
   */  
	private $_cronJob = array();  
	
	/**
	 * Timestamp последнего посещения FO	   
	 * @var int
	 */ 
	private $_last_visit = 0;
	
	/**
	 * Уникальный идентификаор клиента FO	   
	 * @var string
	 */ 
	private $_uid = '';
 
    private $debugMode = false;
    
  /*
   * @internal
   */   
    private $_debug_writer = false;
  /*
   * @internal
   */  
    private $_debugger_request = false;
  /*
   * @internal
   */  
    private $_debugger_response = false;
  /*
   * @internal
   */  
    private $_debugger_channel = false;
  /*
   * @internal
   */  
    private $_debugger = false;   
    
  /*
   * @internal
   */  
    private $_widgetAliases = array(
        'Simple' => 'Html',
    );
    
  /*
   * @internal
   */ 
    private $_widgets = null;
	
	private $_widgetPaths = array();
    
  /*
   * @internal
   */  
    private $_widgetsTemplatesPath = array();
    
  /*
   * @internal
   */  
    private $_widgetsGetHtmlHandler = false;
    
  /*
   * @internal
   */  
    private $_dbConnection = null;  

  /*
   * @internal
   */ 	
	private $routes = array(
		'general' => array(),
		'get' => array(),
		'post' => array(),
		'put' => array(),
		'delete' => array(),
	);
	
	private $cronMode = false;
	private $cronLog = false;
	private $exitCode = 0;
    
    /**
     * Singleton instance
     * @var Application
     */
    private static $_instance = null;

    /**
     * Singleton instance
     *
     * @return Application
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } 
	
    /**
     * Short for getInstance()
     *
     * @return Application
     */	
	public static function get()
	{
		return self::getInstance();
	}
        
    /**
     * Enforce singleton; disallow cloning 
     * @internal
     * @return void
     */
    private function __clone() {}
   
    /**
     * Constructor
     *
     * Instantiate using {@link getInstance()}; application is a singleton object.
     * @internal
     * @return void
     */
    protected function __construct()
    {
		register_shutdown_function('\Cetera\Application::shutdown');
		set_exception_handler('\Cetera\Application::exception_handler');
		set_error_handler('\Cetera\Application::error_handler');		
		
        $this->_locale = new \Zend_Locale('ru');
  
		$check = '/cms/fo.php';  
		
        if (substr( $_SERVER['SCRIPT_NAME'], 0 - strlen( $check ) ) == $check)
		{
			$this->setFrontOffice();
		}
		
        $this->initDebug();
        
        if (php_sapi_name() != 'cli') {
        
            if (!isset($_COOKIE['ccms'])) {
                $this->_uid = md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].rand());
            } else {
                $a = explode('.', $_COOKIE['ccms']);
                $this->_uid = $a[0];
                $this->_last_visit = $a[1];
            }
            setcookie('ccms',$this->_uid.'.'.time(),time()+REMEMBER_ME_SECONDS,'/');
            
        }
        
        $this->_widgetsTemplatesPath[] = CMSROOT.'widgets/';
		
		Mail\Event::attach();
    }
    
    /**
     * инициализация режима BackOffice
     * @internal     
     */	
    public function initBo()
    {
        $this->_bo = new BackOffice($this);
    }
    
    /**
     * @see Application::getBo()
     */		
    public function getBackOffice()
    {
        return $this->getBo();
    }
    
    /**
     * Возвращает объект BackOffice
     * 
     * @return BackOffice          
     */		
    public function getBo()
    {
        return $this->_bo;
    } 

	public function getFrontOffice()
	{
	   return $this->getFo();
    }

	public function getFo()
	{
	   return $this->_fo;
    }	
    
    /**
     * Установливает текущий раздел.
     *      
     * @param  int|Catalog $catalog раздел
     * @return void
     */
    public function setCatalog( $catalog )
    {
        if (is_a($catalog,'Cetera\\Catalog') || is_a($catalog,'Cetera\\Server')) {
            $this->_catalog = $catalog;
            return;
        }
        if (is_int($catalog)) {
            $this->_catalog = Catalog::getById($catalog);
            return;        
        }
    }
    
    /**
     * Установливает новую локаль приложения и запоминает её в сессии.
     *      
     * @param  string|\Zend_Locale $locale новая локаль
     * @return void
     */
    public function setLocale($locale = null, $remember = false)
    {
        $this->_locale->setLocale($locale);
        $t = $this->getTranslator();
        if ($t->isAvailable($this->_locale))
            $t->setLocale($this->_locale);
        if ($remember) $this->_session->locale = $this->_locale->toString();
    }
    
    /**
     * Локаль приложения
     *      
     * @return \Zend_Locale
     */
    public function getLocale()
    {
        return $this->_locale;   
    }
    
    /**
     * Сессия приложения
     *      
     * @return \Zend_Session_Namespace
     */
    public function getSession()
    {
        return $this->_session;   
    }
    
    /**
     * Получить переводчик
     *      
     * @internal     
     * @return \Zend_Translate
     */
    public function getTranslator()
    {
        if (!$this->_translator) {

			$this->_translator = new \Zend_Translate(   
				'gettext', 
				CMSROOT . 'lang',  
				$this->_locale,  
				array('scan'=>\Zend_Translate::LOCALE_FILENAME)
			); 

        }
        return $this->_translator;
    }
	
	public function decodeLocaleString($string) {
		if (preg_match_all('/([^\[]*)(\[(\w\w)=([^\]]*)\])+/U', $string, $m)) {
			// default
			$value = $m[1][0];

			// search locale
			foreach ($m[3] as $key => $locale) {
				if ($this->getLocale() == $locale) {
					$value = $m[4][$key];
					break;
				}
			}			
			return $value;
		}
		else {
			return $string;
		}
	}	
	
	protected function loadVars()
	{
        if (!$this->_config) {
            if (file_exists(PREFS_FILE)) {
                $this->_config = parse_ini_file(PREFS_FILE, true);
            } else {
                $this->_config = array();
            }
        }		
	}
    
    /**
     * Получить переменную конфигурации
     *      
     * @param string $name имя переменной     
     * @return mixed
     */
    public function getVar($name)
    {
		$this->loadVars();
    
        if (isset($this->_config[$name])) {
            return $this->_config[$name];
        } else {
            return false;
        }
    }
	
    /**
     * Сохранить переменную конфигурации
     *      
     * @param string $name имя переменной     
     * @return mixed
     */
    public function setVar($name, $value)
    {
        $this->loadVars();
		if ($value === null) {
			unset($this->_config[$name]);
		}
		else {
			$this->_config[$name] = $value;
		}
		$f = fopen(PREFS_FILE,'w');
		foreach($this->_config as $name => $value) {
			if (is_array($value)) continue;
			fwrite($f,$name."=".$value."\n");
		}
		foreach($this->_config as $name => $value) {
			if (!is_array($value)) continue;
			fwrite($f,"\n[".$name."]\n");
			foreach ($value as $key => $val) {
				fwrite($f,$key."=".$val."\n");
			}
		}		
		fclose($f);  			
    }	
    
    /**
     * Получить авторизованного пользователя
     *      
     * @return User
     */
    public function getUser()
    {
        if ($this->_user) return $this->_user;
        
	    try {
    	    $za = $this->getAuth();
            $id = $za->getIdentity();
            if (!$id) return FALSE;
        } catch (\Exception $e) {
            return FALSE;
        }
                
        if (isset($id['user_id'])) {
        
           if (!$this->_connected) return false;          
           $this->_user = User::getAuthorized($id);
           
        }
        
        return $this->_user;
    }
	
    public function setUser($user)
    {
        $this->_user = $user;
    }	
    
    /**
     * Установить соединение с БД
     *      
     * @return void
     */
    public function connectDb()
    {     
        if ($this->_connected) return;
		
		if (function_exists('mysql_connect')) {
			try {
				mysql_connect($this->getVar('dbhost'),$this->getVar('dbuser'),$this->getVar('dbpass'));
				mysql_select_db($this->getVar('dbname'));
				mysql_query('SET NAMES utf8');
			} catch (\Exception $e) {
				throw new \Exception($e->getMessage());
			}
		}		
        
        $connectionParams = array(
            'dbname'       => $this->getVar('dbname'),
            'user'         => $this->getVar('dbuser'),
            'password'     => $this->getVar('dbpass'),
            'host'         => $this->getVar('dbhost'),
            'driver'       => $this->getVar('dbdriver')?$this->getVar('dbdriver'):'pdo_mysql',
			'wrapperClass' => 'Cetera\Database\Connection',
        );
 
        $this->_dbConnection = \Doctrine\DBAL\DriverManager::getConnection( 
            $connectionParams, 
            new \Doctrine\DBAL\Configuration()
        );           
            
        $this->_dbConnection->executeQuery('SET CHARACTER SET utf8'); 
        $this->_dbConnection->executeQuery('SET names utf8');            
            
        $this->_connected = true; 
            
    }
    
    public function setDbConnection( $conn )
    {
        $this->_dbConnection = $conn;
        $this->_connected = true; 
    }
        
    /**
     * Возвращает соединение в БД
     *  
	 * @api	 
     * @return Doctrine\DBAL\Connection
     */    
    public function getConn()
    {
        if (!$this->_dbConnection) throw new \Exception('no DbConnection');
        return $this->_dbConnection;
    }     
        
    /**
     * Установлено ли соединение с БД
     *      
     * @return void
     */
    public function isConnected()
    {
        return $this->_connected;
    }
           
    /**
     * Инициализация дебаггера
     *      
     * @return void    
     */
    private function initDebug()
    {
        if (!$this->getVar('debug_level') && !isset($_REQUEST['debug_level'])) return;

		$this->debugMode = $this->getVar('debug_level') | $_REQUEST['debug_level'];
		
        $this->_debug_writer = new \Zend_Log_Writer_Firebug();
        $this->_debugger_request  = new \Zend_Controller_Request_Http();
        $this->_debugger_response = new \Zend_Controller_Response_Http();
        $this->_debugger_channel  = \Zend_Wildfire_Channel_HttpHeaders::getInstance();
        $this->_debugger_channel->setRequest($this->_debugger_request);
        $this->_debugger_channel->setResponse($this->_debugger_response);
        $this->_debugger = new \Zend_Log($this->_debug_writer);
        $this->_debugger->addPriority('SQL', DEBUG_SQL);
        $this->_debugger->addPriority('CACHE', DEBUG_CACHE);
        $this->_debugger->addPriority('BANNER', DEBUG_BANNER);
        
        ob_start();
		
		ini_set('display_errors', 1);
    }
	
    public function isDebugMode()
    {
		return $this->debugMode;
	}
    
    /**
     * Инициализация сессии
     *      
     * @return void    
     */
    public function initSession()
    {
        \Zend_Session::setSaveHandler(new SessionSaveHandler());
                             
        $this->_session = new \Zend_Session_Namespace(SESSION_NAMESPACE);
        
        if (isset($this->_session->locale) && $this->_session->locale) {
            $this->_locale->setLocale($this->_session->locale);
        }
    }
    
    /**
     * Завершение работы приложения
     *      
     * @return void    
     */
    public static function shutdown()
    {    
		
        if (self::$_instance->_debugger_channel) {
			self::$_instance->_debugger_channel->flush();
			self::$_instance->_debugger_response->sendHeaders();
			ob_end_flush();
		}
		
		if (self::$_instance->cronMode) {
			if ( php_sapi_name() == 'cli' ) {
				$txt = ob_get_contents();
				ob_end_clean();
				if (self::$_instance->cronLog) {
					file_put_contents(self::$_instance->cronLog, date('Y.m.d H:i:s')."\n===================\n".$txt."\n\n", FILE_APPEND);
				}				
				exit(self::$_instance->exitCode);
			}				
		}
		
		if ( php_sapi_name() != 'cli' ) {
			if (self::$_instance->exitCode) {
				http_response_code(500);
			}	
		}		
    }
    
    /*
    * Принимает отладочное сооющение
    * 
    * @param string тип сообщения
    * @param string сообщение
    * @return void    
    */
    public function debug($mode, $str)
    {
    	if (!$this->_debugger) return;
    	$this->_debugger->log($str, $mode);
    }
        
	/**
	 * Возвращает текущий сервер
	 * 	 	   
	 * @return Server
	 */ 
    public function getServer()
    {
        if (!$this->_server) {
            $this->_server = Server::getByDomain($_SERVER['HTTP_HOST']);
            if ($this->_server)
                $this->debug(DEBUG_COMMON, 'Server ID: '.$this->_server->id);
        }
        return $this->_server;
    }
    
    /**
     * Установливает текущий сервер.
     *      
     * @param  int|Server $server сервер
     * @return void
     */
    public function setServer($server)
    {
        if (is_a($server,'Cetera\\Server')) {
            $this->_server = $server;
            return $this;
        }
        if (is_int($server)) {
            $this->_server = Server::getById($server);
            return $this;        
        }
    }    
    
	/**
	 * Возвращает текущий раздел
	 * 	 	   
	 * @return Catalog 
	 */ 
    public function getCatalog()
    {
        if (!$this->_catalog) $this->decodeRequestUri();
        return $this->_catalog;
    }
    
	/**
	 * Возвращает нераспарсенную часть REQUEST_URI
	 * 	 	   
	 * @return string	 
	 */ 
    public function getUnparsedUrl()
    {
        if ($this->_unparsedUrl === null) $this->decodeRequestUri();
        return urldecode($this->_unparsedUrl);
    }
    
	/**
	 * Возвращает путь до текущего раздела
	 * 	 	   
	 * @return array	 
	 */ 
    public function getUrlPath()
    {
        if ($this->_unparsedUrl === null) $this->decodeRequestUri();
        return $this->_urlPath;
    }
    
	/**
	 * Мы в previewMode? 
	 *        
	 * @return bool
	 */ 
	public function previewMode()
	{
	   return $this->_previewMode;
  }
  
	public function setPreviewMode($mode)
	{
	   $this->_previewMode = $mode;
  }
    
	/**
	 * Регистрирует обработчик определенных URL на фронтофисе. Если REQUEST_URI начинается с $path, то запускается данный обработчик
	 * 	 	
  	 * @param string   
  	 * @param callable   	 
	 * @return void	 
	 */ 	
	public function route($path, $hook)
	{
		if (is_callable($hook))
			$this->routes['general'][$path] = $hook;
	}
	
	/**
	 * Парсит REQUEST_URI
	 * 	 	   
	 * @return void	 
	 */ 
    private function decodeRequestUri()
    {
		foreach ($this->routes['general'] as $p => $callable)
		{
			if (substr($_SERVER['REQUEST_URI'],0,strlen($p)) == $p )
			{
				list($url) = explode('?',$_SERVER['REQUEST_URI']);
				$this->_unparsedUrl = trim(str_replace($p, '', $url),'/');
				$res = $callable();
				if ($res) die();
			}
		}		
		
		$this->_unparsedUrl = null;
		
        $this->_catalog = $this->getServer();
        $this->_urlPath[] = $this->_catalog;
        if (!$this->_catalog) return;
        list($url) = explode('?',$_SERVER['REQUEST_URI']);
        $url = trim($url, '/');
    	$dir = explode("/", $url);
    	
		/*
    	if (sizeof($dir) && $dir[0] == PREVIEW_PREFIX) {
            array_shift($dir);
            $this->_previewMode = true;
        }
		*/

    	  while ($alias = array_shift($dir)) {
    	    try {
                $c = $this->_catalog->prototype->getChildByAlias($alias);
                $this->_catalog = $c;
                $this->_urlPath[] = $this->_catalog;
                $this->debug(DEBUG_COMMON, 'Catalog: '.$alias.'('.$c->id.')');
            } catch (\Exception $e) {
                array_unshift($dir, $alias);
                break;
            }
        }
        
        $this->_unparsedUrl = implode('/', $dir);
    }                                              
    
	/**
	 * Работает Front office? 	 
	 *        
	 * @return bool
	 */ 
	public function isFrontOffice()
	{
	   return $this->_fo != null;
    }
    	
  	/**
  	 * Указать приложению, работает FrontOffice или нет 	 
  	 * 
  	 * @param bool         
  	 * @return void
  	 */ 
  	public function setFrontOffice($fo = TRUE)
  	{
  	   if ($fo != $this->_fo) $this->_translator = FALSE;
  	   $this->_fo = $fo;
    }
        
    public function registerOutputHandler($function)
    {
        $this->_result_handler[] = $function;
    }
    
    public function applyOutputHandler(& $result)
    {
        $this->parseWidgets($result);
		$this->parseParams($result);
        
        foreach($this->_result_handler as $handler) {
        
            if (is_array($handler)) {
            
                if ( is_object($handler[0]) )
                    $handler[0]->$handler[1]($result); 
                    else $handler[0]::$handler[1]($result);
            
            } elseif (function_exists($handler)) {
            
                $handler($result);
                
            } elseif (file_exists($handler)) {
            
                include($handler);
            
            }
        }
    }
    
	/**
	 * Возвращает путь к шаблонам
	 * 	 	   
	 * @return string
	 */ 
    public function getTemplateDir() {
        if ($this->getServer()->templateDir) return DOCROOT.$this->getServer()->templateDir;            
        return TEMPLATES_DIR;
    } 
    
    public function getTemplatePath($name)
    {
        $p = '/'.trim($this->getTemplateDir().$this->getCatalog()->url,'/');
        while(strrpos($p, '/') !== false) {
            $f = $p.'/'.$name;
            try {
                if (file_exists($f)) return $f;
            } catch (\Exception $e) {
                break;
            }
            $p = substr($p, 0, strrpos($p, '/'));
        }
        return $this->getTemplateDir().'/'.$name;
    }
    
    public function includeTemplate($name)
    {
        include($this->getTemplatePath($name));
    }
    
	/**
	 * Возвращает список встроенных в систему групп пользователей
	 * 	 	   
	 * @return array	 
	 */ 
    public function getGroups()
    {
        if (!sizeof($this->_groups)) {
            $translator = $this->getTranslator();
            $this->_groups = array(
                GROUP_ALL => array(
                    'id' => GROUP_ALL,
                    'name' => $translator->_('Все'),
                    'describ' => '',
                    'user_defined' => 0
                ),
                GROUP_BACKOFFICE => array(
                    'id' => GROUP_BACKOFFICE,
                    'name' => $translator->_('Пользователи BackOffice'),
                    'describ' => $translator->_('Имеют право входа в систему управления сайтом'),
                    'user_defined' => 0
                ),
                GROUP_ADMIN => array(
                    'id' => GROUP_ADMIN,
                    'name' => $translator->_('Администраторы'),
                    'describ' => $translator->_('Администраторы имеют полные, ничем неограниченные права доступа'),
                    'user_defined' => 0
                ),
            );
        }
        return $this->_groups + $this->_user_groups;
    }
    
	/**
	 * Добавляет новую встроенную группу
	 * 	 
	 * @param array данные группы     	   
	 * @return void	 
	 */ 
    public function addUserGroup($group)
    {
        if (!isset($group['id'])) return;
        $group['user_defined'] = 0;
        $this->_user_groups[$group['id']] = $group;
    }
    
    /*
    * @ignore
    */
    public function initPlugins()
    {
        if ($this->_plugins_loaded) return;
        
        $this->_plugins_loaded = array();

        $translator = $this->getTranslator();
                
        foreach (Plugin::enum() as $plugin)
		{
            if (!$plugin->isEnabled()) continue;
           
            if (file_exists(DOCROOT.PLUGIN_DIR.'/'.$plugin->name.'/'.PLUGIN_CLASSES) && is_dir(DOCROOT.PLUGIN_DIR.'/'.$plugin->name.'/'.PLUGIN_CLASSES))
			{
                $loader = new \Composer\Autoload\ClassLoader();
				
				$parts = explode('_',$plugin->name);
				$prefix = '';
				foreach ($parts as $p) $prefix .= ucfirst($p);
				
                $loader->add($prefix, DOCROOT.PLUGIN_DIR.'/'.$plugin->name.'/'.PLUGIN_CLASSES);
                $loader->register();
            }            
            
			if (file_exists(DOCROOT.PLUGIN_DIR.'/'.$plugin->name.'/widgets') && is_dir(DOCROOT.PLUGIN_DIR.'/'.$plugin->name.'/widgets'))
			{
				$this->_widgetPaths[] = DOCROOT.PLUGIN_DIR.'/'.$plugin->name.'/widgets';
			}
                
            $this->_plugins_loaded[] = $plugin;      
        }
		
		foreach ($this->_plugins_loaded as $plugin) {
			if (file_exists(DOCROOT.PLUGIN_DIR.'/'.$plugin->name.'/'.PLUGIN_CONFIG))
				include(DOCROOT.PLUGIN_DIR.'/'.$plugin->name.'/'.PLUGIN_CONFIG); 
		}
		
		foreach (Theme::enum() as $theme)
		{
			
            if (file_exists(DOCROOT.THEME_DIR.'/'.$theme->name.'/classes') && is_dir(DOCROOT.THEME_DIR.'/'.$theme->name.'/classes'))
			{
                $loader = new \Composer\Autoload\ClassLoader();
                $loader->add(ucfirst($theme->name), DOCROOT.THEME_DIR.'/'.$theme->name.'/classes');
                $loader->register();
            }   			
            
            if (file_exists(DOCROOT.THEME_DIR.'/'.$theme->name.'/'.PLUGIN_CONFIG))
        		    include(DOCROOT.THEME_DIR.'/'.$theme->name.'/'.PLUGIN_CONFIG);  
		
		}
        
    }

    /**
     * Получить список установленных плагинов
	 *
     * @return array
     */	
	public function getPlugins()
	{
		$this->initPlugins();
		return $this->_plugins_loaded;
	}
        
    /**
     * Получить пользовательскую переменную
     *      
     * @param string $name имя переменной     
     * @return mixed
     */
    public function getUserVar($name, $server = 0)
    {
          if (!$server) $server = $this->getServer()->id;
          $server = (int)$server;
          if (!$server) return FALSE;
           
          $slot = new Cache\Slot\Variable($server, $name);
          if (false === ($value = $slot->load())) {
            
            $value = $this->getConn()->fetchColumn("SELECT IFNULL(B.value, A.value) FROM vars A LEFT JOIN vars_servers B ON (A.id=B.var_id and B.server_id=?) where A.name=?",
                array($server, $name),
                0
            );            
            
        	if ($value) {
                $slot->addTag(new Cache\Tag\Variable());
                $slot->save($value);
            }
          }
          return $value;
    }
    
    /**
     * Получить уникальный идентификаор клиента FO
     *         
     * @return string
     */
    public function getUid()
    {
        return $this->_uid;
    }
    
    /**
     * Получить timestamp последнего посещения FO
     *         
     * @return string
     */
    public function getLastVisit()
    {
        return $this->_last_visit;
    }
    
    /**
    * @ignore
    */
    public function getAuth()
    {
        $za = \Zend_Auth::getInstance();
        $za->setStorage(new \Zend_Auth_Storage_Session( SESSION_NAMESPACE ));
        return $za;
    }
    
    /**
     * Делает запись в журнал аудита
     *      
     * @param int $event_code код события      
     * @param string $text дополнительное описание      
     * @return void
    */    
    public function eventLog($event_code, $text = FALSE)
    {
        if (!$this->_connected) return;
		$this->getConn()->insert(
			'event_log',
			array(
				'dat'     => new \DateTime(),
				'user_id' => (int)$this->getUser()->id,
				'code'    => (int)$event_code,
				'text'    => $text,
			),
			array('datetime')
		);
    }
    	
    /**
     * Возвращает виджет
     *      
     * @param mixed $param<br>
     *                integer - возвращает сохраненный в БД виджет с указанным id<br>
     *                string - возвращает созданный в БО виджет с указанным Alias или создает экземпляр виджета c указанным Name<br>
     *                array - создает экземпляр виджета с заданными параметрами. Обязательный параметр в массиве - Name виджета<br>                        
     * @return Widget
    */  
    public function getWidget($name, $params = null, $uid = null)
    {
        $id = 0;
        $f = null;
        $widgetName = null;
        
        if (is_array($name))
		{        
			$params = $name;
            if (!isset($params['name'])) throw new Exception('Не задан параметр "name"');
            $widgetName = $params['name'];
        }
		elseif (is_int($name))
		{          
			$id = (int)$name;  
            $f = $this->getConn()->fetchAssoc('SELECT * FROM widgets WHERE id=?', array($id));
            if (!$f) throw new \Exception('Не удалось загрузить виджет id='.$name);
            $widgetName = $f['widgetName'];
   
            $params = unserialize($f['params']); 
                
        }
		elseif(!$params)
		{        
            $f = $this->getConn()->fetchAssoc('SELECT * FROM widgets WHERE widgetAlias=? ORDER BY id DESC', array($name));
            if ($f) {
                $id = $f['id']; 
                $widgetName = $f['widgetName'];
                $params = unserialize($f['params']);                
            } else {
                $widgetName = $name;
            }
            
        } 
		else
		{
			$widgetName = $name;
		}
		
		$this->initWidgets();
    
        if (!isset($this->_widgets[$widgetName])) {
        
            if (!isset($this->_widgetAliases[$widgetName])) 
                throw new \Exception( sprintf($this->getTranslator()->_('Виджет "%s" не зарегистрирован'),$widgetName) );
                
            $widgetName = $this->_widgetAliases[$widgetName];
        }
        
        $class = $this->_widgets[$widgetName]['class'];
        $widget = new $class($id, $params, $uid);
        $widget->application = $this;
        $widget->widgetName = $widgetName;
		$widget->widgetDescrib = $this->_widgets[$widgetName]['describ'];
		
		if ($f)
		{
			$widget->widgetAlias = $f['widgetAlias'];
			$widget->widgetTitle = $f['widgetTitle'];
			$widget->widgetDisabled = $f['widgetDisabled'];
			$widget->widgetProtected = $f['protected'];			
		}	
        
        return $widget;
    }
        
    /**
     * Добавляет виджет в коллекцию
     *   
     * @return void          
     */
    public function registerWidget($config)
    {	
        if (!isset($config['name'])) return false;
        
		$config['path'] = $this->getCallerPath();
		
        $name = $config['name'];
	
        if (class_exists($config['class']) && is_subclass_of($config['class'], '\\Cetera\\Widget\\Widget'))
		{
			$config['class']::setData($config);
		}
		else
		{
			$config['class'] = '\\Cetera\\Widget\\Widget';
		}
            
        $config['icon'] = $this->getCallerPath().'/'.$config['icon'];
    
		$this->initWidgets();
        $this->_widgets[$name] = $config;
    } 
    
    /**
     * Возвращает список зарегистрированных виджетов
     *   
     * @return array          
     */
    public function getRegisteredWidgets()
    {
		$this->initWidgets();
        return $this->_widgets;
    }
	
	private function initWidgets()
	{
		if (is_array($this->_widgets)) return;
		
		$t = $this->getTranslator();
		
		$this->_widgets = array(
			'Container' => array(
				'name'    => 'Container',
				'class'   => '\\Cetera\\Widget\\Container',
				'describ' => $t->_('Контейнер'),
				'icon'    => '/cms/images/16X16/pack.gif',
				'not_placeable' => true
			),
			   
			'Menu' => array(
				'name'    => 'Menu',
				'class'   => '\\Cetera\\Widget\\Menu',
				'describ' => $t->_('Меню раздела'),
				'icon'    => '/cms/images/folders.gif'
			),
			
			'MenuUser' => array(
				'name'    => 'MenuUser',
				'class'   => '\\Cetera\\Widget\\MenuUser',
				'describ' => $t->_('Пользовательское меню'),
				'icon'    => '/cms/images/icon_menu.png'
			),
			  
			'List' => array(
				'name'    => 'List',
				'class'   => '\\Cetera\\Widget\\WList',
				'describ' => $t->_('Список материалов'),
				'icon'    => '/cms/images/icon_list.png'
			),
			
			'Filter' => array(
				'name'    => 'Filter',
				'class'   => '\\Cetera\\Widget\\Filter',
				'describ' => $t->_('Фильтр'),
				'not_placeable' => true
			),			
					
			'Html' => array(
				'name'    => 'Html',
				'class'   => '\\Cetera\\Widget\\Html',
				'describ' => 'HTML',
				'icon'    => '/cms/images/html.gif'
			),
			 
			'File' => array(
				'name'    => 'File',
				'class'   => '\\Cetera\\Widget\\File',
				'describ' => $t->_('Файл'),
				'icon'    => '/cms/images/16X16/documents.gif'
			),  

			'Paginator' => array(
				'name'    => 'Paginator',
				'class'   => '\\Cetera\\Widget\\Paginator',
				'describ' => $t->_('Страничная навигация'),
				'not_placeable' => true
			),		
			
			'Breadcrumbs' => array(
				'name'    => 'Breadcrumbs',
				'class'   => '\\Cetera\\Widget\\Breadcrumbs',
				'describ' => $t->_('"Хлебные крошки"'),
				'not_placeable' => true
			),		
			
			'Search' => array(
				'name'    => 'Search',
				'class'   => '\\Cetera\\Widget\\Search',
				'describ' => $t->_('Поиск по сайту'),
				'not_placeable' => true
			),		

			'Material' => array(
				'name'    => 'Material',
				'class'   => '\\Cetera\\Widget\\Material',
				'describ' => $t->_('Материал'),
				'icon'    => '/cms/images/math1.gif',
			),		

			'Section' => array(
				'name'    => 'Section',
				'class'   => '\\Cetera\\Widget\\Section',
				'describ' => 'Раздел',
				'icon'    => '/cms/images/math2.gif',
			),		

			'Calendar' => array(
				'name'    => 'Calendar',
				'class'   => '\\Cetera\\Widget\\Calendar',
				'describ' => $t->_('Календарь'),
				'icon'    => '/cms/images/math2.gif',
			),
			
			'User.Auth' => array(
				'name'    => 'User.Auth',
				'class'   => '\\Cetera\\Widget\\User\\Auth',
				'describ' => $t->_('Форма входа'),
				'icon'    => '/cms/images/user.gif',
			),
			
			'User.Register' => array(
				'name'    => 'User.Register',
				'class'   => '\\Cetera\\Widget\\User\\Register',
				'describ' => $t->_('Форма регистрации'),
				'icon'    => '/cms/images/user.gif',
			),
			
			'User.Profile.Edit' => array(
				'name'    => 'User.Profile.Edit',
				'class'   => '\\Cetera\\Widget\\User\\ProfileEdit',
				'describ' => $t->_('Персональные данные'),
				'icon'    => '/cms/images/user.gif',
			),

			'User.Recover' => array(
				'name'    => 'User.Recover',
				'class'   => '\\Cetera\\Widget\\User\\Recover',
				'describ' => $t->_('Форма восстановления пароля'),
				'icon'    => '/cms/images/user.gif',
			),
					
			
		);		
	}
	
	public function initMailEvents()
	{
		$t = $this->getTranslator();
		
		$params = array(		
			'user.id'       => $t->_('ID пользователя'),
			'user.email'    => $t->_('E-mail пользователя'),
			'user.name'     => $t->_('Имя пользователя'),
			'password' => $t->_('Пароль пользователя'),
			'server.name'   => $t->_('Имя сайта'),
			'server.url'    => $t->_('Адрес сайта'),
		);

		\Cetera\Mail\Event::register('USER_REGISTER', $t->_('Регистрация пользователя'), $params);
		\Cetera\Mail\Event::register('USER_RECOVER', $t->_('Восстановление пароля пользователя'), $params);		
	}
	
	public function parseParams(& $result)
	{
		$props = array();
	
		preg_match_all('@\<cms(.+)\>[^\<]*\<\/cms\>@iU',$result,$matches);
		if (sizeof($matches[0])) foreach ($matches[0] as $i => $str)
		{
			preg_match_all('@(.+)="([^"]+)"@iU',$matches[1][$i],$m);
			$attrs = array();
            if (sizeof($m[1])) foreach ($m[1] as $i => $attr_name)
			{
                $attrs[trim($attr_name)] = $m[2][$i];
            }
			switch ($attrs['action'])
			{
				case 'widget':
					$params = false;
					parse_str ( htmlspecialchars_decode( $attrs['widgetparams'] ), $params );   
					$params['name'] = $attrs['widgetname'];
					$widget = $this->getWidget($params);
					$result = str_replace($str, $widget->getHtml(), $result);									
					break;
					
				case 'param':
					$props[$str] = $attrs['value'];				
					break;
			}
		}
		
		foreach ($props as $key => $value) {
			$value = $this->getPageProperty( $value );
			if (is_array($value)) {
				$value = implode("\n", $value);
			}		
			$result = str_replace($key, $value, $result);			
		}
		
	}
		       
    public function parseWidgets(& $result) {
        preg_match_all('@\<cms_widget(.+)\>\<\/cms_widget\>@iU',$result,$matches);
        if (sizeof($matches[0])) foreach ($matches[0] as $i => $widget_str) {
        
            preg_match_all('@(widgetname|widgetparams)="([^"]+)"@iU',$matches[1][$i],$m);
            $name = false;
            $params = false;
            if (sizeof($m[1])) foreach ($m[1] as $i => $attr_name) {
                switch ($attr_name) {
                    case 'widgetname':
                        $name = $m[2][$i];
                        break;
                    case 'widgetparams':                                        
                        parse_str ( htmlspecialchars_decode($m[2][$i]), $params );                                         
                        break;
                }
            }
            
            if ($name) {
                $widget = $this->getWidget(array('name' => $name));
                $widget->setParams($params);
                $result = str_replace($widget_str, $widget->getHtml(), $result);
            }
            
        }
    }
    
    public function registerCronJob($file)
    {
        $this->_cronJob[] = $file;
    }    

    public function getCronJobs()
    {
        return $this->_cronJob;
    } 
    
    public function getCallerPath()
    {
            $d = debug_backtrace();     
			$path = str_replace('N:\\CeteraCMS\\distrib\\','',dirname($d[1]['file']));   
            $path = str_replace('N:\\CeteraCMS\\','',$path);   			
            $path = str_replace('\\','/', $path);           
            $path = str_replace(DOCROOT,'',$path);
            return '/'.$path;      
    }
    
    /**
     * @deprecated используй BackOffice::addModule()          
     */	
    public function addModule()
    {
    } 

	/*
	* @internal
	*/
	public function ping()
	{
		try {
			
			$last_ping = (int)self::configGet('last_ping');
			
			if ( time() - $last_ping < 60*60*24 ) return;
			
			$plugins = array();
			foreach ( $this->getPlugins() as $p) 
			{
				$plugins[] = array(
					'id'      => $p->id,
					'version' => $p->version,
					'title'   => $p->title
				);
			}
			
			$client = new \GuzzleHttp\Client();
			$res = $client->post(PING_URL, array(
				'verify' => false,
				'form_params' => array(
					'server'  => $_SERVER,
					'version' => VERSION,
					'plugins' => $plugins
				)
			));
			
			if ($res->getStatusCode() == 200 /*&& $res->getBody() == 'OK'*/)
			{		
				self::configSet('last_ping', time() );
			}
		
		} catch (\Exception $e) {}
	}
	
	/**
	* Создает и возвращает шаблонизатор Twig
	*
	* @return Twig_Environment
	*/		
	public function getTwig()
	{
		
		if (!$this->twig)
		{
			$loader = new \Twig_Loader_Filesystem( $this->getTemplateDir().'/'.TWIG_TEMPLATES_PATH);
			
			if (file_exists($this->getTemplateDir().'/widgets'))
				$loader->addPath( $this->getTemplateDir().'/widgets' ,'widget' );
			
			foreach ($this->_widgetPaths as $p) $loader->addPath( $p ,'widget' );			
			$loader->addPath( CMSROOT.'/widgets' ,'widget' );
			
			foreach ($this->_widgetPaths as $p) $loader->addPath( $p ,'widget_distrib' );			
			$loader->addPath( CMSROOT.'/widgets' ,'widget_distrib' );			
			
			$options = array(
				'cache'       => CACHE_DIR.'/twig',
				'auto_reload' => true,
				'strict_variables' => true,
			);
			
			if ($this->debugMode) {
				$options['cache'] = false;
				$options['debug'] = true;
			}
			
			$this->twig = new \Twig_Environment($loader, $options);

			$this->twig->addTokenParser( new Twig\TokenParser\Widget() );
			$this->twig->addExtension( new \Twig_Extensions_Extension_Text() );	
			$this->twig->addExtension(new \Twig_Extensions_Extension_I18n());

			$this->twig->addFunction(new \Twig_SimpleFunction('_', function($text) {
				return Application::getInstance()->getTranslator()->_($text);
			}));
			
			$this->twig->addFilter( new \Twig_SimpleFilter('phone', function($num) {
				
				return preg_replace('/[^\+^\d]/i','', $num);
				
			} ) );
			
			$this->twig->addGlobal('application', $this);
			$this->twig->addGlobal('t', $this->getTranslator());
			$this->twig->addGlobal('s',  $_SERVER);
			
		}
		
		return $this->twig;
		
	}
	
	/**
	* Выводит на странице фронтофиса админ-панель
	*
	* @return void
	*/		
	public function showAdminPanel()
	{
		$u = $this->getUser();
		if ($u && $u->allowBackOffice())
		{
			$this->addCSS('/'.LIBRARY_PATH.'/extjs4/resources/css/ext-all.css');
			$this->addCSS('/cms/css/main.css');
			$this->addScript('/'.LIBRARY_PATH.'/extjs4/ext-all.js');
			$this->addScript('/cms/config.php');
			$this->addScript('/cms/admin-panel.js');
		}
	}	
		
	/**
	* Выводит в поток вывода значение свойства, заданного с помощью Cetera\Application::setPageProperty('name', 'value')
	*
	* @param  string $name Имя свойства
	* @return void
	*/			
	public function showPageProperty($name)
	{
		echo '<cms action="param" value="'.$name.'"></cms>';
	}
	
	/**
	* Выводит в поток вывода тег <meta> со значением свойства, заданного с помощью Cetera\Application::setPageProperty('name', 'value')
	*
	* @param  string $name Имя свойства
	* @return void
	*/	
	public function showMeta($name)
	{
		echo '<meta name="'.$name.'" content="';
		$this->showPageProperty($name);
		echo '" />';
	}
	
	/**
	* Выводит в поток вывода тег <title>
	* Содержание задается с помощью Cetera\Application::setPageProperty('title', 'value')
	*
	* @return void
	*/		
	public function showTitle()
	{
		echo '<title>';
		$this->showPageProperty('title');
		echo '</title>';
	}

	/**
	* Выводит в поток вывода строки, добавленные методом Cetera\Application::addHeadString()
	*
	* @return void
	*/	
	public function showHeadStrings()
	{
		$this->showPageProperty("headStrings");
	}	
	
	/**
	* Выводит в поток вывода стили, добавленные методом Cetera\Application::addCSS()
	*
	* @return void
	*/		
	public function showCSS()
	{
		$this->showPageProperty("css");
	}		
	
	/**
	* Выводит в поток вывода скрипты, добавленные методом Cetera\Application::addScript()
	*
	* @return void
	*/	
	public function showScripts()
	{
		$this->showPageProperty("scripts");
	}			
	
	/**
	* Возвращает текущее значение свойства страницы фронтофиса
	*
	* @param  string $name Имя свойства
	* @return mixed Значение свойства
	*/	
	public function getPageProperty($name)
	{
		return $this->params[$name];
	}	
	
	/**
	* Устанавливает определенное свойство страницы фронтофиса
	*
	* @param  string $name Имя свойства
	* @param  string $value Значение свойства
	* @param  boolean $array Используется ли свойство как массив значений. Если true, то новое значение добавляется в массив
	* @param  boolean $unique Проверять значение на уникальность. Если значение уже есть в массиве, то новое добавлено не будет
	* @return Cetera\Application Экземпляр приложения
	*/
	public function setPageProperty($name, $value, $array = false, $unique = true)
	{
		if (!$array)
		{
			$this->params[$name] = $value;
		} 
		else 
		{
			if (!isset($this->params[$name]) || !is_array($this->params[$name]))
			{
				$this->params[$name] = array();
			}	
			if (is_bool($unique))
			{
				if ( $unique && in_array($value, $this->params[$name]) ) return $this;
				$this->params[$name][] = $value;			
			}
			else
			{
				$this->params[$name][$unique] = $value;			
			}
		}
		return $this;
	}	
	
	/**
	* Добавляет строку в блок <head> страницы фронтофиса
	*
	* @param  string $value Строка для добавления
	* @param  boolean $unique Проверять строку на уникальность.
	* @return Cetera\Application Экземпляр приложения
	*/	
	public function addHeadString($value, $unique = true)
	{
		return $this->setPageProperty('headStrings', $value, true, $unique);
	}
	
	/**
	* Добавляет подключает css файл к странице фронтофиса
	*
	* @param  string $file Ссылка на файл
	* @return Cetera\Application Экземпляр приложения
	*/		
	public function addCSS($file)
	{
		return $this->setPageProperty('css', '<link rel="stylesheet" href="'.$file.'">', true, true);
	}	
	
	/**
	* Добавляет подключает js скрипт к странице фронтофиса
	*
	* @param  string $file Ссылка на файл
	* @return Cetera\Application Экземпляр приложения
	*/		
	public function addScript($file)
	{
		return $this->setPageProperty('scripts', '<script src="'.$file.'" type="text/javascript"></script>', true, true);
	}		
	
	/**
	* Переключает приложение в режим cron-работы.
	* При этом весь выходной поток, включая сообщения об ошибках направляются в лог-файл
	* По окончании работы скрипта в лог записывается текущее время
	*
	* @param  string $logFile Имя лог-файла
	* @return void
	*/	
	public function cronJob($logFile = false)
	{
		$this->cronMode = true;
		$this->cronLog = $logFile;
		if ( php_sapi_name() == 'cli' ) {
			ob_start();
		}
		else {
			header('Content-type: text/plain; charset=UTF-8');
		}		
	}
	
	/**
	* Возвращает значение из переменной $_REQUEST по заданному ключу
	*
	* @param  string $key
	* @return mixed Значение
	*/		
	public function getRequest($key) 
	{
		return $_REQUEST[$key];
	}
	
	public static function error_handler($errno, $errstr, $errfile, $errline ) {
		
		$application = self::$_instance;
		
		if ($errno == E_STRICT || $errno == E_NOTICE || $errno == E_DEPRECATED) {
			$errno = 'NOTICE';
			if ($errno == E_STRICT) $errno = 'STRICT';
			if ($errno == E_DEPRECATED) $errno = 'DEPRECATED';
			if ($application) $application->debug(DEBUG_ERROR_PHP, $errno.' '.$errstr.' '.$errfile.' Line: '.$errline);
			return;
		}

		throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
	}	
	
	public static function exception_handler($exception) {

		// Ошибка во время cronJob
		if (self::$_instance->cronMode) {
			print 'ERROR. '.$exception->getMessage()."\n";
			print $exception->getTraceAsString();
			self::$_instance->exit_code = 1;			
			return;
		}
	
		// Ошибка во время ajax обработки данных формы  
		if ($exception instanceof \Cetera\Exception\Form) {
			$res = array('success' => false);
			$res['errors'][$exception->field] = $exception->getMessage();
			echo json_encode($res);
			return;
		}

		self::$_instance->exitCode = 500;

		if ($exception instanceof \Cetera\Exception\CMS) {
			$ext_message = $exception->getExtMessage();
		} else {
			$ext_message = 'In file <b>'.$exception->getFile().'</b> on line: '.$exception->getLine()."<br /><br /><b>Stack trace:</b><br />".nl2br($exception->getTraceAsString());
		}
	  
		if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			
			// Ошибка во время ajax-запроса
				
			$res = array(
				'success'     => false,
				'message'     => $exception->getMessage(),
				'ext_message' => $ext_message,
				'rows'        => false
			);

			echo json_encode($res);
		
		} else {
		
			// Ошибка в Back office

				echo('<head><link rel="stylesheet" type="text/css" href="/'.CMS_DIR.'/css/main.css" /></head><body>'.
				 '<div id="progress"><table width="100%" height="100%" class="x-panel-mc">'.
				 '<tr><td align="center"><div class="panel"><b>'.$exception->getMessage().'</b><br /><br />'.
				 '<div style="text-align: left; padding: 10px">'.$ext_message.'</div></div></td></tr></table></div></body>'); 
		}
	}	
	
}
