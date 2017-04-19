<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera\Exception;  
 
/**
 * Исключение Cetera CMS
 * 
 * @package CeteraCMS
 */ 
class CMS extends \Exception
{	

    const UNKNOWN                 = -999;
    const INVALID_PARAMS          = -1;
    const CAT_EXISTS              = -2;
    const CAT_PHYSICAL_EXISTS     = -3;
    const MATERIAL_NOT_FOUND      = -4;
    const USER_NOT_FOUND          = -5;
    const INVALID_PASSWORD        = -6;
    const AUTHORIZATION_FAILED    = -7;
    const INVALID_EMAIL           = -8;
    const SQL                     = -9;
    const USER_EXISTS             = -10;
    const PASSWORDS_DOESNT_MATCH  = -11;
    const SERVER_NOT_FOUND        = -12;
    const TYPE_EXISTS             = -13;
    const EMAIL_EXISTS            = -14;
    const CANT_CREATE             = -15;
    const MESSAGE_EXISTS          = -16;
    const NO_RIGHTS               = -17;
    const TYPE_RESERVED           = -18;
    const TYPE_FIXED              = -19;
    const FIELD_EXISTS            = -20;
    const FIELD_NOT_FOUND         = -21;
    const SERVER_LIMIT            = -22;
    const CAT_NOT_FOUND           = -23;
    const MATERIALS_LIMIT         = -24;
    const ALIAS_EXISTS            = -25;
    const ADD_FIELD               = -26;
    const EDIT_FIELD              = -27;
    const ENUM_FIELD              = -28;
    const FIELD_REQUIRED          = -29;
    const CHOOSE_CATALOG          = -30;
    const FILE_NOT_FOUND          = -31;

    
    protected $_noext = false;
    
    public $code = 0;
    
    /**
     * Конструктор
     *          
     * @param int|string $error код ошибки
     * @param string $param параметр для подстановки в текстовое сообщение
     * @param string $field поле формы, к которому относится ошибка  
     * @param bool $noext не формировать расширенное сообщение об ошибке     
     * @return void             
     */    
    public function __construct($error, $param = false, $noext = false)
    {        
        $this->_noext = $noext;
    
        $message = '';
         
        if (is_int($error)) {
            $this->code = $error;
            $message = $this->getMessageByCode($error);
            if ($param) $message = sprintf($message, $param);
        } else {
            $message = $error;
        }
        
        parent::__construct($message);
    }
	
    /**
     * Формирует расширенное сообщение об ошибке
     *            
     * @return string            
     */  
	public function getExtMessage()
	{
	    if ($this->_noext) return false;
        $str = 'In file <b>'.$this->getFile().'</b> on line: '.$this->getLine()."<br /><br /><b>Stack trace:</b><br />".nl2br($this->getTraceAsString());
        return $str;
    }
    
    /**
     * Выдает сообщение об ошибке по коду
     *
     * @param int $code код ошибки                  
     * @return string            
     */ 
    protected function getMessageByCode($code)
    {
        $t = \Cetera\Application::getInstance()->getTranslator();
        switch ($code) {
            case self::SERVER_LIMIT:            return $t->_('Исчерпан лимит на количество серверов в системе');
            case self::INVALID_PARAMS:          return $t->_('Неверный параметр');
            case self::CAT_EXISTS:              return $t->_('Раздел уже существует "%s"');
            case self::CAT_PHYSICAL_EXISTS:     return $t->_('Существует файл или каталог с таким именем');
            case self::MATERIAL_NOT_FOUND:      return $t->_('Материал не найден');
            case self::USER_NOT_FOUND:          return $t->_('Пользователь не найден');
            case self::INVALID_PASSWORD:        return $t->_('Неправильный пароль');
            case self::AUTHORIZATION_FAILED:    return $t->_('Вы не опознаны');
            case self::INVALID_EMAIL:           return $t->_('Неправильный E-mail');
            case self::SQL:                     return $t->_('Ошибка SQL: %s');
            case self::USER_EXISTS:             return $t->_('Такой пользователь уже существует');
            case self::PASSWORDS_DOESNT_MATCH:  return $t->_('-');
            case self::SERVER_NOT_FOUND:        return $t->_('-');
            case self::TYPE_EXISTS:             return $t->_('-');
            case self::EMAIL_EXISTS:            return $t->_('Такой E-mail уже существует');
            case self::CANT_CREATE:             return $t->_('Невозможно содать тему обсуждения');
            case self::MESSAGE_EXISTS:          return $t->_('Сообщение уже существует');
            case self::NO_RIGHTS:               return $t->_('Недостаточно полномочий для совершения этого действия');
            case self::TYPE_RESERVED:           return $t->_('-');
            case self::TYPE_FIXED:              return $t->_('-');
            case self::FIELD_EXISTS:            return $t->_('Поле c таким Alias уже существует');
            case self::FIELD_NOT_FOUND:         return $t->_('Поле "%s" обязательно для заполнения');
            case self::CAT_NOT_FOUND:           return $t->_('Раздел не найден');
            case self::ALIAS_EXISTS:            return $t->_('Материал с таким alias уже существует');
            case self::ADD_FIELD:               return $t->_('-');
            case self::EDIT_FIELD:              return $t->_('-');
            case self::ENUM_FIELD:              return $t->_('-');
            case self::FIELD_REQUIRED:          return $t->_('-');
            case self::CHOOSE_CATALOG:          return $t->_('-');
            case self::FILE_NOT_FOUND:          return $t->_('Файл не найден: %s');
            case self::MATERIALS_LIMIT:         return $t->_('Вы превысили лимит на количество материалов');
            case self::UNKNOWN:
            default:                            return $t->_('Неизвестная ошибка (%s)');
        }
    }
}