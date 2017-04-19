<?php
namespace Cetera\User; 

class Twitter extends External {
    public static function getByTwitterId($user)
    {
        $u = self::getByResult(fssql_query('SELECT * FROM '.User::TABLE.' WHERE external='.USER_TWITTER.' and external_id="'.$user->id.'"'));
        if (!$u) {
            fssql_query('INSERT INTO users (date_reg, login,name,disabled,external,external_id) VALUES (NOW(), "'.$user->screen_name.'","'.$user->name.'",0,'.USER_TWITTER.',"'.$user->id.'")');
            $u = self::getByResult(fssql_query('SELECT * FROM '.User::TABLE.' WHERE external='.USER_TWITTER.' and external_id="'.$user->id.'"'));
			$u->addExternal(USER_TWITTER,$user->id);
        }
        return $u;
    }
    
    public function getUrl()
    {
        return 'https://twitter.com/'.$this->external_id;
    }    
    
    public function getSocialCode()
    {
        return 'twitter';
    }    
}