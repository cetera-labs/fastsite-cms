<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera\Iterator\Catalog;
 
/**
 * Итератор разделов
 *
 * @package CeteraCMS
 **/
class Catalog extends \Cetera\Iterator\DynamicObject implements \RecursiveIterator {
	
    /**
     * Родительский раздел 
     *         
     * @var \Cetera\Catalog    
     */        
    protected $catalog = null;	
	
    /**
     * Конструктор              
     *  
     * @param Object $object               
     * @return void  
     */ 
    public function __construct($catalog = null)
    {

		if ( $catalog )
		{
			if ($catalog instanceof \Cetera\Catalog)
			{
				$this->catalog = $catalog; 			          				
			} 
			else
			{
				throw new \Cetera\Exception\CMS('В конструктор должен быть передан Catalog, либо ничего');				
			}
		}
		else
		{	
			$this->catalog = \Cetera\Catalog::getRoot();	
		}

		parent::__construct( \Cetera\Catalog::getObjectDefinition() );  

		$this->query->addSelect('b.level');
        $this->query->addSelect('b.id as node_id');
		$this->query->innerJoin('main', 'dir_structure', 'b', 'main.id = b.data_id');		
        
    } 	
	
         
    /**
     * Имеет ли текущий раздел дочерние разделы
     *     
     * @return bool           
     */  
    public function hasChildren()
    {
        if (!($this->current() instanceof \Cetera\Catalog)) return false;
        return count($this->getChildren()) > 0;
    }
    
    /**
     * Возвращает дочерние разделы текущего раздела
     *     
     * @return Catalog_Iterator            
     */ 
    public function getChildren()
    {
        return $this->current()->getChildren();
    }
    
    /**
     * Выстраивает элементы в строку              
     *             
     * @param mixed $element свойство раздела, которое использовать для формирования строки или функция, которая возвращает строку
     * @param string $glue 
     * @param bool $reverse обратный порядок
     * @param bool $root пропускать карневой раздел
     * @param bool $server пропускать сервера
     * @return string 
     */ 
    public function implode($element = '\\Cetera\\Iterator\\Catalog\\Catalog::SlashedNames', $filter = '\\Cetera\\Iterator\\Catalog\\Catalog::RootExcludeFilter')
    {
        return parent::implode($element, $filter);
    }
	
    
	/**
	 * Проверяет, имеется ли раздел в итераторе 	 
	 *        
	 * @param Catalog $catalog раздел для проверки	 
	 * @return bool
	 */ 
    public function has($catalog)
    {
		if ($catalog instanceof \Cetera\Catalog)
		{
			$cid = $catalog->id;
		}
		else
		{
			$cid = (int)$catalog;
		}
        foreach($this as $c) if ($c->id == $cid) return true;
        return false;
    }
    
	/**
	 * @internal
	 */
    public function __toString()
    {
        return $this->implode();
    }
	
    /**
     * Cтандартный фильтр для метода implode. Исключает корневой раздел.
     *     
     * @return string    
     * @see implode       
     */   
    public static function SlashedNames($catalog, $index, $first, $last, $total)
    {
        return (!$first?' / ':'').$catalog->name;
    }

    /**
     * Cтандартный фильтр для метода implode. Исключает корневой раздел.
     *     
     * @return bool    
     * @see implode       
     */         
    public static function RootExcludeFilter($catalog)
    {
        if (!($catalog instanceof \Cetera\Catalog)) return true;
        return !$catalog->isRoot();
    }	

}