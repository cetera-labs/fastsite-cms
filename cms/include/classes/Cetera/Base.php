<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera;
 
/**
 * Базовый класс для объектов системы
 *
 * @property int $id идентификатор объекта
 * 
 * @package FastsiteCMS
 */ 
abstract class Base {
   
    /** @internal */     
   protected $_id;
   
	/**
	 * Плагины
	 * @internal
	 */  
    public static $plugins = array();  
	private $pluginInstances = array(); 
   
    /**
     * Конструктор    
     *  
     * @internal	 
     * @param array поля объекта
     * @return void     
     */    
    protected function __construct($fields = null) 
    {
    	  if (is_array($fields))
            $this->setFields($fields);
    }
     
    /**
     * Устанавливает свойства объекта   
     * 
     * @internal     
     * @param array значения свойств объекта
     * @return void     
     */   	 
    protected function setFields($fields) 
    {
    	foreach ($fields as $name => $value) {
            $property = '_' . $name;
            if (property_exists($this, $property)) $this->$property = $value;
        }
    }    
    
    /**
	 * @internal
     * Конвертирует в json-строку {'id':ID_объекта}     
     */   		
    public function __toString()
    {
        return json_encode(array('id' => $this->id));
    }    
	
    /**
     * Возвращает объект в виде массива с указанными полями
     *            
     * @return array 
     */ 	
    public function asArray()
    {
		if (func_num_args() == 0) {
			$fields = ['id'];
		}
		else {
			$fields = func_get_args();
			if (count($fields)==1 && is_array($fields[0])) {
				$fields = $fields[0];
			}			
		}

		$obj = [];
		foreach ($fields as $k => $f) {
            
            if (!is_array($f)) {
                $k = $f;
            }
            
            $value = $this->$k;
            
            if ($value instanceof Base) {
                if (!is_array($f)) {
                    $value = $value->id;
                }
                else {
                    $value = $value->asArray($f);
                }
            }
            elseif ($value instanceof Iterator\Base) {
                
                if (!is_array($f)) {
                    $value = $value->asArray();
                }
                else {
                    $value = $value->asArray($f);
                }
                
            }
            
			$obj[$k] = $value;
		}
		return $obj;
    }
    
    public function getId()
    {
        return (int)$this->_id;
    }     
         
    /**
     * Перегрузка чтения свойств класса. 
     *           
     * Если в классе существует метод getСвойство(), то вызывается этот метод
     * Если в классе существует поле $_свойство, то возвращается это поле
     * В противном случает бросается исключение         
     *    
	 * @internal
     * @param string $name свойство класса          
     * @return mixed
     * @throws LogicException          
     */          
    public function __get($name)
    {
    
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) return $this->$method();
        
        $property = '_' . $name;
        if (property_exists($this, $property)) return $this->$property;
    
        throw new \LogicException("Property {$name} is not found");
    }

    /**
     * Перегрузка записи свойств класса. 
     *   
     * Если в классе существует метод setСвойство(), то вызывается этот метод
     * Если в классе существует поле $свойство, то полю присваивается значение свойства
     * В противном случает бросается исключение   
     *     
     * @param string $name свойство класса   
     * @param mixed $value значение свойства           
     * @return void
     * @throws LogicException          
     */ 
    public function __set($name, $value)
    {
    
        $method = 'set' . ucfirst($name);
        if (method_exists($this, $method)) return $this->$method($value);
                                 
        if (property_exists($this, $name)) {
            $this->$name = $value;
            return;
        }
    
        throw new \LogicException("Property {$name} is not found");
    }
     
    /**
     * Disallow cloning          
     */         
    final public function __clone(){}
	
    /**
     * Расширяет функциональность класса с помощью методов другого класса.
	 *
	 * Например, необходимо добавить в клас \Cetera\User метод, возвращающий кол-во дней, которые прошли с момента регистрации пользователя.
	 * Создадим класс-плагин:
	 * <pre>
	 * class MyUser extends \Cetera\ObjectPlugin {
	 *     public function getRegisteredDays() {
     *         // экземпляр класса, к каторому будет добавлен плагин находится в свойстве object
	 *         $date_reg = new DateTime($this->object->date_reg);
	 *         $date_now = new DateTime('now');
	 *         $diff = $date_now->getTimestamp() - $date_reg->getTimestamp();
	 *         return ceil($diff / (60*60*24));
	 *     }
	 * }
	 * </pre>
	 * Добавим плагин к классу \Cetera\User:
	 * <pre>
	 * \Cetera\User::addPlugin( 'MyUser' );
	 * </pre>
	 * Теперь можем использовать метод \Cetera\User::getRegisteredDays():
	 * <pre>
	 * $user = \Cetera\Application::getInstance()->getUser();
	 * echo 'Я с вами '.$user->getRegisteredDays().' дней!';
	 * </pre>	 
     *     
     * @param \Cetera\ObjectPlugin $class класс, методы которого добавить к данному   
     * @return void        
     */ 	
	public static function addPlugin( $class )
	{
		if (is_subclass_of($class, '\Cetera\ObjectPlugin'))
		{
			static::$plugins[] = $class;
		} 
		else 
		{
		    throw new \LogicException("{$class} must extend \\Cetera\\ObjectPlugin");
		}
	}
		
    public function __call($name, $arguments)
	{
		foreach ( static::$plugins as $plugin )
		{
			if ( method_exists ( $plugin , $name ) )
			{
				if (!isset( $this->pluginInstances[ $plugin ] ))
				{
					$this->pluginInstances[ $plugin ] = new $plugin( $this );
				}
				return call_user_func_array ( array( $this->pluginInstances[ $plugin ], $name ) , $arguments );
			}
		}
		if (!count($arguments))try
		{
			return static::__get( $name );
		}
		catch (\LogicException $e)
		{
			throw new \LogicException("Method {$name} is not exists");
		}
		throw new \LogicException("Method {$name} is not exists");
    }
	
    public function decodeLocaleString($str) 
    {
        return Application::getInstance()->decodeLocaleString($str);
    }
    
}
