<?php
namespace Cetera; 

/**
 * Группы пользователей.
 * 
 * @package FastsiteCMS
 */ 
class UserGroup extends Base {
	
	use DbConnection;

    protected $_name;
    protected $_describ;

    protected function __construct($fields) 
    {
        $fields['data'] = unserialize($fields['data']);
        parent::__construct($fields);
    }

    /**
     * Возвращает все группы пользователей.
     *          
     * @return array       
     */   
    public static function enum()
    {
        $res = array();
        $r = self::getDbConnection()->query('SELECT * FROM users_groups ORDER BY name');
        while ($f = $r->fetch()) {
             $res[] = new self($f);
        }
        return $res;
    }
  	
  	/**
  	 * Возвращает группу пользователей по ее идентификатору.	
  	 * 	 
  	 * @param int $id ID группы 	 
  	 * @return UserGroup
  	 * @throws Exception\CMS	 
  	 */     	
  	public static function getById($id) 
    {
		$f = self::getDbConnection()->fetchAssoc('SELECT * FROM users_groups WHERE id=?', [$id]);
		if (!$f) throw new Exception\CMS('Группа ID='.$id.' не найдена');
		return new self($f);
  	}
    
    public function __toString()
    {
        return $this->id;
    }       
    
}