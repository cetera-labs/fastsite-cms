<?php
namespace Cetera\User; 

class VK extends External {
    public static function getByVKId($user)
    {
        $u = self::getByResult(fssql_query('SELECT * FROM '.User::TABLE.' WHERE external='.USER_VK.' and external_id="'.$user->uid.'"'));
        if (!$u) {
            fssql_query('INSERT INTO users (date_reg, login,name,disabled,external,external_id) VALUES (NOW(), "'.$user->screen_name.'","'.$user->first_name.' '.$user->last_name.'",0,'.USER_VK.',"'.$user->uid.'")');
            $u = self::getByResult(fssql_query('SELECT * FROM '.User::TABLE.' WHERE external='.USER_VK.' and external_id="'.$user->uid.'"'));
			$u->addExternal(USER_VK,$user->uid);
        } else {
            fssql_query('UPDATE users SET login="'.$user->screen_name.'", name="'.$user->first_name.' '.$user->last_name.'" WHERE id='.$u->id);
        }
        return $u;
    }
    
    public function getUrl()
    {
        return 'http://vk.com/id'.$this->external_id;
    }
    
    public function getSocialCode()
    {
        return 'vk';
    }    
}