<?php
namespace Cetera\Entity;

use Gedmo\Mapping\Annotation as Gedmo;

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
    
    public function setParent(Node $parent = null)
    {
        $this->parent = $parent;        
    }
    
    public function setSection(Section $section)
    {
        $this->section = $section;        
    }    
          
}