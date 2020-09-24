<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera; 
 
/**
 * Пользовательские классы для определенного типа материалов должны наследовать этот класс
 *   
 * @package FastsiteCMS
 **/
abstract class MaterialUser extends Material {
    
    abstract public static function getTypeId();
    
    public static function getObjectDefinition() {
        return ObjectDefinition::findById(static::getTypeId());
    }
    
    public static function create() {
        $o = new static();
        $o->objectDefinition = static::getObjectDefinition();
        return $o;
    }
    
    public static function getById($id, $type = 0, $table = null) {
        return parent::getById($id, static::getObjectDefinition());
    }
    
}