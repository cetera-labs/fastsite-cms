<?php
namespace Cetera\Widget; 

/**
 * Виджет "Меню"
 * 
 * @package CeteraCMS
 */ 
class Menu extends Templateable {
	
	use Traits\Catalog;
          
	public static $name = 'Menu';		  
		  
    public $level = 1;
	protected $stack = array();

    protected $_params = array(
        'name'                 => '',
		'menu'  			   => 0,
		'menu_alias'		   => 0,
        'catalog'              => 0,
        'depth'                => 1,
		'css_class'            => 'menu',
		'css_class_submenu'    => false,
		'expand_active'        => false,
		'materials_show'       => false,
		'materials_hide_index' => true,
		'template'             => 'default.twig',
    );  
	
    private $_menu = null;
    
    public function getMenu()
    {
        if ($this->_menu === null)
		{
			if ($this->getParam('menu'))
			{
				if (is_array($this->getParam('menu')))
				{
					$this->_menu = $this->getParam('menu');
				}
				else 
				{
					$this->_menu = \Cetera\Menu::getById($this->getParam('menu'))->getChildren();
				}
			} 
			elseif ($this->getParam('menu_alias'))
				$this->_menu = \Cetera\Menu::getByAlias($this->getParam('alias'))->getChildren();
				else $this->_menu = false;
		}
        return $this->_menu;
    }	
        
    public function getChildren()
    {		
		try {
			if ($this->getMenu()) 
				return $this->getMenu();		
			
			if ($this->getCatalog()) 
				return $this->getCatalog()->children->where('hidden<>1');
			
			return [];
		}
		catch (\Exception $e) {
			return [];
		}		
    }  

    public function setCatalog($c)
    {
		if ($c instanceof \Cetera\Catalog)
		{
			$this->_cat = $c;
		} 
		elseif ($c)
		{
			$this->_cat = \Cetera\Catalog::getById($c);
		}
		return $this;
    }	
	
    public function hasSubmenu()
	{
		if ($this->getParam('depth') && ( $this->level > $this->getParam('depth') )) return false;
		if ($this->getParam('expand_active') && !$this->application->getCatalog()->getPath()->has( $this->getCatalog() )) return false;
		return true;
	}	
	
	public function showSubmenu($c)
	{		
		array_push( $this->stack, $this->getCatalog() );
		array_push( $this->stack, $this->getParam('css_class') );
		$this->setCatalog($c);
		$this->level++;
		$html = null;
		if ($this->hasSubmenu()) {
			if ($this->getParam('css_class_submenu') === false) {
				$this->setParam('css_class_submenu', 'nested '.$this->getParam('css_class') );
			}
			$this->setParam('css_class', $this->getParam('css_class_submenu') );
			$html = $this->getHtml();
		}
		$this->level--;
		$this->setParam('css_class', array_pop($this->stack) );
		$this->setCatalog( array_pop($this->stack) );
		return $html;
	}
	
	public function getMaterials()
	{
		if (!$this->getParam('materials_show')) return [];
		$list = $this->getCatalog()->getMaterials()->orderBy('tag', 'ASC');		
		if ($this->getParam('materials_hide_index')) $list->where('alias <> "index"');
		return $list;
	}
      
}