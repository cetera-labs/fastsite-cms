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
 * Итератор объектов, на которые ссылается поле 
 *
 * @package FastsiteCMS
 **/
class Linkset2 extends Base {
	
	use \Cetera\DbConnection;
	
    /**
     * Конструктор              
     *  
     * @param Object $object               
     * @return void  
     */ 
    public function __construct($material, $field)
    {

		if ($field['type'] != FIELD_LINKSET2) 
			throw new \Cetera\Exception\CMS('Illegal type of field '.$field['name'].' - '.$field['type']);
	
		
        $res = $this->getDbConnection()->fetchAll('SELECT * FROM '.$material->objectDefinition->table.'_'.$field->name.' WHERE id=? ORDER BY tag',[$material->id]);
		
		foreach ($res as $r) {
            try {
                $this->elements[] = \Cetera\Material::getById($r['dest_id'], $r['dest_type']);
            }
            catch (\Exception $e) {}
		}
    } 	
	
    /**
     * Добавляет произвольный материал в итератор
     *  
     * @param \Cetera\DynamicFieldsObject $material
     * @return void  
     */ 	
	public function add($material, $check = true) {
		if ($material->objectDefinition->id != $this->objectDefinition->id) {
			throw new \Exception('Illegal type of material '.$material->objectDefinition->id.'. Must be '.$this->objectDefinition->id);
		}
		return parent::add($material, $check);
	}	

}