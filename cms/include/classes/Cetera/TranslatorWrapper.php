<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
namespace Cetera;

use Zend\I18n\Translator\Translator;
 
class TranslatorWrapper extends Translator {
    
	public function _($message, $textDomain = 'default', $locale = null)
	{
		return parent::translate($message, $textDomain, $locale);
	}

    public function getMessages($locale = null, $textDomain = 'default')
    {        
        return (array)parent::getAllMessages($textDomain, $locale);
    }
    
}