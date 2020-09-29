<?php
namespace Cetera\Widget; 

/**
 * Виджет "Пользовательское меню"
 * 
 * @package FastsiteCMS
 */ 
class MenuUser extends Templateable {  

	public static $name = 'MenuUser';
 
    protected $_params = array(
        'name'  => '',
        'menu'  => 0,
		'alias' => null,
		'depth' => 1,
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
		try {
			$menu = $this->getMenu();
			if ($menu) return $this->getMenu()->children;
			return [];
		}
		catch (\Exception $e) {
			return [];
		}		
    }  

}