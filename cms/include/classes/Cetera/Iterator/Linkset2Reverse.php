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
class Linkset2Reverse extends Base {
	
	use \Cetera\DbConnection;
	
    /**
     * Конструктор              
     *  
     * @param Object $object               
     * @return void  
     */ 
    public function __construct($object, $fieldName)
    {
		$types = $this->getDbConnection()->fetchAll('SELECT B.* FROM types_fields A LEFT JOIN types B ON (A.id=B.id) WHERE A.name=? and A.type=?',[$fieldName, FIELD_LINKSET2]);
        foreach ($types as $t) {		
            $res = $this->getDbConnection()->fetchAll('SELECT * FROM '.$t['alias'].'_'.$fieldName.' WHERE dest_id=? and dest_type=? ORDER BY tag',[$object->id, $object->objectDefinition->id]);
            foreach ($res as $r) {
                try {
                    $m = \Cetera\Material::getById($r['id'], $t['id']);
                    if ($m->published) $this->elements[] = $m;
                }
                catch (\Exception $e) {}
            }
        }
    } 	

}