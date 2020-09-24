<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera\Exception;  
 
/**
 * @package FastsiteCMS
 */ 
class Form extends CMS
{
    /**
     * Поле формы, к которому относится ошибка
     *
     * @var string
     */
    public $field = false;

    /**
     * Конструктор
     *
     * @param int $code код ошибки
     * @param string $field поле формы, к которому относится ошибка
     * @param string $param параметр для подстановки в текстовое сообщение
     * @return void
     */
    public function __construct($code, $field, $param = false)
    {
        $this->field = $field;
        parent::__construct($code, $param, true);
    }
}