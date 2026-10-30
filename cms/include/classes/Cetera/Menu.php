<?php
namespace Cetera; 

/**
 * Меню.
 * 
 * @package FastsiteCMS
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
		if (!$f) throw new Exception\CMS('Меню ID='.$id.' не найдено');
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
		if (!$f) throw new Exception\CMS('Меню alias='.$alias.' не найдено');
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
		if (!$f) throw new Exception\CMS('Меню name='.$name.' не найдено');
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
        
        if (isset($m)) throw new Exception\CMS('Меню c таким алиасом уже существует.');
        
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
        
        if (isset($m) && $m->id != $this->id) throw new Exception\CMS('Меню c таким алиасом уже существует.');
        
		self::getDbConnection()->update('menus',
			array(
				'alias' => $this->alias,
				'name'  => $this->name,
				'data'  => serialize($this->data),
			),
			array('id' => $this->id)
		);
  	}  
    
    /**
     * Разбирает массив данных меню в объекты вместе с флагом скрытия.
     */
    public static function resolveItems(array $data)
    {
        $items = array();
        foreach ($data as $d) {
            try {
                if (isset($d['id']) && $d['id']) {
                    $o = DynamicFieldsObject::getByIdType($d['id'], $d['type']);
                }
                elseif (isset($d['url']) && $d['url']) {
                    $o = new ExternalLink($d);
                }
                else {
                    throw new \Exception('Cant parse menu child');
                }
                $items[] = array('item' => $o, 'hidden' => !empty($d['hidden']), 'data' => $d);
            } catch (\Exception $e) {}
        }
        return $items;
    }

    /**
     * Все элементы меню, включая скрытые (для админки).
     */
    public function getItems()
    {
        return is_array($this->_data) ? self::resolveItems($this->_data) : array();
    }

    public function getChildren()
    {
        if (!is_array($this->_children)) {
            $this->_children = array();
            if (is_array($this->_data)) {
                $items = self::resolveItems($this->_data);
                foreach ($items as $i) {
                    if (!$i['hidden']) $this->_children[] = $i['item'];
                }
                if (sizeof($items) < sizeof($this->_data)) {
                    $this->_data = array_column($items, 'data');
                    $this->save();
                }
            }
        }
        return $this->_children;
    }  
    
    public function __toString()
    {
        return json_encode($this->getChildren());
    }       
    
}