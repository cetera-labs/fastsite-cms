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
 * Родительский класс для создания плагинов расширяющих функциональность встроенных классов
 * Подробнее: <a href="Base.html#method_addPlugin">Cetera\Base::addPlugin</a>
 */ 
abstract class ObjectPlugin
{
	
	protected $object;
	
	/**
	* @ignore
	*/
	final public function __construct( $object ) {		
		$this->object = $object;		
	}
	
}