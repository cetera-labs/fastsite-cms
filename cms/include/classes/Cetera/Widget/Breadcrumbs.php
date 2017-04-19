<?php
namespace Cetera\Widget; 

/**
 * Виджет "Хлебные крошки"
 * 
 * @package CeteraCMS
 */ 
class Breadcrumbs extends Templateable {
	
	use Traits\Catalog;
	
	public static $name = 'Breadcrumbs';
	
	public $items = array();
          	
	protected function initParams()
	{
		$this->_params = array(
			'catalog' => 0,
			'root'    => $this->t->_('Главная'),
			'template'=> 'default.twig',
			'add'   => false
		);  		
	}	

	protected function init()
	{
		foreach ($this->getCatalog()->getPath() as $c) {
			if (!$c->id) continue;
			$this->items[] = array(
				'url'  => $c->url,
				'name' => (!$c->isServer() || !$this->getParam('root'))?$c->name:$this->getParam('root')
			);
		}
		
		if (is_array($this->getParam('add'))) {
			$this->items = array_merge($this->items, $this->getParam('add'));
		}
	}
      
}