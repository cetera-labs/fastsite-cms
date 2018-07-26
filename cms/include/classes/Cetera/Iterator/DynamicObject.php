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
 * Итератор объектов
 *
 * @package CeteraCMS
 **/
class DynamicObject extends DbObject {
		
    /**
     * Тип материалов
     *         
     * @var ObjectDefinition    
     */     
    protected $objectDefinition = null;
    
    protected $table = null;
        
    protected $joinedFields = array();	

    /**
     * Конструктор              
     *  
     * @param \Cetera\ObjectDefinition $object               
     * @return void  
     */ 
    public function __construct($object)
    {
        if ($object instanceof \Cetera\ObjectDefinition) {
        
            $this->objectDefinition = $object;
            
        } else {
        
            throw new \Cetera\Exception\CMS('В конструктор должен быть передан ObjectDefinition');
            
        }
        
        $this->table = $this->objectDefinition->table;
        
        parent::__construct();
		
        $this->query->select('main.*')->from($this->table, 'main');
        
    } 
    	
	protected function fetchObject($row)
	{
		return \Cetera\DynamicFieldsObject::fetch($row, $this->objectDefinition);
	}
    
    protected function fixQuery($query)
    { 
		$query->addSelect('main.id', 'main.name');
		parent::fixQuery($query);
    }	
    
    public function join($fieldName, $direct = 0)
    {
        if (in_array($fieldName, $this->joinedFields)) return $this;
        
        $field = $this->objectDefinition->getField($fieldName); 
        
        if ($field instanceof \Cetera\ObjectFieldLinkSetAbstract) {
			if ($direct) {
				$link = $field->name.'_link';
			}
			else {
				$link = $field->name;
			}
            $this->query->leftJoin('main', $field->getLinkTable(), $link, 'main.id = '.$link.'.id');
            $this->joinedFields[] = $fieldName;
			if ($direct) {
				$this->query->leftJoin($link, $field->getObjectDefinition()->table, $field->name, $link.'.dest = '.$field->name.'.id');
			}
        }

        return $this;
    }
	
	public function filterInclude($fieldName, $condition, $combination = 'AND') {
		
		$field = $this->objectDefinition->getField($fieldName); 		
		return parent::where('main.id IN (SELECT l.id FROM '.$field->getLinkTable().' l LEFT JOIN '.$field->getObjectDefinition()->table.' t ON (l.dest=t.id) WHERE '.$condition.')', $combination);		
		
	}
	
	public function filterExclude($fieldName, $condition) {
		
		$field = $this->objectDefinition->getField($fieldName); 		
		return parent::where('main.id NOT IN (SELECT l.id FROM '.$field->getLinkTable().' l LEFT JOIN '.$field->getObjectDefinition()->table.' t ON (l.dest=t.id) WHERE '.$condition.')', $combination);		
		
	}	
            
    public function where($where, $combination = 'AND')
    {
        preg_match_all("|`(\w+)`|U", $where, $matches);
        if ($matches[1]) {
            $fields = array_unique($matches[1]);
            foreach ($fields as $fieldName) {
                $field = $this->objectDefinition->getField($fieldName);	                 
                if ($field instanceof \Cetera\ObjectFieldLinkSetAbstract) {					
                    $this->join($fieldName);
                    $where = str_replace("`$fieldName`", "`$fieldName`.dest", $where); 
                } 
            }
        }
		
		return parent::where($where, $combination);    
    } 

    public function getObjectDefinition()
    {
		return $this->objectDefinition;
	}		

}
