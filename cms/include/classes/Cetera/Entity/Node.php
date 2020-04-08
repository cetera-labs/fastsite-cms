<?php
namespace Cetera\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Cetera\Application;

/**
 * @Gedmo\Tree(type="nested")
 */
class Node extends AbstractEntity {

    /**
     * @Gedmo\TreeLeft
     */
    protected $lft;

    /**
     * @Gedmo\TreeRight
     */
    protected $rght;

    /**
     * @Gedmo\TreeLevel
     */
    protected $level;

    /**
     * @Gedmo\TreeParent
     */
    protected $parent;

    protected $children;
        
    protected $section;
	
    protected $url = false;
	protected $fullUrl = false;
	protected $treePath = false;	
    
    public function setParent(Node $parent = null)
    {
        $this->parent = $parent;        
    }
    
    public function setSection(Section $section)
    {
        $this->section = $section;        
    } 

    public function getUrl() {
		if ($this->url === false) $this->fillPath();
        return $this->url;        
    } 
	
    public function isRoot() {
        return $this->level <= 1;
    }	

    private function fillPath() {
        $this->url = '';
        $this->fullUrl = '';
        $this->treePath = '';

		$repo = Application::getInstance()->getEntityManager()->getRepository( get_class($this) );
		
		foreach ($repo->getPath($this) as $item) { 	  
			$this->treePath .= '/item-'.$item->section->id.'-'.$item->id;
			if ($item->isRoot()) continue;

			$this->fullUrl .= '/'.$item->section->alias;
			if ($item->section->isServer()) continue;

			$this->url .= '/'.$item->alias;
		}
		$this->url      = $this->url.'/';
		$this->fullUrl  = $this->fullUrl.'/';
		$this->treePath = '/root'.$this->treePath;
		if ($this->url == '') $this->url = '/';
    }	
          
}