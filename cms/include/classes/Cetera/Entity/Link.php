<?php
namespace Cetera\Entity;

class Link {

    private $dest_type;
    private $dest_id;
    
    private $object = null;
    
    public function getObject() {
        if (!$this->object) {
            $od = \Cetera\ObjectDefinition::findById($this->dest_type);
            $em = \Cetera\Application::getInstance()->getEntityManager();
            $this->object = $em->find($od->getEntityClassName(), $this->dest_id);
        }
        return $this->object;
    }

}