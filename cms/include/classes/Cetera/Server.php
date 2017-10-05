<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera; 

/**
 * Виртуальный сервер
 *
 * @package CeteraCMS
 **/
class Server extends Catalog {

    /**
     * Флаг, указывающий, что сервер является основным
	 * @internal
     */         
    const DEFAULT_SERVER = 1;
	
    /**
     * Подкаталог с шаблонами сервера
	 *
     * @internal         
     * @var string
     */ 
    protected $_templateDir;
	
    /**
     * Тема установленная для сервера
	 * @internal
     */ 	
	protected $_theme;
    
    /** @internal */
    public static function fetch($data, $a = null, $b = null)
    {
        return parent::fetch($data, true);
    }     
      
	/**
	 * Количество серверов.	
	 * 	 	 
	 * @return int
	 */  
	public static function count()
    {
		return self::getDbConnection()->fetchColumn("SELECT COUNT(*) FROM dir_data WHERE is_server<>0");
    } 
	
	/**
	 * Возвращает все сервера.	
	 * 	 	 
	 * @return Array
	 */ 
	public static function enum()
    {
      $result = array();
      $r = self::getDbConnection()->executeQuery('SELECT * FROM dir_data A WHERE is_server<>0 ORDER BY tag');
      while($fields = $r->fetch()) {
          $result[] = self::fetch($fields);
      }      

      return $result;
    } 
    
	/**
	 * Возвращает сервер по его доменному имени	
	 * 	 	
	 * @param string $domain доменное имя      
	 * @return Server
	 */ 
    public static function getByDomain($domain)
    {
        $slot = new Cache\Slot\ServerByDomain($domain);
        if (false === ($server = $slot->load())) {

            $fields = self::getDbConnection()->fetchAssoc( 'SELECT B.* FROM server_aliases A LEFT JOIN dir_data B USING (ID) WHERE B.hidden<>1 and B.is_server<>0 and A.name = ?', array( $domain ) );
            
            if ($fields) {
                $server = self::fetch($fields); 
            } else {
                $server = self::getDefault();
                if (!$server) return FALSE;
            }
            $slot->addTag(new Cache\Tag\CatalogID($server->id));
            $slot->save($server);
        }
        return $server;
    }  
    
	/**
	 * Возвращает основной сервер	
	 * 	 	   
	 * @return Server
	 */ 
    public static function getDefault()
    {
        $fields = self::getDbConnection()->fetchAssoc( "SELECT * FROM dir_data where hidden<>1 and is_server<>0 and type&".Server::DEFAULT_SERVER."=1" );
        if ($fields) {
            return self::fetch($fields);
        } else {
            return false;
        }     
    }
        
	/**
	 * Возвращает php шаблон, исполняемый "по умолчанию" для сервера
	 * 	 	   
	 * @return string
	 */ 
    public function getDefaultTemplate() {
        if ($this->_defaultTemplate) return $this->_defaultTemplate;
        
        if ($this->template) 
            $this->_defaultTemplate = $this->template;
            else $this->_defaultTemplate = DEFAULT_TEMPLATE;
            
        return $this->_defaultTemplate;
    }
      
    
    /**
     * Является ли раздел ссылкой на другой раздел. 
     * Всегда возвращает FALSE  
     *        
     * @return boolean
     */   
    public function isLink()
    {
        return FALSE;
    }
    
    /**
     * Является ли раздел сервером.
     * Всегда возвращает TRUE
     *         
     * @return boolean
     */ 
    public function isServer()
    {
        return TRUE;
    }
      
    
    /**
     * Изменение свойств сервера     
     *     
     * @param array $props новые свойства       
     * @return void   
     * @throws Exception      
     */  
    public function update($props)
    {
        if (isset($props['server_aliases']) || isset($props['alias'])) {
    	    self::getDbConnection()->executeQuery("DELETE FROM server_aliases WHERE id=".$this->id);
    	    if (isset($props['server_aliases'])){
                $alias = json_decode($props['server_aliases']);
                unset($props['server_aliases']);
            } else {
                $alias = array();
            }
    	    if (isset($props['alias'])) $alias[] = $props['alias'];
    	    foreach($alias as $al) if ($al) {
			   self::getDbConnection()->insert('server_aliases', array(
			       'id'   => $this->id,
				   'name' => $al
			   ));
        	   $slot = new Cache\Slot\ServerByDomain($al);
        	   $slot->remove();
    	    }
        }
        
        self::getDbConnection()->update('dir_data', array('templateDir' => $props['templateDir']), array('id'=>$this->id));
    
        parent::update($props);
    }
    
    /**
     * Возвращает тему, активированную для сервера
     *           
     * @return string
     */      
    public function getTheme()
    {
		if ($this->_theme === null)
		{
			$tname = str_replace( THEME_DIR.'/', '', $this->templateDir );
			if ($tname) {
				$theme = Theme::find($tname);
				if ($theme) $theme->loadConfig($this);				
				$this->_theme = $theme;
			} else {
				$this->_theme = false;
			}
		}
		return $this->_theme;
    }
    
    /**
     * Устанавливает тему, активированную для сервера
     *           
     * @return string
     */      
    public function setTheme(Theme $theme = null)
    {
        $theme_path = null;
        if ($theme) $theme_path = THEME_DIR.'/'.$theme->name;
        self::getDbConnection()->update('dir_data',array('templateDir'=>$theme_path),array('id'=>$this->id));
    }    
	
	public function getAliases()
	{
		$res = array();		
		$r = self::getDbConnection()->fetchAll("select name from server_aliases where name<>'".$this->alias."' and id=".$this->id." order by name");
		$i = 1;
		foreach ($r as $f) {
			$res[] = array(
				'id' => $i++,
				'name' => $f['name']
			);
		}	
		return $res;
	}
	
	public function getRobots()
	{
		return self::configGet('robots_'.$this->id);
	}
	
	public function setRobots($value)
	{
		self::configSet('robots_'.$this->id, $value);
		return $this;
	}	

	public function boArray()
	{
		$array = parent::boArray();
		$array['robots'] = $this->getRobots();
		return $array;
	}
	
}