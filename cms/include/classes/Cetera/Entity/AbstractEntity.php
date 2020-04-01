<?php
namespace Cetera\Entity;

abstract class AbstractEntity {

    protected $id;
    
    public function __get($name)
    {
    
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) return $this->$method();
        
        if (property_exists($this, $name)) return $this->$name;
    
        throw new \LogicException("Property {$name} is not found");
    }    
    
}