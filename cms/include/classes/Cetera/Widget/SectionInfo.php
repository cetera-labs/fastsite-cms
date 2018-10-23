<?php
namespace Cetera\Widget; 

/**
 * Виджет "Раздел"
 * 
 * @package CeteraCMS
 */ 
class SectionInfo extends Templateable {
			
	use Traits\Catalog;
	use Traits\Meta;
	
	public static $name = 'Section.Info';
		
	protected function initParams()
	{
		$this->_params = array[
			'template'               => 'default.twig',
			'catalog'                => 0,
			'show_meta'              => true,
		];  		
	}		
	

	protected function init()
	{
		parent::init();
		
		$c = $this->getCatalog();
		if ($this->getParam('show_meta') && $c) {
			if ($c->meta_title) 
				$name = $c->meta_title;
				else $name = strip_tags($c->name);
				
			if ($c->meta_description) 
				$short = strip_tags($c->meta_description);
				else $short = strip_tags($c->short);
						
			$this->setMetaTitle($name);
			$this->setMetaDescription($short);
			$this->setMetaPicture($c->pic);
			
			$a = $this->application;
		
			if ($c->meta_keywords) {
				$a->setPageProperty('keywords', $c->meta_keywords);
			}
			
		}
	}	
      
}