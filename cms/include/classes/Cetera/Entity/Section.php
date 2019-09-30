<?php
namespace Cetera\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class Section extends AbstractEntity {
    
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
    
    protected $pic;
    protected $metaTitle;
    protected $metaKeywords;
    protected $metaDescription;
    
    public function __construct() {
        $this->nodes = new ArrayCollection();
    }    
}