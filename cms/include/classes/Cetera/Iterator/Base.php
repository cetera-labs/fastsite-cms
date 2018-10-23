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
class Base implements \Countable, \Iterator, \ArrayAccess {

    /** Текущая позиция */
    protected $position = 0;
              
    /** Кэш */
    protected $elements = [];
    
    protected $pageNumber = 1; 
    protected $offset = 0; 
    
    protected $itemCountPerPage = null;	
	
	protected $dontUsePaging = false;

    /**
     * Конструктор              
     *  
     * @param array $array массив элементов              
     * @return void  
     */ 
    public function __construct($array = null)
    {
        if ($array) $this->elements = $array;
    }
    
    public function __toString()
    {
        $data = array();
        foreach ($this as $id => $item) {
            $data[] = array(
                'id'   => $item->id,
                'name' => $item->name
            );            
        }
        return json_encode($data);
    } 

    public function getElements()
    {
		return $this->elements;
	}		
	
    /**
     * Массив идентификаторов объектов             
     *            
     * @return array 
     */ 	
    public function idArray() {
        $data = [];
        foreach ($this->getElements() as $item) $data[] = $item->id;       
        return $data;
    }
	
    /**
     * Возвращает итератор в виде массива объектов с указанными полями
     *            
     * @return array 
     */ 	
    public function asArray()
    {
		if (func_num_args() == 0) {
			$fields = array('id');
		}
		else {
			$fields = array();
			$args = func_get_args();
			if (count($args)==1 && is_array($args[0])) {
				$args = $args[0];
			}			
			foreach ($args as $f ) {
				if (is_string($f)) $fields[] = $f;
			}
		}
		
        $data = array();
        foreach ($this as $item) {
			if (method_exists ( $item , 'asArray' )) {
				$data[] = $item->asArray($fields);   
			}
			else {
				$obj = array();
				foreach ($fields as $f) {
					$obj[$f] = $item->$f;
				}
				$data[] = $obj;
			}
		}
        return $data;
    }		
    
    public function findIndexById( $id )
    {
        foreach ($this->getElements() as $i => $item) if ($item->id == $id) return $i;
        return -1;  
    }     

    public function findById( $id )
    {
        $idx = static::findIndexById( $id );
		if ($idx < 0) return null;
		return $this->getElements()[$idx];
    }  	
	
    /**
     * Количество объектов в итераторе              
     *             
     * @return int  
     */ 
    public function count()
    {
        return count($this->getElements());
    }
	
    /**
     * Полное количество объектов              
     *             
     * @return int  
     */     
    public function getCountAll()
    {
		return count($this->getElements());
    } 	

    /**
     * Отмотать итератор к первому элементу              
     *             
     * @return void  
     */ 
    public function rewind()
    {
        $this->position = 0;
    }
	
	protected function getPosition($pos = false)
	{
		if ($pos === false) $pos = $this->position;
		
		if ($this->dontUsePaging)
		{
			return $pos;
		}	
		$realpos = $pos + $this->offset;
		if ($this->itemCountPerPage)
		{
			$realpos += ($this->pageNumber - 1)*$this->itemCountPerPage;
		}
		return $realpos;		
	}
    
    /**
     * Возвращает текущий элемент              
     *             
     * @return FSObject  
     */
    public function current()
    {
		return $this->getElements()[$this->getPosition()];
    }

    /**
     * Возвращает текущую позицию              
     *             
     * @return int  
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Переход к следующему элементу              
     *             
     * @return void  
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * Текущий элемент существует?              
     *             
     * @return bool 
     */
    public function valid()
    {
		if ($this->dontUsePaging || !$this->itemCountPerPage)
		{		
			return $this->position >= 0 && $this->position < count($this->getElements());
		}
		if ($this->position < 0) return false;
		if ($this->itemCountPerPage && $this->position >= $this->itemCountPerPage) return false;
		if ($this->getPosition() >= count($this->getElements())) return false;
		return true;
    }
    
