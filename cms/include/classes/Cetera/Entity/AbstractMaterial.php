<?php
namespace Cetera\Entity;

abstract class AbstractMaterial extends AbstractEntity {

    protected $tag;
    protected $idcat;
    protected $dat;
    protected $datUpdate;
    protected $name;
    protected $type;
    protected $autor;
    protected $alias;
	protected $section;
	
    public function getUrl() {
        if ($this->idcat < 0) return false;     
        $url = '/'.$this->alias;             
        if ( $this->section->isServer() ) return $url;                   
        return rtrim($this->section->getUrl(),'/').$url;
    } 	
    
}