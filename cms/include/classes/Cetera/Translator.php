<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
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