<?php
namespace Cetera\Widget; 

/**
 * Виджет "Постраничная навигация"
 * 
 * @package CeteraCMS
 */ 
class Material extends Templateable {
		
	use Traits\Material;
	use Traits\Meta;
	
	public static $name = 'Material';
		
    protected $_params = array(
		'template'       => 'default.twig',
		'material'       => 0,
		'catalog'        => 0,
		'material_type'  => 0,
		'material_id'    => 0,
		'material_alias' => null,
		'share_buttons'  => false,
		'show_pic'       => false,
		'show_meta'      => false
    );
	
	protected function init()
	{
		parent::init();
		
		$m = $this->getMaterial();
		if ($this->getParam('show_meta') && $m)
		{
			if ($m->meta_title) 
				$name = $m->meta_title;
				else $name = strip_tags($m->name);
				
			if ($m->meta_description) 
				$short = strip_tags($m->meta_description);
				else $short = strip_tags($m->short);
						
			$this->setMetaTitle($name);
			$this->setMetaDescription($short);
			$this->setMetaPicture($m->pic);
			
			$a = $this->application;
			$a->addHeadString('<meta property="og:url" content="http://'.$_SERVER['SERVER_NAME'].$m->url.'"/>', 'og:url');	
			$a->addHeadString('<meta property="og:type" content="article"/>', 'og:type');
		
			if ($m->meta_keywords) 			
			{
				$a->setPageProperty('keywords', $m->meta_keywords);
			}
			
		}
	}
      
}