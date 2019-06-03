<?php
namespace Cetera\Traits; 

trait Extendable {
	
	public static $extension = null;
	
	final public static function extend( $class )
	{
		static::$extension = $class;
	}
	
	public static function create()
	{
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