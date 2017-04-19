<?php
namespace Cetera\Widget; 

/**
 * Виджет "Пользовательское меню"
 * 
 * @package CeteraCMS
 */ 
class MenuUser extends Templateable {  

	public static $name = 'MenuUser';
 
    protected $_params = array(
        'name'  => '',
        'menu'  => 0,
		'alias' => null,
		'css_class' => 'menu',
		'template'  => 'default.twig',
    ); 
  
    private $_menu = false;
    
    public function getMenu()
    {
        if (!$this->_menu)
		{
			if ($this->getParam('menu'))
				$this->_menu = \Cetera\Menu::getById($this->getParam('menu'));
				elseif ($this->getParam('alias'))
					$this->_menu = \Cetera\Menu::getByAlias($this->getParam('alias'));
		}
        return $this->_menu;
    }
        
    public function getChildren()
    {
		$menu = $this->getMenu();
		if ($menu) return $this->getMenu()->children;
		return array();
    }     
}