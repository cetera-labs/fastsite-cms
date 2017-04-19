<?php
namespace Cetera; 

/**
 * Меню.
 * 
 * @package CeteraCMS
 */ 
class Menu extends Base {

    protected $_name;
    protected $_alias;
    protected $_data;
    protected $_children;

    protected function __construct($fields) 
    {
        $fields['data'] = unserialize($fields['data']);
        parent::__construct($fields);
    }

    /**
     * Возвращает все созданные меню.    
     *          
     * @return array       
     */   
    public static function enum()
    {
        $res = array();
        $r = fssql_query('SELECT * FROM menus ORDER BY name');
        while ($f = mysql_fetch_assoc($r)) {
             $res[] = new self($f);
        }
        return $res;
    }
    
  	public static function getByResult($r) 
    {
    		if (!$r || !mysql_num_rows($r)) throw new Exception\CMS('Меню не найдено');
    		$f = mysql_fetch_assoc($r);
    		if (!$f) throw new Exception\CMS('Меню не найдено');
           
    		return new self($f);
  	}
  	
  	/**
  	 * Возвращает меню по его идентификатору.	
  	 * 	 
  	 * @param int $id ID меню 	 
  	 * @return Menu
  	 * @throws Exception\CMS	 
  	 */     	
  	public static function getById($id) 
    {
        $r = fssql_query('SELECT * FROM menus WHERE id='.$id);
  			return self::getByResult($r);
  	}
    
  	/**
  	 * Возвращает меню по его алиасу.	
  	 * 	 
  	 * @param string $alias алиас меню 	 
  	 * @return Menu
  	 * @throws Exception\CMS	 
  	 */     	
  	public static function getByAlias($alias) 
    {
        try {
            $r = fssql_query('SELECT * FROM menus WHERE alias="'.mysql_real_escape_string($alias).'"');
      			return self::getByResult($r);
        } catch (Exception $e) {
            throw new Exception\CMS('Меню '.$alias.' не найдено');
        }
  	}
    
  	/**
  	 * Возвращает меню по его названию.	
  	 * 	 
  	 * @param string $name название меню 	 
  	 * @return Menu
  	 * @throws Exception\CMS	 
  	 */     	
  	public static function getByName($name) 
    {
        $r = fssql_query('SELECT * FROM menus WHERE name="'.mysql_real_escape_string($name).'"');
  			return self::getByResult($r);
  	}
    
  	/**
  	 * Создает меню	
  	 * 
     * @param string $alias алиас меню 		 
  	 * @param string $name название меню 	 
  	 * @return Menu
  	 * @throws Exception\CMS	 
  	 */     	
  	public static function create($alias, $name) 
    {
        try {
            $m = self::getByAlias($alias);
        } catch (\Exception $e) {}
        
        if ($m) throw new Exception\CMS('Меню c таким алиасом уже существует.');
        
        fssql_query('INSERT INTO menus SET alias="'.mysql_real_escape_string($alias).'", name="'.mysql_real_escape_string($name).'"');
  			return self::getById(mysql_insert_id());
  	}
   
  	/**
  	 * Удаляет меню	
  	 * 	 
  	 */   
    public function delete()
    {
        fssql_query('DELETE FROM menus WHERE id='.$this->id);
    }
        
  	/**
  	 * Сохраняет меню	
  	 * 
  	 * @throws Exception\CMS	 
  	 */     	
  	public function save() 
    {
        try {
            $m = self::getByAlias($this->alias);
        } catch (\Exception $e) {}
        
        if ($m && $m->id != $this->id) throw new Exception\CMS('Меню c таким алиасом уже существует.');
        
        fssql_query('UPDATE menus SET alias="'.mysql_real_escape_string($this->alias).'", name="'.mysql_real_escape_string($this->name).'", data="'.mysql_real_escape_string(serialize($this->data)).'" WHERE id='.$this->id);
  	}  
    
    public function getChildren()
    {
        if (!is_array($this->_children)) {
            $this->_children = array();
            if (is_array($this->_data)) {
                $check = array();
                foreach ($this->_data as $d) {
                    try {
						if (isset($d['id']) && $d['id']) {
							$this->_children[] = DynamicFieldsObject::getByIdType($d['id'], $d['type']);
						}
						elseif (isset($d['url']) && $d['url']) {
							$this->_children[] = new ExternalLink($d['name'], $d['url']);
						}
						else {
							throw new \Exception('Cant parse menu child');
						}
                        $check[] = $d;
                    } catch (\Exception $e) {}
                }
                if (sizeof($check) < sizeof($this->_data)) {
                    $this->_data = $check;
                    $this->save();
                }
            }
        }
        return $this->_children;
    }  
    
}