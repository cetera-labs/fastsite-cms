<?php
namespace Cetera\User; 

class Google extends External {
    public static function getByGoogleId($user)
    {
        $u = self::getByResult(fssql_query('SELECT * FROM '.User::TABLE.' WHERE external='.USER_GOOGLE.' and external_id="'.$user['id'].'"'));
        if (!$u) {
            fssql_query('INSERT INTO users (date_reg, email, login, name, disabled, external, external_id) VALUES (NOW(), "'.$user['email'].'", "'.$user['name'].'","'.$user['name'].'",0,'.USER_GOOGLE.',"'.$user['id'].'")');
            $u = self::getByResult(fssql_query('SELECT * FROM '.User::TABLE.' WHERE external='.USER_GOOGLE.' and external_id="'.$user['id'].'"'));
			$u->addExternal(USER_GOOGLE,$user['id']);
        }
        return $u;
    }
    
    public function getUrl()
    {
        return 'https://plus.google.com/u/0/'.$this->external_id;
    }    
    
    public function getSocialCode()
    {
        return 'google';
    } 
}