<?php
namespace Cetera\Traits; 

trait Extendable {
	
	public static $extension = null;
	
	final public static function extend( $class )
	{
		static::$extensions = $class;
	}
	
	final protected static function create()
	{
		if ( static::$extension ) {
			return new static::$extensions();
		}
		return new static();
	}	
	
}