    /**
     * Добавляет элемент              
     *    
     * @param $elm FSObject добавляемый раздел              
     * @return Iterator\Object
     */
    public function append($obj, $check = true)
    {
        if ( $check && $this->findIndexById( $obj->id ) >= 0) return $this;
        $this->elements[] = $obj;
        return $this;
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
    
    public function setItemCountPerPage($itemCountPerPage = null)
    {
        $this->itemCountPerPage = $itemCountPerPage;
        return $this;
    }
    
    public function setCurrentPageNumber( $pageNumber )
    {
        $pageNumber = (int)$pageNumber;
        if ($pageNumber < 1) $pageNumber = 1;
        $this->pageNumber = $pageNumber;  
        return $this;
    }
    
    public function setOffset( $offset )
    {
        $offset = (int)$offset;
        if ($offset < 0) $offset = 0;
        $this->offset = $offset;  
        return $this;
    }    
    
    public function getPageCount()
    {
        $total = $this->getCountAll();

        if (!$total) return 0;
        
        if (!$this->itemCountPerPage) return 1;
        
        return ceil($total / $this->itemCountPerPage);
    }   
    
    public function getCurrentPageNumber()
    {
        return $this->pageNumber;
    }	
	
    /**
     * Сортирует в обратном порядке             
     *               
     * @return Iterator\Object
     * @todo     
     */
    public function reverse()
    {
        return $this;
    }
    
    /**
     * Нельзя изменять содержимое            
     *               
     * @return void   
     */
    public function offsetSet($offset, $value) {
        $this->elements[$this->getPosition($offset)] = $value;
    }
    
    /**
     * Существует ли элемент на данной позиции            
     *  
     * @param int $offser позиция                  
     * @return bool  
     */
    public function offsetExists($offset) {
        return isset($this->elements[$this->getPosition($offset)]);
    }
    
    /**
     * Нельзя изменять содержимое            
     *               
     * @return void   
     */
    public function offsetUnset($offset) {
        unset( $this->elements[$this->getPosition($offset)] );
    }
    
    /**
     * Получить элемент на данной позиции            
     *  
     * @param int $offser позиция                  
     * @return FSObject   
     */
    public function offsetGet($offset) {
        return isset($this->elements[$this->getPosition($offset)]) ? $this->elements[$this->getPosition($offset)] : null;
    }
    
    /**
     * Выстраивает элементы в строку              
     *             
     * @param mixed $element свойство объекта, которое использовать для формирования строки или функция, которая возвращает строку
     * @param string $filter функция фильтрации элементов, должна возвращать false, если элемент следует пропустить
     * @return string 
     * @todo когда будет PHP5.3, заменить $c = new $class(); $c->$method(... на $class::$method(...  
     */ 
    public function implode($element = 'name', $filter = false)
    {
        $ret = '';
        
        $total = 0;
        $skip = array();
        foreach ($this as $id => $item) {

            $skip[$id] = 1;

            if (is_callable($filter))
			{
                if (substr_count($filter, '::')) {
                    list($class, $method) = explode('::', $filter);
                    $c = new $class($item);
                    if (!$c->$method($item)) continue;
                } else {
                    if (!$filter($item)) continue;
                }
            }
    
            $skip[$id] = 0;
            $total++;
        }
        
        $index = 0;
        foreach ($this as $id => $item) {

            if ($skip[$id]) continue;
            
            if (is_callable($element)) {
                if (is_string($element) && substr_count($element, '::')) {
                    list($class, $method) = explode('::', $element);
                    $c = new $class();
                    $string = $c->$method($item, $index, $index == 0, $index == $total-1, $total);
                } else { 
                    $string = $element($item, $index, $index == 0, $index == $total-1, $total);
                }
            } elseif (method_exists($item, $element)) {
                $string = $item->$element();
            } else {
                $string = $item->$element;
            }
            $index++;
            $ret .= $string;
        }
        return $ret;
    }
	
    /**
     * Создает новый фильтр для этого итератора
     *             
     * @param string $name имя фильтра
     * @return \Cetera\Filter
     */	
	public function createFilter($name) {
		return new \Cetera\Filter($name, $this);
	}

}
