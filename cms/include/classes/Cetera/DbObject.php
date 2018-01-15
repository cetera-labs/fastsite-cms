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
 * Класс для объектов, хранящихся в БД
 *
 * @property int $id идентификатор объекта
 * 
 * @package CeteraCMS
 */ 
abstract class DbObject {

    use DbConnection;
    
    /**
     * Значения полей объекта 
     *         
     * @var array    
     */  
    public $fields = array();
   
    public static function getTable() {
		 throw new \Exception('Method getTable() must be overriden');
	}
    
    public static function enum()
    {
    		$data = self::getDbConnection()->fetchAll('SELECT * FROM '.static::getTable().' ORDER BY id');	
        $res = array();
        foreach ($data as  $d) $res[] = new static($d);
        return $res;
    }
    
    /**
     * Конструктор    
     *  
     * @internal	 
     * @param array поля объекта
     * @return void     
     */    
    public function __construct($fields = null) 
    {
    	  if (is_array($fields))
            $this->setFields($fields);
    }
  	
  	public static function getById( $id )
  	{ 		
    		$data = self::getDbConnection()->fetchAssoc('
                  	SELECT * 
                  	FROM '.static::getTable().'
                  	WHERE id = ?',
                    array($id)
        );	
    		if ($data) return new static($data);
    		throw new \Exception('Объект ID='.$id.' не найден');		
  	}
    
  	public function delete()
  	{ 		
    		self::getDbConnection()->delete(static::getTable(), array('id' => $this->id));		
  	} 

  	public function save()
  	{ 
		foreach($this->fields as $k => $v) {
			if (is_bool($v)) {
				$this->fields[$k] = (int)$v;
			}
		}
        if ($this->id)
    		    self::getDbConnection()->update(static::getTable(), $this->fields, array('id' => $this->id));
            else {
                self::getDbConnection()->insert(static::getTable(), $this->fields);
                $this->id = $this->getDbConnection()->lastInsertId();
            }		
  	} 
    
    /**
     * Устанавливает поля объекта  
     *       
     * @internal	 
     * @param array $fields поля объекта     
     */	
    public function setFields($fields)
    {
        $this->fields = $fields;     
    }
    
    /**
     * Перегрузка чтения свойств класса.       
     *    
	   * @internal
     * @param string $name свойство класса          
     * @return mixed       
     */          
    public function __get($name)
    {
    
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) return $this->$method();
    
        return $this->fields[$name];
    }

    /**
     * Перегрузка записи свойств класса.  
     *     
     * @internal	 
     * @param string $name свойство класса   
     * @param mixed $value значение свойства           
     * @return void       
     */ 
    public function __set($name, $value)
    {
    
        $method = 'set' . ucfirst($name);
        if (method_exists($this, $method)) return $this->$method($value);
    
        $this->fields[$name] = $value;
    }
    
}
