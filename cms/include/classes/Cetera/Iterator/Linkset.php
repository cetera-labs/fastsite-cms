<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera\Iterator;
 
/**
 * Итератор объектов, на которые ссылается поле 
 *
 * @package CeteraCMS
 **/
class Linkset extends DynamicObject {
	
	
    /**
     * Конструктор              
     *  
     * @param Object $object               
     * @return void  
     */ 
    public function __construct($object, $field)
    {

		if ($field['type'] != FIELD_LINKSET && $field['type'] != FIELD_MATSET) 
			throw new \Cetera\Exception\CMS('Illegal type of field '.$field['name'].' - '.$field['type']);

        if ($field['type'] == FIELD_LINKSET) {
        
			if ($field['len'] == CATALOG_VIRTUAL_USERS) {
			    $od = User::getObjectDefinition();
			} elseif ($field['pseudo_type'] == PSEUDO_FIELD_CATOLOGS) {
			    $od = \Cetera\Catalog::getObjectDefinition();
			} elseif (!$field['len']) {
				$od = $object->getObjectDefinition();
			} else {
			    $c = \Cetera\Catalog::getById($field['len']);
			    if (!$c) throw new \CeteraException\CMS('Catalog '.$field['len'].' is not found.');
			    $od = $c->materialsObjectDefinition;
			} 
          
        } 
		else {            
            $od = \Cetera\ObjectDefinition::findById($field['len']);
        }		
		
		parent::__construct( $od );  

		$linktable = $object->table.'_'.$od->table.'_'.$field['name'];
		
		$this->query->innerJoin('main', $linktable, 'b', 'main.id = b.dest and b.id='.$object->id);		
        
    } 	

}