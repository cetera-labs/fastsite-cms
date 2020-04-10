<?php
namespace Cetera\Entity;

abstract class AbstractEntity {

	const OID = 0;

    public $id;
	
	private $objectDefinition = null;
    
    public function __get($name)
    {
    
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) return $this->$method();
        
        if (property_exists($this, $name)) return $this->$name;
    
        throw new \LogicException("Property {$name} is not found");
    }   

	public function getObjectDefinition() {
		if ($this->objectDefinition === null) {
			$c = get_called_class();
			$this->objectDefinition = \Cetera\ObjectDefinition::findById($c::OID);
		}
		return $this->objectDefinition;
	}
    
}