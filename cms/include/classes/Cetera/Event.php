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
 * @package FastsiteCMS
 */ 
class Event {
	
	const EVENT_CORE_MATERIAL_COPY = 'CORE_MATERIAL_COPY';
	const EVENT_CORE_MATERIAL_BEFORE_SAVE = 'CORE_MATERIAL_BEFORE_SAVE';
	const EVENT_CORE_MATERIAL_AFTER_SAVE = 'CORE_MATERIAL_AFTER_SAVE';
	const EVENT_CORE_USER_REGISTER = 'CORE_USER_REGISTER';
	const EVENT_CORE_USER_RECOVER = 'CORE_USER_RECOVER';
    const EVENT_CORE_USER_DELETE = 'CORE_USER_DELETE';
	const EVENT_CORE_BO_LOGIN_OK = 1;
	const EVENT_CORE_BO_LOGIN_FAIL = 2;
	const EVENT_CORE_LOG_CLEAR = 3;
	const EVENT_CORE_DIR_CREATE = 4;
	const EVENT_CORE_DIR_EDIT = 5;
	const EVENT_CORE_DIR_DELETE = 6;
	const EVENT_CORE_MATH_CREATE = 7;
	const EVENT_CORE_MATH_EDIT = 8;
	const EVENT_CORE_MATH_DELETE = 9;
	const EVENT_CORE_MATH_PUB = 10;
	const EVENT_CORE_MATH_UNPUB = 11;
	const EVENT_CORE_USER_PROP = 12;
	
	private static $listeners = array();
		
	/**
	* @deprecated
	*/
	public static function register($id,$name = null,$parameters = null)
	{
		$bo = Application::getInstance()->getBo();
		if ($bo) return $bo->registerEvent($id, $name ,$parameters);
	}

	/**
	* @deprecated
	*/	
	public static function enum()
	{
		return Application::getInstance()->getBo()->getRegisteredEvents();
	}	
   
	/**
	* Повесить свой обработчик на событие
	*/	   
    protected function __construct() {}

	public static function attach($event, $hook)
	{
		if (is_callable($hook)) {
			self::$listeners[$event][] = $hook;	
		}			
	}
	
	/**
	* Сообщить о наступлении события
	*/		
	public static function trigger($event, $params = [])
	{
		if ( isset(self::$listeners[$event]) && is_array(self::$listeners[$event]) ) {			
			foreach (self::$listeners[$event] as $callable) {
				$callable($event, $params);
			}			
		}
		if ( isset(self::$listeners['*']) && is_array(self::$listeners['*']) ) {			
			foreach (self::$listeners['*'] as $callable) {
				$callable($event, $params);
			}			
		}	
	}	
}
