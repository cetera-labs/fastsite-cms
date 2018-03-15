<?php
namespace Cetera; 

/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
/**
 * Класс для работы с подключаемыми модулями
 *
 * @package CeteraCMS
 **/ 
class Plugin implements \ArrayAccess  {

    private $_info = null;
    
    private static $disabled = null;
    
    public $name;
    
	/**
	* Возвращает все установленные модули
	*
	* @return array
	*/	
    public static function enum()
    {
        $plugins = array();
        if (file_exists(DOCROOT.PLUGIN_DIR) && is_dir(DOCROOT.PLUGIN_DIR) && $__dir = opendir(DOCROOT.PLUGIN_DIR))
		{
        	while (($__item = readdir($__dir)) !== false)
            {
          		if($__item=="." or $__item==".." or !is_dir(DOCROOT.PLUGIN_DIR.'/'.$__item)) continue;
        	    if (!file_exists(DOCROOT.PLUGIN_DIR.'/'.$__item.'/'.PLUGIN_INFO)) continue;
                $plugins[$__item] = new self($__item);
        	}  
        	closedir($__dir);
        }    
		ksort($plugins);
        return $plugins;
    }
    
	/**
	* Возвращает модуль с указанным именем
	*
	* @return Plugin
	*/	
    public static function find($name)
    {
        if (!is_dir(DOCROOT.PLUGIN_DIR.'/'.$name)) return false;
        if (!file_exists(DOCROOT.PLUGIN_DIR.'/'.$name.'/'.PLUGIN_INFO)) return false;
        return new self($name);
    }    
    
    private function __construct($name)
    {
        $this->name = $name;
    }
    
	/**
	* Включен ли модуль
	*
	* @return boolean
	*/		
    public function isEnabled ()
    {   
		
		try {
			// Если не хватает требуемых модулей, то отключаем
			$this->checkRequirements();
			// Проверяем соответствует ли версия CMS
			$this->checkVersion();
		}
		catch(\Exception $e) {
			return false;
		}		
		
        if (self::$disabled === null) {
			self::$disabled = Application::getInstance()->getVar('module_disable');
			if (!is_array(self::$disabled)) self::$disabled = array();
		}
        return !(boolean)(self::$disabled && self::$disabled[$this->name]);
    }
	
	public function checkVersion() {
		
		$application = Application::getInstance();
		$translator  = $application->getTranslator();
		
		// требуется более свежая CMS
		if ($this->cms_version_min && version_compare($this->cms_version_min, $application->getVersion()) > 0 ) {
			throw new \Exception(sprintf($translator->_('Не подходящая версия Cetera CMS. Требуется %s или выше'), $this->cms_version_min));
		}
		// CMS слишком новая
		if ($this->cms_version_max && version_compare($this->cms_version_max, $application->getVersion()) <= 0 ) {
			throw new \Exception(sprintf($translator->_('Не подходящая версия Cetera CMS. Требуется не выше %s'), $this->cms_version_max));
		}		
		
	}		
	
	public function checkRequirements() {  
	
		$translator = Application::getInstance()->getTranslator();
	
		if (is_array($this->requires)) {			
			foreach ($this->requires as $r) {				
				$pl = self::find( $r['plugin'] );
				if (!$pl) {
					throw new \Exception(sprintf($translator->_('Отсутствует модуль %s'), $r['plugin']));
				}
				if (!$pl->isEnabled()) {
					throw new \Exception(sprintf($translator->_('Требуемый модуль %s отключен'), $r['plugin']));
				}
				elseif (version_compare($r['version'], $pl['version']) > 0) {
					throw new \Exception(sprintf($translator->_('Установлен модуль %s v%s, требуется v%s'), $r['plugin'], $pl['version'], $r['version'] ));
				}								
			}			
		}	
	}	
    
	/**
	* Удаляет модуль
	*
	* @param boolean $data удалить из БД все данные модуля
	* @return void
	*/		
    public function delete($data = false)
    {    
		if ($this->name == 'partner') return;
        if ($data) {
            $schema = new Schema();  
            $schema->dropSchema($this->name);      
        }
        Util::delTree(WWWROOT.PLUGIN_DIR.'/'.$this->name);
    }  
    
	/**
	* Включить модуль
	*/		
    public function enable()
    {   
		try {
			$this->checkRequirements();
			$this->checkVersion();
		}
		catch(\Exception $e) {
			$translator = Application::getInstance()->getTranslator();
			throw new \Exception($translator->_('Невозможно включить модуль:').' '.$e->getMessage());
		}
	
		$a = Application::getInstance();
		$md = $a->getVar('module_disable');
		unset($md[$this->name]);
		$a->setVar('module_disable', $md);
    }   
        
	/**
	* Отключить модуль
	*/	
    public function disable()
    {     
		$a = Application::getInstance();
		$md = $a->getVar('module_disable');
		$md[$this->name] = 1;
		$a->setVar('module_disable', $md);
    }             
    
	/**
	* @ignore
	*/
    public function offsetExists($offset)
    {     
        $this->grabInfo();
        return array_key_exists ( $offset , $this->_info );    
    }
    
	/**
	* @ignore
	*/	
    public function offsetGet ( $offset )
    {    
        $this->grabInfo();
        return $this->_info[ $offset ];    
    }
    
	/**
	* @ignore
	*/	
    public function offsetSet ( $offset , $value ) {
        $this->_info[ $offset ] = $value;
    }
    
	/** 
	* @ignore
	*/	
    public function offsetUnset ( $offset ) {} 
    
	/**
	* @ignore
	*/	
    public function __get($name)
    {
    
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) return $this->$method();
        
        $this->grabInfo();
        if ($this->offsetExists ( $name )) return $this->_info[ $name ];
    
