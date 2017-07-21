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
        $this->countAll = $stmt->rowCount();
        
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
			$from = '`'.$od->table.'` main';
			$group = '';
			$where = $this->where;
			$f = array();
			foreach (array_unique($this->fields) as $key => $field) {
				if ($field == 'id' || $od->hasField($field)) {
					$f[] = 'main.'.$field;
					$where = str_replace('`'.$field.'`', 'main.`'.$field.'`', $where);
				}
				else {
					try {
						$parts = explode('.', $field);
						if (count($parts) == 3) {
							$link_od = \Cetera\ObjectDefinition::findByTable( $parts[0] );
							if (!$link_od->hasField($parts[2]) || !$link_od->hasField($parts[1])){
								throw new \Exception('field doesnt exist');
							}
							$link = $link_od->getField($parts[1]);
							if ($link['len'] != $od->id) {
								throw new \Exception('field doesnt exist');
							}
							$from .= ' LEFT JOIN `'.$link_od->table.'` link'.$key.' ON (main.id=link'.$key.'.'.$parts[1].')';
							$f[] = 'link'.$key.'.`'.$parts[2].'` as `'.$field.'`';
							$where = str_replace('`'.$field.'`', 'link'.$key.'.`'.$parts[2].'`', $where);
						}
						elseif (count($parts) == 2) {
							$link = $od->getField($parts[0]);
							if (is_subclass_of($link, '\Cetera\ObjectFieldLinkSetAbstract')) {
								$from .= ' LEFT JOIN `'.$link->getLinkTable().'` mlink'.$key.' ON (main.id=mlink'.$key.'.id) LEFT JOIN `'.$link->getTable().'` link'.$key.' ON (mlink'.$key.'.dest=link'.$key.'.id)';
								$f[] = 'link'.$key.'.`'.$parts[1].'` as `'.$field.'`';
								$where = str_replace('`'.$field.'`', 'link'.$key.'.`'.$parts[1].'`', $where);
							}
							elseif (is_subclass_of($link, '\Cetera\ObjectFieldLinkAbstract')) {
								$from .= ' LEFT JOIN `'.$link->getTable().'` link'.$key.' ON (main.`'.$parts[0].'`=link'.$key.'.id)';
								$f[] = 'link'.$key.'.`'.$parts[1].'` as `'.$field.'`';
								$where = str_replace('`'.$field.'`', 'link'.$key.'.`'.$parts[1].'`', $where);
							}
							else {
								throw new \Exception('field doesnt exist');
							}
						}
						else {
							throw new \Exception('field doesnt exist');
						}
					}
					catch (\Exception $e) {
						$f[] = 'NULL as `'.$field.'`';
						$where = str_replace('`'.$field.'`', 'NULL', $where);
					}
				}
			}
			$q[] = 'SELECT '.$od->id.' as  _type_id_, '.implode(',', $f).' FROM '.$from.' '.$where.' GROUP BY main.id';
		}
		return implode(' UNION ', $q).$this->order;
    } 	

}
