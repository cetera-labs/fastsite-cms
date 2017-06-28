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
   
    protected function __construct() 
    {}

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
