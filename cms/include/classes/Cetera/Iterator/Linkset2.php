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
                $m = \Cetera\Material::getById($r['dest_id'], $r['dest_type']);
                if ($m->published) $this->elements[] = $m;
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
		return parent::add($material, $check);
	}

    /**
     * Удаляет материал из итератора
     *  
     * @param \Cetera\DynamicFieldsObject $material
     * @return void  
     */ 	
	public function remove(\Cetera\DynamicFieldsObject $material) {
		foreach ($this->elements as $key => $value) {
			if ($value->id == $material->id && $value->objectDefinition->id == $material->objectDefinition->id) {
				unset($this->elements[$key]);
				$this->elements = array_values($this->elements);
				break;
			}
		}
		return $this;
	}	

}