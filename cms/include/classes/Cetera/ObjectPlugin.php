<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera; 
 
abstract class ObjectPlugin
{
	
	protected $object;
	
	final public function __construct( $object )
	{
		
		$this->object = $object;
		
	}
	
}