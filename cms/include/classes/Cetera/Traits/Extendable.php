<?php
namespace Cetera\Traits; 

trait Extendable {
	
	public static $extension = null;
	
	final public static function extend( $class )
	{
		static::$extension = $class;
	}
	
	final protected static function create()
	{
		if ( static::$extension ) {
			return new static::$extension();
		}
		return new static();
	}	
	
}