<?php
namespace Cetera\User; 

class Facebook extends External {
                       
    public static function getByFacebookId($user)
    {
        $u = self::getByResult(fssql_query('SELECT * FROM '.User::TABLE.' WHERE external='.USER_FACEBOOK.' and external_id="'.$user['id'].'"'));
        if (!$u) {
            fssql_query('INSERT INTO users (email,date_reg,login,name,disabled,external,external_id) VALUES ("'.$user['email'].'",NOW(),"'.$user['name'].'","'.$user['name'].'",0,'.USER_FACEBOOK.',"'.$user['id'].'")');
            $u = self::getByResult(fssql_query('SELECT * FROM '.User::TABLE.' WHERE external='.USER_FACEBOOK.' and external_id="'.$user['id'].'"'));
			$u->addExternal(USER_FACEBOOK,$user['id']);
		} else {
            fssql_query('UPDATE users SET email="'.$user['email'].'",name="'.$user['name'].'" WHERE id='.$u->id);
        }
        return $u;
    }
    
    public function getUrl()
    {
        return 'https://www.facebook.com/'.$this->external_id;
    }
    
    public function getSocialCode()
    {
        return 'facebook';
    }     
    
    /*
    public static function logout()
    {
        parent::logout();
        require_once("Auth/facebook/facebook.php");
        $facebook = new Facebook(array(
            'appId'  => FACEBOOK_APP_ID,
            'secret' => FACEBOOK_APP_SECRET,
        ));
        $facebook->destroySession();
    }
    */

}