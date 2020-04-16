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
    protected $hidden = false;		
	
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
        if ($this->catalog->prototype->nodeId) {
            $this->query->andWhere('c.id=:id')->setParameter('id', $this->catalog->prototype->nodeId);
        }
        else {
            $this->query->andWhere('c.data_id=:idcat')->setParameter('idcat', $this->catalog->prototype->id);
        }        
		
    } 
	
    /**
     * Включать скрытые разделы 
     *         
     * @return Cetera\Iterator\Catalog\Children
     */	
    public function hidden($hidden = true)
    {
         $this->hidden = $hidden;         
         return $this;
    }	

    protected function fixQuery($query)
    {   
		$query->addOrderBy('main.tag', 'ASC');
		parent::fixQuery($query);
    }

	protected function fixWhere($query)
	{
        if (!$this->hidden) {
			$query->andWhere('main.hidden=0');      
		}
    }	
	
}