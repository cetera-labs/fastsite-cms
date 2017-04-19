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
 * @package CeteraCMS
 */ 
class SQL extends CMS
{
    /**
     * Запрос, в котором возникла ошибка
     *      
     * @var string     
     */         
    private $query = false;
    
    /**
     * Конструктор
     *   
          
     * @param string $query запрос, в котором возникла ошибка       
     * @return void             
     */ 
    public function __construct($query)
    {
        $this->query = $query;
        parent::__construct(CMS::SQL, mysql_error());
    }
    
    /**
     * Формирует расширенное сообщение об ошибке
     *            
     * @return string            
     */  
	public function getExtMessage()
	{
        $str = '<b>Query:</b><br />'.$this->query.'<br /><br />';
        return $str.parent::getExtMessage();
    }	
}
