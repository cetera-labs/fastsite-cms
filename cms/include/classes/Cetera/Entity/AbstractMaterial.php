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
	
    public function getUrl() {
        if ($this->idcat < 0) return false;     
        $url = '/'.$this->alias;             
        if ( $this->getSection()->isServer() ) return $url;                   
        return rtrim($this->getSection()->getUrl(),'/').$url;
    } 	
    
}