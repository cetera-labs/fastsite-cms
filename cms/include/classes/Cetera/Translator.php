<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
namespace Cetera;
 
trait Translator {
    
	public static function t()
	{
		return \Cetera\Application::getInstance()->getTranslator();
	}		
    
}