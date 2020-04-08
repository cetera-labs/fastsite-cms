<?php
namespace Cetera\Entity;

abstract class AbstractSection extends AbstractEntity {

    /**
     * Привязка раздела к структуре
     */
    protected $nodes;    
    
    protected $tag;
    protected $name;
    protected $tablename;
    protected $type;
    protected $template;
    protected $templatedir;
    protected $inheritfields;
    protected $typ;
    protected $dat;
    protected $hidden;
    protected $isServer;
	protected $preview;
    
    public function __construct() {
        $this->nodes = new ArrayCollection();
    }
	
	public function getUrl() {
		if (!count($this->nodes)) {
			return false;
		}
		return $this->nodes[0]->getUrl();
	}
	
    public function isServer() {
        return $this->isServer;
    }	
    
}