        return null;
    }    
    
    private function grabInfo()
    {
        if (is_array($this->_info)) return;
        
        if (file_exists(DOCROOT.PLUGIN_DIR.'/'.$this->name.'/'.PLUGIN_INFO)) {
        
            $this->_info = json_decode(implode('',file(DOCROOT.PLUGIN_DIR.'/'.$this->name.'/'.PLUGIN_INFO)), true);
            $this->_info['id'] = $this->name;
            
        } else {
            $this->_info = array(
                  'id'          => $this->name,
                  'title'       => $this->name,
                  'description' => ''
            );
        } 
        
        $this->_info['disabled'] = !$this->isEnabled();  
        
        if (!isset($this->_info['schema']))
		{
            if (file_exists(DOCROOT.PLUGIN_DIR.'/'.$this->name.'/'.PLUGIN_DB_SCHEMA)) 
                $this->_info['schema'] = DOCROOT.PLUGIN_DIR.'/'.$this->name.'/'.PLUGIN_DB_SCHEMA;   
        }
		else
		{
            $this->_info['schema'] = DOCROOT.PLUGIN_DIR.'/'.$this->name.'/'.$this->_info['schema'];
        }   
        if (isset($this->_info['sql']))
		{
			$this->_info['sql'] = DOCROOT.PLUGIN_DIR.'/'.$this->name.'/'.$this->_info['sql'];
        }  		
    }   
	
	/**
	* Установить модуль из Marketplace
	*
	* @param string $plugin название модуля
	* @param Callable $status метод или функция для приема сообщений
	* @param Zend_Translate $translator класс-переводчик	
	*/	
    public static function install($plugin, $status = null, $translator = null)
    {
		
        if (!$translator) $translator = Application::getInstance()->getTranslator();
		    
		$pluginPath = WWWROOT . PLUGIN_DIR . '/' . $plugin;
		$archiveFile = WWWROOT . PLUGIN_DIR . '/' . $plugin . '.zip';	

		if ($status) $status($translator->_('Загрузка плагина'), true);

        if (!file_exists(WWWROOT . PLUGIN_DIR))
            mkdir(WWWROOT . PLUGIN_DIR);

        if (!is_writable(WWWROOT . PLUGIN_DIR))
            throw new \Exception($translator->_('Каталог') . ' ' . DOCROOT . PLUGIN_DIR . ' ' . $translator->_('недоступен для записи'));

		$d = false;   
        try
		{			
            $client = new \GuzzleHttp\Client();
			$res = $client->request('GET', PLUGINS_INFO . '?download=' . $plugin, ['verify'=>false]);
            $d = $res->getBody();

            if (!$d) throw new \Exception($translator->_('Не удалось скачать плагин'));
        } 
		catch (\Exception $e)
		{
			if ($status)
			{
				$status($translator->_('Не удалось скачать плагин.'), false);  
				$status($translator->_('Переустановка ранее загруженного плагина'), true, true); 				
			}
        }

        if ($d)
		{
            file_put_contents($archiveFile, $d);

            if ($status) $status('OK', false);  

            if (file_exists($pluginPath))
			{
				if ($status) $status($translator->_('Удаление предыдущей версии'), true);
                Util::delTree($pluginPath);
                if ($status) $status('OK', false);  
            }

			if ($status) $status($translator->_('Распаковка архива'), true);

            $zip = new \ZipArchive;
            if ($zip->open($archiveFile) === TRUE)
			{
                if (!$zip->extractTo(WWWROOT . PLUGIN_DIR)) throw new Exception($translator->_('Не удалось распаковать архив'). ' ' . $archiveFile);
                $zip->close();
                unlink($archiveFile);

            } 
			else throw new \Exception($translator->_('Не удалось открыть архив'). ' ' . $archiveFile);

            if ($status) $status('OK', false);  
        }
		
		if ($status) $status($translator->_('Модификация БД'), true);
        $schema = new Schema(); 
        $schema->fixSchema('plugin_' . $plugin);
		$schema->readDump('plugin_' . $plugin);
        if ($status) $status('OK', false);  

        if (file_exists($pluginPath . '/' . PLUGIN_INSTALL))
            include $pluginPath . '/' . PLUGIN_INSTALL;
		
		$p = new self($plugin);
		self::installRequirements($p->requires, $status, $translator);
		
	}
	
	/**
	* Устанавливает модули, требующиеся для работы модуля или темы
	*
	* @param array $req список модулей
	* @param Callable $status метод или функция для приема сообщений
	* @param Zend_Translate $translator класс-переводчик
	*/	
    public static function installRequirements($req, $status = null, $translator = null)
    {
		if (!is_array($req)) return;
		if (!$translator) $translator = Application::getInstance()->getTranslator();
		if ($status) $status($translator->_('Проверка зависимостей:'), true, true); 				
		foreach ($req as $r)
		{
			$instal = false;
			if ($status) $status($translator->_('Плагин').' "'.$r['plugin'].'" ('.$r['version'].')', true);
			$pl = self::find( $r['plugin'] );
			if (!$pl)
			{
				$instal = true;
				if ($status) $status($translator->_('отсутствует'), false);  
			}
			else
			{
				 if (version_compare($r['version'], $pl['version']) > 0)
				 {
					 $instal = true;
					 if ($status) $status($translator->_('установлен').' '.$pl['version'], false);  
				 }
			}
			if ($instal)
			{
				if ($status) $status($translator->_('Установка/обновление').' "'.$r['plugin'].'"', true, true); 
				self::install($r['plugin'], $status, $translator);
			}
			else
			{
				if ($status) $status('OK', false);  
			}
		}		
	}		

}
