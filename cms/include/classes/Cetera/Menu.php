<?php
namespace Cetera; 

/**
 * Меню.
 * 
 * @package CeteraCMS
 */ 
class Menu extends Base {
	
	use DbConnection;

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
        $r = self::getDbConnection()->query('SELECT * FROM menus ORDER BY name');
        while ($f = $r->fetch()) {
             $res[] = new self($f);
        }
        return $res;
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
		$f = self::getDbConnection()->fetchAssoc('SELECT * FROM menus WHERE id=?',array($id));
		if (!$f) new Exception\CMS('Меню ID='.$id.' не найдено');
		return new self($f);
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
		$f = self::getDbConnection()->fetchAssoc('SELECT * FROM menus WHERE alias=?',array($alias));
		if (!$f) new Exception\CMS('Меню alias='.$id.' не найдено');
		return new self($f);
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
		$f = self::getDbConnection()->fetchAssoc('SELECT * FROM menus WHERE name=?',array($name));
		if (!$f) new Exception\CMS('Меню name='.$id.' не найдено');
		return new self($f);
  	}
    
  	/**
  	 * Создает меню	
  	 * 
     * @param string $alias алиас меню 		 
  	 * @param string $name название меню 	 
  	 * @return Menu
  	 * @throws Exception\CMS	 
  	 */     	
  	public static function create() 
    {
		$alias = func_get_arg(0);
		$name = func_get_arg(1);
		
        try {
            $m = self::getByAlias($alias);
        } catch (\Exception $e) {}
        
        if ($m) throw new Exception\CMS('Меню c таким алиасом уже существует.');
        
        self::getDbConnection()->insert('menus', array(
			'alias' => $alias,
			'name'  => $name
		));
  		return self::getById( self::getDbConnection()->lastInsertId() );
  	}
   
  	/**
  	 * Удаляет меню	
  	 * 	 
  	 */   
    public function delete()
    {
        self::getDbConnection()->executeQuery('DELETE FROM menus WHERE id='.$this->id);
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
        
		self::getDbConnection()->update('menus',
			array(
				'alias' => $this->alias,
				'name'  => $this->name,
				'data'  => serialize($this->data),
			),
			array('id' => $this->id)
		);
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