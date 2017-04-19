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
class Children extends Catalog {
		
    /**
     * Включать скрытые разделы 
     *         
     * @var boolean    
     */   	
    protected $hidden = true;		
	
    /**
     * Конструктор              
     *  
     * @param Object $object               
     * @return void  
     */ 
    public function __construct($parent = null)
    {

		parent::__construct( $parent );  
		
		$this->query->innerJoin('b', 'dir_structure', 'c', 'b.lft BETWEEN c.lft and c.rght and b.level=c.level+1');
		$this->query->andWhere('c.data_id=:idcat')->setParameter('idcat', $this->catalog->prototype->id);
		
    } 

    protected function fixQuery($query)
    {   
		$query->addOrderBy('main.tag', 'ASC');
		parent::fixQuery($query);
    }		
	
}