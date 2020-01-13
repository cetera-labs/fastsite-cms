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
    
    public function __construct() {
        $this->nodes = new ArrayCollection();
    }
    
}