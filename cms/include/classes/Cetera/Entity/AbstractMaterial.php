<?php
namespace Cetera\Entity;

abstract class AbstractMaterial extends AbstractEntity {

    public $tag;
    public $idcat;
    public $dat;
    public $datUpdate;
    public $name;
    public $type;
    public $autor;
    public $alias;
	public $section;
    
    public function __construct() {
        $this->tag = 0;
        $this->alias = 'empty';
    }    
	
    public function getUrl() {
        if ($this->idcat < 0) return false;     
        $url = '/'.$this->alias;             
        if ( $this->section->isServer() ) return $url;                   
        return rtrim($this->section->getUrl(),'/').$url;
    } 	
    
}