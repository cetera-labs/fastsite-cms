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
 * Пользователь
 *
 * @package CeteraCMS
 **/
class User extends DynamicFieldsObjectPredefined implements User\UserInterface {
    
    const TYPE = 2;
    const TABLE = 'users';
	
	public static $social = array(
			'facebook'      => USER_FACEBOOK,
			'vkontakte'     => USER_VK,
			'twitter'       => USER_TWITTER,
			'odnoklassniki' => USER_ODNOKLASSNIKI,
			'googleplus'    => USER_GOOGLE,
			'livejournal'   => USER_LJ
	);	
    
    private $_groups = FALSE;
	
	private $_external = FALSE;
    
  	public static function enum()
    {   
        return new Iterator\User();
  	}
	
    /**
     * Возвращает пользователя по ID внешней сети
     * 
	 * @param mixed $network код внешней сети
     * @param string $id идентификатор пользователя во внешней сети
     * @return User      
     */   	
    public static function getExternal($network, $id)
	{

		if (!(int)$network)
		{
			if (!isset(self::$social[$network])) return null;
			$network = self::$social[$network];
		}
		
		$data = self::getDbConnection()->fetchAssoc( 'SELECT A.* FROM '.User::TABLE.' A LEFT JOIN users_external B ON (A.id = B.user_id) WHERE B.external_id = ? and B.external_type = ?', array( $id, $network ) );
		if (!$data) {
			// пробуем найти по старой схеме
			try {
				$data = self::getDbConnection()->fetchAssoc( 'SELECT * FROM '.User::TABLE.' WHERE external_id=? and external=?', array( $id, $network ) );
				if ($data) {
					$u = User::fetch($data); 
					$u->addExternal( $network, $id );	
				} else {
					return null;
				}
			} 
			catch (\Exception $e) {
				return null;
			}
		
		}
		
        switch (  $network ) {
            case USER_OPENID:        return User\OpenId::fetch($data);
            case USER_FACEBOOK:      return User\Facebook::fetch($data);
            case USER_TWITTER:       return User\Twitter::fetch($data);
            case USER_VK:            return User\VK::fetch($data);
            case USER_LJ:            return User::fetch($data);
            case USER_GOOGLE:        return User\Google::fetch($data);
            case USER_ODNOKLASSNIKI: return User::fetch($data);
        } 
        
        return self::fetch($data);  		
		
	}
	
    public static function fetch($data, $type = 0, $table = null)
    {
		if ( is_array($data) && isset($data['id']) && isset($data['password']) ) {
			$data['__password_crypted'] = true;
		}
		return parent::fetch($data, $type, $table);
	}		
	
    /**
     * Возвращает пользователя по ID
     *   
     * @param string $username логин              
     * @return User      
     */   
  	public static function getById($uid)
    {    
		if ($uid == USER_ANONYMOUS) return new User\Anonymous();
        $fields = self::getDbConnection()->fetchAssoc( 'SELECT * FROM '.User::TABLE.' WHERE id = ?', array( $uid ) );
        if (!$fields) throw new \Exception('User:'.$uid.' is not found'); 
        return self::fetch($fields);    
  	}         
   	
    /**
     * Возвращает пользователя по его логину
     *   
     * @param string $username логин              
     * @return User      
     */   
  	public static function getByLogin($username)
    {    
        $fields = self::getDbConnection()->fetchAssoc( 'SELECT * FROM '.User::TABLE.' WHERE login = ?', array( $username ) );
        if (!$fields) return false;
        return self::fetch($fields);    
  	}
	
    /**
     * Возвращает пользователя по его e-mail
     *   
     * @param string $email логин              
     * @return User      
     */   
  	public static function getByEmail($email)
    {
        $fields = self::getDbConnection()->fetchAssoc('SELECT * FROM '.User::TABLE.' WHERE email = ?', array($email));
        if (!$fields) return false;
        return self::fetch($fields);       
  	}
			
    /**
     * Возвращает авторизованного в данный момент пользователя. Или false, если нет авторизации
     *             
     * @return User      
     */ 
  	public static function getAuthorized($identity)
  	{       
        $conn = self::getDbConnection();
        $conn->executeUpdate('UPDATE users_auth SET time=? WHERE user_id=? and uniq=? and ip=?', array( time(), (int)$identity['user_id'], $identity['uniq'], $_SERVER['REMOTE_ADDR'] ));        
        $fields = $conn->fetchAssoc('SELECT A.* FROM users A LEFT JOIN users_auth B ON (A.id=B.user_id) WHERE B.user_id=? and B.uniq=?', array( (int)$identity['user_id'], $identity['uniq'] ));  
        
        if ($fields) {		
            return self::fetch($fields);
        } else {
            return false;
        }      
    }
		
