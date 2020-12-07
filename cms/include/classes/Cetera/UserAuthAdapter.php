<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
 
/**
 * Адаптер авторизации
 *  
 * @package FastsiteCMS
 * @access private
 */ 
class UserAuthAdapter implements AdapterInterface {

	/**
	 * @ignore
	 */
    protected $_username;
	/**
	 * @ignore
	 */	
    protected $_email;
	/**
	 * @ignore
	 */	
    protected $_password;
	/**
	 * @ignore
	 */	
    protected $_remember;
	/**
	 * @ignore
	 */	
    protected $_backoffice;
    
	/**
	 * @ignore
	 */	
    protected $_authenticateResultInfo = null;

	/**
	 * Конструктор
	 *  
	 * @param array $v массив с данными для авторизации: login - логин пользователя, email - e-mail пользователя, password - пароль, remember - запомнить авторизацию. Обязательно указать либо login, либо email
     * @param boolean $backoffice TRUE - если необходимо авторизоваться для входа в BackOffice	 
	 */	
    public function __construct($v, $backoffice = false)
    {
        $v = array_merge(['email' => null, 'login' => null, 'pass' => null, 'backoffice' => null], $v);
        $this->_username   = $v['login'];
        $this->_email      = $v['email'];
        $this->_password   = $v['pass']?$v['pass']:$v['password'];
        $this->_remember   = isset($v['remember'])?$v['remember']:FALSE;
        $this->_backoffice = $backoffice;
        if (isset($v['backoffice'])) {
            $this->_backoffice = $v['backoffice'];
        }
    }

	/**
	 * Авторизовать пользователя
	 *  
	 * Пример:
     *  $auth = \Cetera\Application::getInstance()->getAuth();
	 *	$result = $auth->authenticate(new \Cetera\UserAuthAdapter(array(<br>
	 *		'login'    => $_POST['login'],<br>
	 *	    'pass'     => $_POST['password'],<br>
	 *		'remember' => $_POST['remember']<br>
	 *	))); <br>
     *<br>
	 *	switch ($result->getCode()) {<br>
	 *		case Zend\Authentication\Result::FAILURE_IDENTITY_NOT_FOUND:<br>
	 *			echo 'Пользователь не найден';<br>
	 *			break;<br>
	 *		case Zend\Authentication\Result::FAILURE_CREDENTIAL_INVALID:<br>
	 *			echo 'Неверный пароль';<br>
	 *			break;<br>
	 *		case Zend\Authentication\Result::SUCCESS:<br>
	 * 			echo 'Добро пожаловать!';<br>
	 * 			break;<br>
	 *	}<br>
     *
	 * @return Zend\Authentication\Result
	 */	
    public function authenticate()
    {
        $this->_authenticateResultInfo = array(
            'code'     => Result::FAILURE,
            'identity' => null
        );
        
		$user = null;
		if ($this->_username) $user = User::getByLogin($this->_username);  

		if (!$user && $this->_email) $user = User::getByEmail($this->_email);

		if (!$user || !$user->isEnabled() || (!$user->allowBackOffice() && $this->_backoffice)) {
			$this->_authenticateResultInfo['code'] = Result::FAILURE_IDENTITY_NOT_FOUND;
			return $this->_authenticateCreateAuthResult();
		}
		
		if (!$user->checkPassword($this->_password)) {
			  $this->_authenticateResultInfo['code'] = Result::FAILURE_CREDENTIAL_INVALID;
			  return $this->_authenticateCreateAuthResult(); 
		}

        $this->_authenticateResultInfo['code'] = Result::SUCCESS;
        $this->_authenticateResultInfo['identity'] = array(
            'uniq'     => $user->authorize($this->_remember),
            'user_id'  => $user->id
        );
		if ($this->_remember)
			 Application::getInstance()->getSession()->getManager()->rememberMe(REMEMBER_ME_SECONDS);		
			//else Application::getInstance()->getSession()->getManager()->regenerateId();
        return $this->_authenticateCreateAuthResult();
    }
    
	/**
	 * @ignore
     */	 
    protected function _authenticateCreateAuthResult()
    {
        return new Result(
            $this->_authenticateResultInfo['code'],
            $this->_authenticateResultInfo['identity']
        );
    }

}