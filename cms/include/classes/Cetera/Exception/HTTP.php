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
 * Класс для генерации HTTP ошибок
 *
 * @package FastsiteCMS
 */ 
class HTTP extends CMS
{
    /**
     * Код ошибки
     * @var int
     */
    private $status = 500;
    
    /**
     * расширенное сообщение об ошибке
     * @var string
     */              
    private $msg = false;

    /**
     * Конструктор
     *
     * @param int $status HTTP status code
     * @param string $msg расширенное сообщение об ошибке
     * @return void
     */
    public function __construct($status, $msg)
    {
        $this->status = $status;
        $this->msg = $msg;
        parent::__construct('Error :: '.$status);
    }
    
    /**
     * HTTP status code
     *            
     * @return int           
     */  
	public function getStatus()
	{
        return $this->status;
    }
    
    /**
     * Формирует расширенное сообщение об ошибке
     *            
     * @return string            
     */  
	public function getExtMessage()
	{
        return $this->msg;
    }
}