    /**
     * Привязывает пользователя к аккаунту внешней сети
     * 
	 * @param mixed $network код внешней сети
     * @param string $id идентификатор пользователя во внешней сети
     * @return User      
     */   	
    public function addExternal($network, $id)
	{

		if (!(int)$network)
		{
			if (!isset(self::$social[$network])) return $this;
			$network = self::$social[$network];
		}

        $this->getDbConnection()->delete('users_external', array(
                'external_type' => $network, 
                'external_id'   => $id,
        ));		
		
        $this->getDbConnection()->insert('users_external', array(
                'user_id'       => $this->id, 
                'external_type' => $network, 
                'external_id'   => $id,
        ));
			
	    $this->_external = null;
			
        return $this;  		
		
	}
	
    /**
     * Возвращает ID пользователя, если он привязан к внешней сети
     * 
	 * @param mixed $network код внешней сети
     * @return string $id      
     */   	
    public function getExternalId($network)
	{
		$this->fetchExternal();
		
		if (!(int)$network)
		{
			if (!isset(self::$social[$network])) return null;
			$network = self::$social[$network];
		}		
		
		if (isset( $this->_external[$network] )) return $this->_external[$network]['external_id'];
		return null;
		
	}
	
	public function getExternals()
	{
		$this->fetchExternal();
		
		$res = array();
		
		foreach($this->_external as $external)
		{
			$res[] = self::getExternal($external['external_type'], $external['external_id']);
		}
		
		return $res;
	}
	
	private function fetchExternal()
	{
		if (!$this->_external)
		{
			$this->_external = array();

			if ($this->fields['external']) $this->_external[ $this->fields['external'] ] = array(
				'user_id' => $this->id,
				'external_id' => $this->fields['external_id'],
				'external_type' => $this->fields['external']
			);

			$stmt = $this->getDbConnection()->executeQuery('SELECT * FROM users_external WHERE user_id = ?', array( $this->id ));
			while ($row = $stmt->fetch())
			{
				$this->_external[ $row['external_type'] ] = $row;
			}
		}		
	}

    
    /**
     * Имеет ли право пользователь на доступ в back office
     *             
     * @return bool      
     */ 
	public function allowBackOffice()
    {
	   if (!$this->isEnabled()) return FALSE;
	   return $this->isInGroup(GROUP_BACKOFFICE) || $this->isInGroup(GROUP_ADMIN);
    }
    
    /**
     * Имеет ли пользователь привелегии администратора
     *             
     * @return bool      
     */ 
    public function allowAdmin()
    {
        if (!$this->isEnabled()) return FALSE;
        return $this->isInGroup(GROUP_ADMIN);
    }
    
    /**
     * Имеет ли пользователь привелегии администратора
     *             
     * @return bool      
     */ 
    public function isAdmin()
    {
        return $this->allowAdmin();
    }    
    
    /**
     * Имеет ли пользователь привелегии суперпользователя
     * 
     * Суперпользовател может создавать защищенные разделы, материалы, типы материалов, группы пользователей          
     *             
     * @return bool      
     */ 
    public function isSuperUser()
    {
        return $this->id == ADMIN_ID;
    }
    
    /**
     * Имеет ли пользователь разрешение на раздел
     *     
     * @param int $permission код разрешения
     * @param int|Catalog $catalog раздел или ID раздела            
     * @return bool      
     */ 
    public function allowCat($permission, $catalog)
    {
        if ($this->allowAdmin()) return TRUE;
        if (!$this->allowBackOffice()) return FALSE;
        
        if (!is_object($catalog)) {
            if ($catalog == CATALOG_VIRTUAL_HIDDEN) return TRUE;
            if ($catalog == CATALOG_VIRTUAL_USERS) return FALSE;
        
            try {
                $catalog = Catalog::getById($catalog);
            } catch (Exception $e) {
                return FALSE;
            }
        }
        
        return $catalog->allowAccess($permission, $this->groups);
    }
    
    /**
     * Имеет ли пользователь право на доступ к физическому каталогу
     *     
     * @param string $path путь к каталогу отновительно корня сервера           
     * @return bool      
     */
    public function allowFilesystem($path)
    {
        if (!$this->allowBackOffice()) return FALSE;
        $r = self::getDbConnection()->fetchColumn('SELECT COUNT(*) FROM users_groups_deny_filesystem WHERE path=? and group_id IN ('.implode(',',$this->getGroups()).')', array($path), 0);
        if ($r > 0) return FALSE;       
        return TRUE;
    }
    
