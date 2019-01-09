<?php
namespace Cetera\Traits; 

trait Extendable {
	
	public static $extension = null;
	
	final public static function extend( $class )
	{
		static::$extension = $class;
	}
	
	final public static function create()
	{
		//print "***".get_class().'==='.static::$extension."***\n";
		if ( static::$extension ) {
			return new static::$extension();
		}
		return new static();
	}

	final public static function callStatic($method)
	{
		if ( static::$extension ) {
			return static::$extension::$method();
		}
		return static::$method();		
	}
	
}