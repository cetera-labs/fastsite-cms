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
 * Итератор - цепочка из итераторов
 *
 * @package CeteraCMS
 **/
class Base extends Base {

	protected $iterators = [];

    /**
     * Конструктор              
     *  
     * @param array $array массив элементов              
     * @return void  
     */ 
    public function __construct()
    {
        $numargs = func_num_args();
		$arg_list = func_get_args();
		if ($numargs == 1 && is_array($arg_list[0])) {
			$array = $arg_list[0];
		}
		else {
			$array = $arg_list;
		}
		foreach ($array as $i) {
			if ($i instanceof Base)) {
				$this->iterators[] = $;
			}
		}		
    }
    
    public function getElements()
    {
		$res = [];
		foreach ($this->iterators as $i) {
			$res += $i->getElements();
		}
		return $res;
	}
	
    public function append(Base $iterator)
    {
		$this->iterators[] = $iterator;
		return $this;
	}	
}
