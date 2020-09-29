<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
namespace Cetera\Widget; 

/**
 * Виджет "Список материалов"
 * 
 * @package FastsiteCMS
 */ 
class Filter extends Templateable { 
	
	public static $name = 'Filter';
	
	protected $filter;
	 	
	protected function initParams()
	{
		$this->_params = array(
			'filter'    => false,
		    'action'    => null,
		    'css_class' => 'filter',			
			'template'  => 'default.twig',
		); 
		
	}

	protected function init()
	{
		$this->filter = $this->getParam('filter');
	}
	
	public function getFilter()
	{
		return $this->filter;
	}
    
}