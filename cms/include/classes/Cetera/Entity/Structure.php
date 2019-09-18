<?php
namespace Cetera\Entity;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\Tree(type="nested")
 */
class Structure extends AbstractEntity {

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
    
    public function setParent(Structure $parent = null)
    {
        $this->parent = $parent;        
    }
    
    public function setSection(Section $section)
    {
        $this->section = $section;        
    }    
          
}