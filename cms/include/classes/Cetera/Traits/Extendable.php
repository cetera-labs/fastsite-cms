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
	
}