<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera\Iterator;
 
/**
 * Итератор объектов, которые содержат ссылку на данный объект в определенном поле 
 *
 * @package FastsiteCMS
 **/
class LinksetReverse extends DynamicObject {
	
	
    /**
     * Конструктор              
     *  
     * @param Object $object               
     * @return void  
     */ 
    public function __construct($object, $field)
    {

		if ($field['type'] != FIELD_MATSET && $field['type'] != FIELD_LINKSET && $field['type'] != FIELD_LINK && $field['type'] != FIELD_MATERIAL) 
			throw new \Cetera\Exception\CMS('Illegal type of field '.$field['name'].' - '.$field['type']);

		parent::__construct( $field->getParentObjectDefinition() );
				
		if ($field instanceof \Cetera\ObjectFieldLinkSetAbstract) {
			$this->query->innerJoin('main', $field->getLinkTable() , 'b', 'main.id = b.id and b.dest='.(int)$object->id);
		}
		else {
			$this->where($field['name'].'='.(int)$object->id);
		}
        
    } 	

}