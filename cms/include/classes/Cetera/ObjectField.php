<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
namespace Cetera; 

/**
 * @internal
 **/
class ObjectField implements \ArrayAccess {

    protected $container = array(); 
    
    public $name = null;
    
    protected $parentObjectDefinition = null;
    
    public static function factory($data, $od) {
    
        switch($data['type']) {
            case FIELD_LINK:
                if ($data['pseudo_type'] == PSEUDO_FIELD_LINK_USER) return new ObjectFieldLinkUser($data, $od);
                return new ObjectFieldLink($data, $od);
            case FIELD_LINKSET:
                if ($data['pseudo_type'] == PSEUDO_FIELD_LINKSET_USER) return new ObjectFieldLinkSetUser($data, $od);
                if ($data['pseudo_type'] == PSEUDO_FIELD_CATOLOGS) return new ObjectFieldLinkSetCatalog($data, $od);
                return new ObjectFieldLinkSet($data, $od);
            case FIELD_LINKSET2:
                return new ObjectFieldLinkSet2($data, $od);				
            case FIELD_MATERIAL:
                return new ObjectFieldMaterial($data, $od);
            case FIELD_MATSET:
                return new ObjectFieldMaterialSet($data, $od);
			case FIELD_TEXT:
			case FIELD_LONGTEXT:
			case FIELD_HUGETEXT:
				return new ObjectFieldText($data, $od);
            default:
                return new ObjectFieldScalar($data, $od);
        }
    
    }

    public function __construct($data, $od) {
        $this->container = $data;
        $this->name = $data['name'];
        $this->parentObjectDefinition = $od;
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $offset = $this->fixOffset($offset);
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        $offset = $this->fixOffset($offset);
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset) {
        $offset = $this->fixOffset($offset);
        unset($this->container[$offset]);
    }

    public function offsetGet($offset) {
        $offset = $this->fixOffset($offset);
		if ($offset == 'describ') {
			return Application::getInstance()->decodeLocaleString( $this->container[$offset] );
		}
		if ($offset == 'len' && $this->container['type'] == FIELD_ENUM) {
            $g = Application::getInstance()->getConn()->fetchArray("SHOW COLUMNS FROM ".$this->parentObjectDefinition->alias." LIKE '".$this->container['name']."'");
            return substr($g[1],5,strlen($g[1])-6);			
		}
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
    
    public static function fixOffset($offset) {
        if ($offset == 'show') $offset = 'shw';
        if ($offset == 'description') $offset = 'describ';
        if ($offset == 'length') $offset = 'len';
        return $offset;
    }
}

/**
 * Скалярное поле
 * @internal
*/
class ObjectFieldScalar extends ObjectField {
}

/**
 * Текстовое поле
 * @internal
*/
class ObjectFieldText extends ObjectFieldScalar {
}

class ObjectFieldLinkSet2 extends ObjectField {

    public function getLinkTable() 
    {
        return $this->parentObjectDefinition->table.'_'.$this->name;
    }

}


/**
 * Поля-ссылки на другие объекты
 * @internal
*/
abstract class ObjectFieldLinkAbstract extends ObjectField {

    private $objectDefinition = null;
        
    final public function getObjectDefinition()
    {
        if (!$this->objectDefinition)
            $this->objectDefinition = $this->_getObjectDefinition();
            
        return $this->objectDefinition;
    }
    
    abstract protected function _getObjectDefinition();
    
    final public function getTable() {
        return $this->getObjectDefinition()->table;
    }
	
	public function getIterator()
	{
		return $this->getObjectDefinition()->getMaterials();
	}

}

/**
 * Поля-множества ссылок на другие объекты
 * @internal
*/
abstract class ObjectFieldLinkSetAbstract extends ObjectFieldLinkAbstract {

    public function getLinkTable() 
    {
        return $this->parentObjectDefinition->table.'_'.$this->getTable().'_'.$this->name;
    }

}

/**
 * @internal
*/
class ObjectFieldLink extends ObjectFieldLinkAbstract {

    use ObjectFieldLinkTrait;
}

/**
 * @internal
*/
class ObjectFieldMaterial extends ObjectFieldLinkAbstract {

    use ObjectFieldMaterialTrait;
    
}

/**
 * @internal
*/
class ObjectFieldLinkUser extends ObjectFieldLinkAbstract {

    use ObjectFieldUserTrait;
}

/**
 * @internal
*/
class ObjectFieldLinkSet extends ObjectFieldLinkSetAbstract {

    use ObjectFieldLinkTrait;

}

/**
 * @internal
*/
class ObjectFieldMaterialSet extends ObjectFieldLinkSetAbstract {

    use ObjectFieldMaterialTrait;

}

/**
 * @internal
*/
class ObjectFieldLinkSetCatalog extends ObjectFieldLinkSetAbstract {

    use ObjectFieldCatalogTrait;
}

/**
 * @internal
*/
class ObjectFieldLinkSetUser extends ObjectFieldLinkSetAbstract {

    use ObjectFieldUserTrait;
}

/**
 * Поля, содержащие другие материалы
 * @internal
*/
trait ObjectFieldMaterialTrait {

    protected function _getObjectDefinition()
    {
        return new ObjectDefinition($this->container['len']);
    }
}

/**
 * Поля, хранящие ссылки на пользователей
 * @internal
*/
trait ObjectFieldUserTrait {

    protected function _getObjectDefinition()
    {
        return new ObjectDefinition(User::TYPE, User::TABLE);
    }
}

/**
 * Поля, хранящие ссылки на разделы
 * @internal
*/
trait ObjectFieldCatalogTrait {

    protected function _getObjectDefinition()
    {
        return new ObjectDefinition(Catalog::TYPE, Catalog::TABLE);
    }
	
    private $catalog = null;

    public function getCatalog()
    {
        if (!$this->catalog) $this->catalog = Catalog::getById($this->container['len']);
        return $this->catalog;
    }
	
	public function getIterator()
	{
		return $this->getCatalog()->getChildren();
	}		
    
}

/**
 * Поля, хранящие ссылки на материалы из другого раздела
 * @internal
*/
trait ObjectFieldLinkTrait {

    private $catalog = null;

    public function getCatalog()
    {
        if (!$this->catalog) $this->catalog = Catalog::getById($this->container['len']);
        return $this->catalog;
    }

    protected function _getObjectDefinition()
    {
        return $this->getCatalog()->getMaterialsObjectDefinition();
    }
	
	public function getIterator()
	{
		return $this->getCatalog()->getMaterials();
	}	
}