    /**
     * Пользователь не заблокирован
     *             
     * @return bool      
     */
	public function isEnabled()
    {
	   return !$this->isDisabled();
    }
    
    /**
     * Пользователь заблокирован
     *             
     * @return bool      
     */
	public function isDisabled()
    {
	   return (bool)$this->fields['disabled'];
    }
    
    /**
     * Является ли пользователь членом группы
     *          
     * @param int $group_id ID группы        
     * @return bool      
     */
    public function isInGroup($group_id)
    {
        if ($group_id == GROUP_ADMIN)
            return in_array($group_id, $this->getGroups());
            
        return in_array($group_id, $this->getGroups()) || $this->allowAdmin();
    }
    
    /**
     * Список групп, в которых состоит пользователь
     *               
     * @return array     
     */ 
    public function getGroups()
    {
        if (!$this->_groups) {
            $this->_groups = array(GROUP_ALL);
            if ($this->id == ADMIN_ID) $this->_groups[] = GROUP_ADMIN;
            $r = self::getDbConnection()->fetchAll('SELECT group_id FROM users_groups_membership WHERE user_id='.$this->id);
            foreach ($r as $f) $this->_groups[] = $f['group_id'];
        }
        return $this->_groups;
    }
    
    public function getName()
    {
        if ($this->fields['name']) return $this->fields['name'];
        return $this->login;
    }        
    
    /**
     * Снимает авторизацию пользователя
     *                     
     * @return void     
     */ 
    public static function logout()
    {
        $id = \Zend_Auth::getInstance()->getIdentity();
        if (!$id) return;
        self::getDbConnection()->executeQuery("DELETE FROM users_auth WHERE user_id=".$id['user_id']." and uniq='".$id['uniq']."'");
        \Zend_Auth::getInstance()->clearIdentity();
		\Zend_Session::forgetMe();		
    }
    
    /**
     * Авторизует пользователя
     *   
     * @param bool $remember долговременная авторизация                  
     * @return void     
     */ 
    public function authorize($remember)
    {
        $uniq = md5 (uniqid (rand()));
		$conn = self::getDbConnection();
        $conn->executeQuery('UPDATE '.User::TABLE.' SET last_login=NOW() WHERE id='.$this->id);
		$conn->executeQuery('DELETE FROM users_auth WHERE remember = 0 and time < ?', array(time()-AUTH_INACTIVITY_SECONDS));
        $conn->executeQuery('INSERT INTO users_auth SET user_id='.$this->id.', remember='.(int)$remember.',uniq="'.$uniq.'", ip="'.$_SERVER['REMOTE_ADDR'].'", time='.time());
        return $uniq;
    }
    
    /**
     * Удаляет пользователя
     *               
     * @return void     
     */ 
    public function delete()
    {
        if ($this->id == 0 || $this->id == ADMIN_ID) return FALSE;
		$conn = self::getDbConnection();
		$str .= 'DELETE [LOGIN='.$this->login.'][ID='.$this->id.']';
        $conn->executeQuery('DELETE FROM users_groups_membership WHERE user_id='.$this->id);
        $conn->executeQuery('DELETE FROM users_auth WHERE user_id='.$this->id);
        parent::delete();
		
       $application = Application::getInstance();
       $application->eventLog(EVENT_USER_PROP, $str);		
    }
	
    public function setFields($fields)
    {
		if (!isset($fields['__password_crypted']) && isset($fields['password'])) {
			if ($fields['password'] == PASSWORD_NOT_CHANGED) {
				unset($fields['password']);
			}			
			elseif ($this->id == 1 && Application::getInstance()->getUser()->id > 0 && Application::getInstance()->getUser()->id != 1) {
				// запретить смену пароля админу
				unset($fields['password']);
			}
			else {
				$this->setPassword($fields['password']);
				$fields['password'] = $this->password;
			}
		}
		
		if (isset($fields['__password_crypted'])) {
			unset($fields['__password_crypted']);
		}
		
        return parent::setFields($fields, $from_db);    
    } 	
	
    public function setPassword($value)
    {   
		$this->fields['password'] = md5($value);
		return $this;
	}	
	
    public function checkPassword($value)
    {   
		return $this->password == md5($value);
	}	
    
