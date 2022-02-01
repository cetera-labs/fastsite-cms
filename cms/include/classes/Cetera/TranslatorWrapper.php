<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
namespace Cetera;

use Laminas\I18n\Translator\Translator;
 
class TranslatorWrapper extends Translator {
    
	public function _($message, $textDomain = 'default', $locale = null)
	{
		return parent::translate($message, $textDomain, $locale);
	}

    public function getMessages($locale = null, $textDomain = 'default')
    {        
        return (array)parent::getAllMessages($textDomain, $locale);
    }
    
    public function addTranslation($baseDir) {
        parent::addTranslationFilePattern('gettext', $baseDir, '%s.mo');
    }
    
}