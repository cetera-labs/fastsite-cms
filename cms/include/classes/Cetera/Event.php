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
	
	private static $events = array();
	
	public static function register($id,$name = null,$parameters = null)
	{
		if (isset(self::$events[$id])) throw new \Exception('Событие "'.$id.'" уже зарегистрировано');
		self::$events[$id] = array(
			'id'         => $id,
			'name'       => $name,
			'parameters' => $parameters,
		);
	}

	public static function enum()
	{
		$res = array();
		foreach (self::$events as $id => $value) $res[] = $value;
		return $res;
	}	
   
    protected function __construct() {}

	public static function attach($event, $hook)
	{
		if (is_callable($hook)) {
			self::$listeners[$event][] = $hook;	
		}			
	}
	
	public static function trigger($event, $params)
	{
		if ( is_array(self::$listeners[$event]) ) {
			
			foreach (self::$listeners[$event] as $callable) {
				$callable($event, $params);
			}
			
		}
	}	
}
