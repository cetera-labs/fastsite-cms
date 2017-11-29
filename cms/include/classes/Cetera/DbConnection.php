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
 * Используется в классах, которым необходимо работать с БД. 
 * Реализует метод getDbConnection() для получения объекта \Doctrine\DBAL\Connection
 */   
trait DbConnection {
    
    /**
     * Возвращает активное соединение с БД в рамках приложения
     *      
     * @return \Doctrine\DBAL\Connection
     */  	
    public static final function getDbConnection()
    {
        return Application::getInstance()->getConn();
    }
	
    /**
     * Сохраняет пару ключ/значение в БД
     *      
	 * @api
     * @param string $key ключ
	 * @param miced $value значение
     * @return void
     */  	
    public static function configSet($key, $value)
    {
		self::getDbConnection()->executeQuery('REPLACE INTO config SET `class` = ?, `key` = ?, `value` = ?', array( get_called_class(), $key, serialize($value) ));
    }	
	
    /**
     * Возвращает значение связанное с ключем из БД
     *      
	 * @api
     * @param string $key ключ
     * @return mixed
     */  	
    public static function configGet($key)
    {
		$data = self::getDbConnection()->fetchAssoc('SELECT value FROM config WHERE `class` = ? and `key` = ?', array( get_called_class(), $key ));
		if (!$data) return null;
		return unserialize($data['value']);
    }	
	
    /**
     * Возвращает все ключи/значения из БД
     *      
	 * @api
     * @return mixed
     */  	
    public static function configGetAll()
    {
		$data = self::getDbConnection()->fetchAll('SELECT * FROM config WHERE `class` = ?', array( get_called_class() ));
		$res = array();
		foreach ($data as $id => $v)
		{
			$res[$v['key']] = unserialize($v['value']);
		}
		return $res;
    }		
	
    /**
     * Удаляет пару ключ/значение из БД
     *      
	 * @api
     * @param string $key ключ
     * @return void
     */  		
    public static function configUnset($key)
    {
		self::getDbConnection()->executeQuery('DELETE FROM config WHERE `class` = ? and `key` = ?', array( get_called_class(), $key ));
    }		
    
}