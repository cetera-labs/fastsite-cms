<?php
namespace Cetera\ORM;

class Paginator extends \Doctrine\ORM\Tools\Pagination\Paginator {
    
    public function asArray() {
        return $this->getIterator();
    }
    
}