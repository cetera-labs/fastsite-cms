<?php
namespace Cetera\Entity;

abstract class AbstractSection extends AbstractEntity {

    /**
     * Привязка раздела к структуре
     */
    public $nodes;    
    
    public $tag;
    public $name;
    public $tablename;
    public $type;
    public $template;
    public $templatedir;
    public $inheritfields;
    public $typ;
    public $dat;
    public $hidden;
    public $isServer;
	public $preview;
    
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

    public function getAlias() {
        return $this->tablename;
    }	
    
}