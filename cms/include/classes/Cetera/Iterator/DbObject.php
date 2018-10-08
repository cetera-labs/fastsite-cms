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
abstract class DbObject extends Base {
	
	use \Cetera\DbConnection;
	   
    protected $query = null;
     
    protected $sync = false;
    
    protected $countAll = null;   
       
    /**
     * Конструктор              
     *  
     * @param \Cetera\ObjectDefinition $object               
     * @return void  
     */ 
    public function __construct()
    {
        
        $this->query = $this->getDbConnection()->createQueryBuilder();
		$this->dontUsePaging = true;
        
    }

    function __clone()
    {
        $this->query = clone $this->query;
    }	
    
    public function fetchElements()
    {
        if ($this->sync) return;
        $this->elements = array();
        
        $query = clone $this->query;
                
        if ($this->offset) 
              $query->setFirstResult( $this->offset ); 
                     
        if ($this->itemCountPerPage)
              $query->setMaxResults( $this->itemCountPerPage )
                    ->setFirstResult( $this->offset + ($this->pageNumber - 1)*$this->itemCountPerPage );
                    
        $this->fixQuery($query);
                      
        //print $query."\n\n";
                     
        $stmt = $query->execute(); 
        while ($row = $stmt->fetch()) {    
			//print $this->objectDefinition->table.'<br>';
			//print_r($row);
            $this->append( $this->fetchObject($row) , false);
        }
        $this->sync = true;
    }
	
    abstract protected function fetchObject($row);
    
    protected function fixQuery($query)
    {   
		$this->fixWhere($query);
    }	
	
    protected function fixWhere($query)
    {    
    }		
    
    public function getQuery()
    {       
        return $this->query;  
    } 

    /**
     * Порядковый номер первого элемента              
     *             
     * @return int  
     */	
    public function getFirstIndex()
    {	
		if (!$this->itemCountPerPage) return 1;
		return 1 + $this->itemCountPerPage * ($this->pageNumber - 1);
	}
	
    /**
     * Порядковый номер последнего элемента
     *             
     * @return int  
     */	
    public function getLastIndex()
    {	
		if (!$this->itemCountPerPage) return $this->getCountAll();
		$count = $this->itemCountPerPage + $this->itemCountPerPage * ($this->pageNumber - 1);
		if ($count > $this->getCountAll()) $count = $this->getCountAll();
		return $count;
	}	
	
    /**
     * Полное количество объектов              
     *             
     * @return int  
     */     
    public function getCountAll()
    {
        if ($this->sync && $this->countAll !== null) return $this->countAll;
        
        $query = clone $this->query;
        $query->resetQueryPart('orderBy')
              ->setMaxResults(null)
              ->setFirstResult(null)
              ->select('COUNT(1)');
			  
        $this->fixWhere($query);
                            
        $stmt = $query->execute();  
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
	
    public function idArray() {
        $this->fetchElements();
        return parent::idArray();  
    } 		
    
    public function findIndexById( $id )
    {
        $this->fetchElements();
        return parent::findIndexById( $id );  
    }     
	
    /**
     * Количество объектов в итераторе              
     *             
     * @return int  
     */ 
    public function count()
    {
        $this->fetchElements();
        return parent::count();
    }
    
    /**
     * Возвращает текущий элемент              
     *             
     * @return FSObject  
     */
    public function current()
    {
        $this->fetchElements();
        return parent::current();    
    }

    /**
     * Текущий элемент существует?              
     *             
     * @return bool 
     */
    public function valid()
    {
        $this->fetchElements();
        return parent::valid();    
    }
        
    /**
     * Существует ли элемент на данной позиции            
     *  
     * @param int $offser позиция                  
     * @return bool  
     */
    public function offsetExists($offset) {
        $this->fetchElements();
        return parent::offsetExists($offset);    
    }
        
    /**
     * Получить элемент на данной позиции            
     *  
     * @param int $offser позиция                  
     * @return FSObject   
     */
    public function offsetGet($offset) {
        $this->fetchElements();
        return parent::offsetGet($offset);  
    } 
	                
    public function setItemCountPerPage($itemCountPerPage = null)
    {
        $this->sync = false;
        return parent::setItemCountPerPage($itemCountPerPage);
    }
    
    public function setCurrentPageNumber( $pageNumber )
    {
        $this->sync = false;
        return parent::setCurrentPageNumber( $pageNumber );
    }
    
    public function setOffset( $offset )
    {
        $this->sync = false;
        return parent::setOffset( $offset );
    }            
      
    public function setParameter($key, $value, $type = null)
    {
        $this->query->setParameter($key, $value, $type);
        $this->sync = false;
        return $this;    
    }
    
    public function setParameters(array $params, array $types = array())
    {
        $this->query->setParameters($params, $types);
        $this->sync = false;
        return $this;      
    }
    
        
    public function select($select = null)
    {
        if (!$select) return $this;
        $select = is_array($select) ? $select : func_get_args();
        $this->query->select($select);
        $this->sync = false;
        return $this;
    }  
    
    public function where($where, $combination = 'AND')
    {   
		if (self::isSafe($where)) {
			if ($combination == 'OR') {
				$this->query->orWhere($where);
			}
			elseif ($combination == 'AND') {
				$this->query->andWhere($where);
			}
			else {
				$this->query->where($where);
			}
			$this->sync = false;
		}
        return $this;
    }   
    
    public function orderBy($order, $sort = null, $add = false)
    {
		if (self::isSafe($order) && self::isSafe($sort)) {
			$this->query->add('orderBy', $order . ' ' . (!$sort ? 'ASC' : $sort), $add);
			$this->sync = false;
		}
        return $this;        
    } 
    
    public function groupBy($groupBy, $add = true)
    {
        if (empty($groupBy)) {
            return $this;
        }

        $this->query->add('groupBy', $groupBy, $add);
        $this->sync = false;
        return $this; 
    } 
	
	private static function isSafe($sql)
	{
		return !preg_match("#(update|insert into|drop table|alter table|delete from|create table)+\s#is", $sql);
	}

}
