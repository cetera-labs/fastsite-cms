<?php
namespace Cetera\Widget; 

/**
 * Виджет "Постраничная навигация"
 * 
 * @package CeteraCMS
 */ 
class Paginator extends Templateable {
	
	public static $name = 'Paginator';
		 
    protected $_params = array(
        'iterator' => null,
		'url'      => '?page={page}',
		'template' => 'default.twig',
    );  
	
    public function getIterator()
    {
		return $this->getParam('iterator');
	}		
    
    public function hasPages()
    {	
		$iterator = $this->getIterator();
		if (!($iterator instanceof \Cetera\Iterator\Object)) return false;
		if ($iterator->getPageCount() <= 1) return false;
		
		return true;
    } 
	
	public function getUrl( $page = 1 )
	{
		return str_replace('{page}', $page, $this->getParam('url'));
	}
	
	public function getPreviousPage()
	{
		$p = $this->getIterator()->getCurrentPageNumber()-1;
		if ($p > $this->getIterator()->getPageCount()) $p = $this->getIterator()->getPageCount();
		return $p;
	}
      
}