<?php
namespace Cetera\Widget; 

/**
 * Виджет "Раздел"
 * 
 * @package CeteraCMS
 */ 
class Section extends Templateable {
			
	use Traits\Catalog;
	use Traits\Meta;
	
	public static $name = 'Section';
	
	protected $_material = null;

	public $error404 = false;
	
	protected function initParams()
	{
		$this->_params = array(
			'template'               => 'default.twig',
			'catalog'                => 0,
			'show_meta'              => true,
			
			'display_index'          => true,
			
			'material_id'            => 0,
			'material_alias'         => null,
			'material_share_buttons' => false,
			'material_show_pic'      => false,
			'material_template'      => null,
			'material_unpublished'   => false,
			
			'list_limit'             => 10,
			'list_where'             => null,
			'list_page'              => null,	
			'list_page_param'        => 'page',	
			'list_order'             => 'dat',
			'list_sort'              => 'DESC',
			'list_paginator'         => true,	
			'list_template'          => null,
			'list_subsections'		 => false,
			'paginator_template'     => null,
			
			'page404_title'          => $this->t->_('Страница не найдена'),
			'page404_template'		 => null,
		);  		
	}		
	
	public function getMaterial()
	{
		if (!$this->_material)
		{
			try {
				$mid = (int)$this->getParam('material_id');
				$c = $this->getCatalog();
				
				if ($mid) {
					$this->_material = $c->getMaterialByID($id);
				}
				else {
					$alias = $this->getParam('material_alias');
					if (!$alias) {
						$alias = current(explode('/', $this->application->getUnparsedUrl() ));
						if (!$alias && $this->getParam('display_index'))	{
							$this->_material = $c->getMaterialByAlias('index');
						} 
						elseif ($alias)  {
							try {
								$this->_material = $c->getMaterialByAlias($alias, null, (boolean)$this->getParam('material_unpublished'));
							}
							catch (\Exception $e) {
								$this->error404 = true;
								$this->_material = null;
							}
						}
					}
					else {
						$this->_material = $c->getMaterialByAlias($alias, null, (boolean)$this->getParam('material_unpublished'));
					}
				}

			}
			catch (\Exception $e) {
				$this->_material = null;
			}
		}
		return $this->_material;
	}

	protected function _getHtml()
	{
		$m = $this->getMaterial();
		$c = $this->getCatalog();
		
		$a = $this->application;
			
		if ($this->error404)
		{
			header("HTTP/1.0 404 Not Found");
			$this->setMetaTitle($this->getParam('page404_title'));
		}
		elseif ($this->getParam('show_meta') && $c && !$m)
		{
			if ($c->meta_title) 
				$name = $c->meta_title;
				else $name = strip_tags($c->name);
				
			if ($c->meta_description) 
				$short = strip_tags($c->meta_description);
				else $short = strip_tags($c->short);

			$this->setMetaTitle($name);
			$this->setMetaDescription($short);
			$this->setMetaPicture($c->pic);						
		}
		
		return parent::_getHtml();
	}	
      
}