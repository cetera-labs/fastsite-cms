<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera; 
 
/**
 * Абстрактный класс для объектов системы с известной таблицей в БД. (Пользователи, Разделы)
 *  
 * @package FastsiteCMS
 */ 
abstract class DynamicFieldsObjectPredefined extends DynamicFieldsObject {

    /**
     * Описание объекта 
     *         
	 * @internal
     * @var array    
     */  
    public static $predefinedObjectDefinition = array();
    
	/** 
	 * @internal
	 */
    public static function fetch($data, $a = 0, $b = null)
    {
        return parent::fetch($data, static::TYPE, static::TABLE);
    }
	
	public static function getObjectDefinition()
	{
		if ( !isset( self::$predefinedObjectDefinition[static::TYPE] ) )
		{
			self::$predefinedObjectDefinition[static::TYPE] = new ObjectDefinition(static::TYPE, static::TABLE);
		}
		return self::$predefinedObjectDefinition[static::TYPE];
	}
    
    /**
     * Возвращает объект по ID
     *   
     * @param int $id ID объекта              
     * @return User      
     */ 
  	public static function getById($id)
    {           
        return static::fetch($id);
  	}  
    
	/** @internal */
    public static function factory($type=0, $table = null, $fields = null)
    {       
		return parent::factory(static::TYPE, static::TABLE, $fields);
    }          

}
