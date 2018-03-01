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
 * @package CeteraCMS
 */ 
class Event {
	
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
		if ( is_array(self::$listeners[$event]) ) {			
			foreach (self::$listeners[$event] as $callable) {
				$callable($event, $params);
			}			
		}
		if ( is_array(self::$listeners['*']) ) {			
			foreach (self::$listeners['*'] as $callable) {
				$callable($event, $params);
			}			
		}		
	}	
}
