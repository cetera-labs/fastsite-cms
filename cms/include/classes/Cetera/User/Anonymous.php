<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
namespace Cetera\User; 

/**
 * Виртуальный анонимный пользователь
 *
 * Состоит в группах "Все" и "Анонимные пользователи"
 *
 * @package FastsiteCMS
 **/
class Anonymous implements UserInterface {

	/**
	 * @internal
	 */
    public $id = USER_ANONYMOUS;
	/**
	 * @internal
	 */	
    public $login = 'Anonymous';
	/**
	 * @internal
	 */	
    public $groups = array(GROUP_ALL, GROUP_ANONYMOUS);
    
	/**
	 * @internal
	 */
    public function allowBackOffice()
    {
        return false;
    }
    
	/**
	 * @internal
	 */	
    public function allowAdmin()
    {
        return false;
    }
    
	/**
	 * @internal
	 */	
    public function isSuperUser()
    {
        return false;
    }
    
	/**
	 * @internal
	 */		
    public function allowCat($permission, $catalog)
    {
        return false;
    }
    
	/**
	 * @internal
	 */		
    public function allowFilesystem($path)
    {
        return false;
    }
    
	/**
	 * @internal
	 */		
    public function isEnabled()
    {
        return true;
    }
    
	/**
	 * @internal
	 */		
    public function isInGroup($group_id) {
        return in_array($group_id, $this->getGroups());
    }
    
	/**
	 * @internal
	 */		
    public static function getGroups()
    {
         return $this->groups;
    }
	
    public function __get($name)
    {
		return null;
    }	
	
    public function __isset ( $name )
    {     
        return true;    
    } 

    public function __toString()
    {
        return json_encode(array(
            'id'   => USER_ANONYMOUS,
			'name' => ''
        ));
    }  	

}