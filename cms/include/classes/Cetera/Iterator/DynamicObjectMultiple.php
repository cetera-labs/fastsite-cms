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
class DynamicObjectMultiple extends DbObject {
		
    /**
     * Типы материалов
     *         
     * @var ObjectDefinition    
     */     
    protected $objectDefinitionArray = array();
	
	protected $fields = array('id','name','type','idcat','tag','dat', 'alias');
	
	protected $where = '';
	protected $order = '';
    
    /**
     * Конструктор              
     *  
     * @param array of \Cetera\ObjectDefinition $array
     * @return void  
     */ 
    public function __construct($array, $fields = array())
    {
		foreach ($array as $od)
		{
			$this->objectDefinitionArray[$od->id] = $od;
		}
		$this->dontUsePaging = true; 
		$this->fields = array_merge($this->fields, $fields);
    } 
    	
	protected function fetchObject($row)
	{
		$tid = $row['_type_id_'];
		unset($row['_type_id_']);
		
		return \Cetera\DynamicFieldsObject::fetch($row, $this->objectDefinitionArray[ $tid ]);
	}
            
    public function where($where, $combination = 'AND')
    {
		if ($this->where)
		{
			$this->where .= ' '.$combination.' ('.$where.')';
		}
		else
		{
			$this->where .= ' WHERE ('.$where.')';
		}
		
        $this->sync = false;
        return $this;         
    }

    public function select($select = null)
    {
        if (!$select) return $this;
        $this->fields = array_merge($this->fields, $select);
        $this->sync = false;
        return $this;
    } 	

    protected function fetchElements()
    {
        if ($this->sync) return;
        $this->elements = array();
        
        $query = $this->getQuery();
                
        if ($this->offset)	
			$query .= ' OFFSET '.(int)$this->offset;
                     
        if ($this->itemCountPerPage)
			$query .= ' LIMIT '.( $this->offset + ($this->pageNumber - 1)*$this->itemCountPerPage ).', '.$this->itemCountPerPage;
                     
        $stmt = $this->getDbConnection()->query($query); 
        while ($row = $stmt->fetch())
		{    
            $this->append( $this->fetchObject($row) , false);
        }
        $this->sync = true;
    }
	
    public function getCountAll()
    {
        if ($this->sync && $this->countAll !== null) return $this->countAll;
        
        $query = $this->getQuery();
                            
        $stmt = $this->getDbConnection()->query($query);  
        if ($stmt->rowCount() > 1)
        {
            $this->countAll = $stmt->rowCount();
        } 
        else
        {
            $this->countAll = $stmt->fetchColumn();
        }
        
        return $this->countAll;  
    }	

    public function orderBy($sort, $order = null, $add = true)
    {
		if ($this->order && $add)
		{
			$this->order .= ', '.$sort;
		}
		else
		{
			$this->order = ' ORDER BY '.$sort;
		}
		if ($order) $this->order .= ' '.$order;
		
        $this->sync = false;
        return $this;       
    }  	
	
    public function getQuery()
    {   
		$q = array();
		foreach ($this->objectDefinitionArray as $od)
		{
			$q[] = 'SELECT '.$od->id.' as  _type_id_, '.implode(',', $this->fields).' FROM `'.$od->table.'`'.$this->where;
		}
		return implode(' UNION ', $q);
    } 	

}