    public function save($hidden = true)
    {       
        $this->getFieldsDef();
        
		if (!$this->fields['login']) throw new Exception\Form(Exception\CMS::FIELD_NOT_FOUND, 'login', 'login');
		
        $r = self::getDbConnection()->fetchColumn('SELECT COUNT(*) FROM '.$this->table.' WHERE login=? and id<>?', array($this->fields['login'], (int)$this->id), 0 );
	    if ($r > 0) throw new Exception\Form(Exception\CMS::USER_EXISTS, 'login');
        
        $values = 'login="'.addslashes($this->fields['login']).'"';
        $login = $this->fields['login'];
        unset($this->fields['login']);
        
        $values .= $this->saveDynamicFields(null, $hidden);
   
        if ($this->id) {
            $sql = "UPDATE ".$this->table." SET ".$values." WHERE id=".$this->id;  
			$str = 'UPDATE';
        } else {
            $sql = "INSERT INTO ".$this->table." SET ".$values;
			$str = 'CREATE';
        }
		
        self::getDbConnection()->executeQuery($sql);
		
		$this->fields['login'] = $login;
        
        if (!$this->id) $this->_id = self::getDbConnection()->lastInsertId();  
      
        $this->saveDynimicLinks();   
       
        if (isset($this->fields['groups']) && is_array($this->fields['groups']))
        {
            self::getDbConnection()->executeQuery('DELETE FROM users_groups_membership WHERE user_id='.$this->id);
            foreach($this->fields['groups'] as $gid) 
              if ($gid && ($this->id != 1 || $gid != GROUP_ADMIN))
                self::getDbConnection()->executeQuery('INSERT INTO users_groups_membership SET user_id='.$this->id.', group_id='.(int)$gid);
        }
       
       $application = Application::getInstance();
       $str .= ' [LOGIN='.$login.'][ID='.$this->id.']';
       if (is_array($this->fields['groups'])) $str .= '[GROUPS='.implode(',',$this->fields['groups']).']';
       $application->eventLog(EVENT_USER_PROP, $str);

    }
    
    public function boArray()
    {
        return array(
            'id'          => $this->id,
            'name'        => $this->name,
            'permissions' => array(
                'admin' => $this->allowAdmin(),
                'adminRootCat' => $this->allowCat(PERM_CAT_ADMIN,0)
            )
        );
    }      
	
	public static function generatePassword($length = 5)
	{
		$arr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','r','s','t','u','v','x','y','z',
					 'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','R','S','T','U','V','X','Y','Z','1','2','3','4','5','6','7','8','9','0');
		$pass = "";
		for($i = 0; $i < $length; $i++) $pass .= $arr[rand(0, count($arr) - 1)];		
		return $pass;
	}
	
	public static function register($params, $unique_email = false)
	{
		$a = Application::getInstance();
		$t = $a->getTranslator();
		
		if ($unique_email) {
			$u = self::getByEmail($params['email']);
			if ($u) {
				throw new Exception\Form($t->_('Пользователь с таким e-mail уже зарегистрирован'), 'email', 'email');
				return;
			}			
		}
		
		if ($params['password'] != $params['password2']) {
			throw new Exception\Form($t->_('Пароли не совпадают'), 'password', 'password');
		}

		$u = self::create();
		$u->setFields($params);		
		$u->save();
				
		Event::trigger(EVENT_CORE_USER_REGISTER, [
			'user'     => $u, 
			'server'   => $a->getServer(), 
			'password' => $params['password'] 
		]);
		
		return $u;
	}
	
	public function recoverPassword($mailFrom = 'no-reply@cetera.ru', $fromName = false)
	{
		$a = Application::getInstance();
		$t = $a->getTranslator();
		
		if (!$this->email) {
			throw new \Exception($t->_('У пользователя не указан e-mail'));
		}		
		
		$pass = $this->generatePassword();
		$this->password = $pass;
		$this->save();
		
		$res = \Cetera\Mail\Event::trigger( 'USER_RECOVER', array('user' => $this, 'server' => $a->getServer(), 'password' => $pass ));
		
		if (!$res) {
			// если не было отправлено писем по событию, то принудительно сообщим о смене пароля
			$mail = new \PHPMailer(true);
			$mail->AddAddress($this->email);					
			$mail->CharSet = 'utf-8';
			$mail->ContentType = 'text/plain';
			$mail->From = $mailFrom;
			$mail->FromName = $fromName;
			$mail->Subject = $t->_('Ваш пароль на сайт ').$a->getServer()->name;
			$mail->Body = $t->_('Ваш новый пароль: ').$pass;
			$mail->Send();				
		}
	}

}