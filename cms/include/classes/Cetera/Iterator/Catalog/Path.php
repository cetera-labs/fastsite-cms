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
 * Итератор разделов от корня до заданного
 *
 * @package CeteraCMS
 **/
class Path extends Catalog implements \RecursiveIterator {
			
    /**
     * Конструктор              
     *  
     * @param Object $object               
     * @return void  
     */ 
    public function __construct( $parent = null )
    {
		parent::__construct( $parent );  		
		$this->query->innerJoin('b', 'dir_structure', 'c', 'b.lft <= c.lft and b.rght >= c.rght');
		$this->query->andWhere('c.data_id=:idcat')->setParameter('idcat', $this->catalog->id);
        $this->query->orderBy('b.lft', 'ASC');
    } 	
	
	/*
	 * @internal
	 */
    public function fetchElements()
    {
        parent::fetchElements();
		if ( $this->elements[0]->id > 0 ) array_unshift ( $this->elements , \Cetera\Catalog::getRoot() );
    }	
	
}