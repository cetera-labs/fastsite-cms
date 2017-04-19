<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
namespace Cetera\User; 

/**
 * Пользователь OpenId
 *
 * @package CeteraCMS
 **/
class OpenId extends External {

    /**
     * Возвращает пользователя по его OpenId идентификатору
     *   
     * @param string OpenId идентификатор             
     * @return User\OpenId     
     */  
    public static function getByOpenId($openid)
    {
        $u = self::getByResult(fssql_query('SELECT * FROM '.User::TABLE.' WHERE external='.USER_OPENID.' and external_id="'.$openid.'"'));
        if (!$u) {
            if (!$user['fullname']) $user['fullname'] = parse_url($openid, PHP_URL_HOST);
            fssql_query('INSERT INTO users (date_reg, email,login,name,disabled,external,external_id) VALUES (NOW(), "'.$user['email'].'","'.parse_url($openid, PHP_URL_HOST).'","'.$user['fullname'].'",0,'.USER_OPENID.',"'.$openid.'")');
            $u = self::getByResult(fssql_query('SELECT * FROM '.User::TABLE.' WHERE external='.USER_OPENID.' and external_id="'.$openid.'"'));
			$u->addExternal(USER_OPENID,$openid);
        }
        return $u;
    }
          
}
